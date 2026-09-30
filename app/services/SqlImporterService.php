<?php

namespace app\services;

use Exception;

/**
 * Class SqlImporterService
 * 
 * Serviço educacional responsável por ler, validar e realizar o parsing de arquivos
 * de dump SQL exportados do phpMyAdmin tradicional.
 * 
 * O parsing é realizado via Leitura em Stream (linha a linha) para maximizar a 
 * eficiência de hardware e memória (evitando carregar arquivos grandes na RAM).
 * 
 * Os dados extraídos são convertidos e salvos na estrutura de metadados do DevStudio
 * (tabelas `banco`, `tabela` e `atributo` da base `mvc_creator`).
 * 
 * @package app\services
 */
class SqlImporterService
{
    private BancoService $bancoService;
    private TabelaService $tabelaService;
    private AtributoService $atributoService;

    public function __construct()
    {
        $this->bancoService = new BancoService();
        $this->tabelaService = new TabelaService();
        $this->atributoService = new AtributoService();
    }

    /**
     * Importa um arquivo SQL enviado pelo usuário e popula a estrutura no mvc_creator.
     * 
     * @param string $filePath Caminho absoluto do arquivo temporário (.sql)
     * @param int $idUsuario ID do usuário logado na sessão
     * @param string|null $nomeBancoManual Nome opcional para o banco de dados
     * @return array Resumo do resultado da importação
     * @throws Exception Em caso de erros de validação ou estrutura malformada
     */
    public function importarSql(string $filePath, int $idUsuario, ?string $nomeBancoManual = null): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("O arquivo de dump SQL não foi encontrado ou não é legível.");
        }

        // Validação básica de extensão do arquivo
        $extensao = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extensao !== 'sql') {
            // Em arquivos temporários de upload sem extensão, verifica o conteúdo inicial
            $primeirosBytes = file_get_contents($filePath, false, null, 0, 500);
            if (!preg_match('/CREATE TABLE|INSERT INTO|-- phpMyAdmin/i', $primeirosBytes)) {
                throw new Exception("O arquivo fornecido não possui um formato SQL válido.");
            }
        }

        // 1. Extrair nome do Banco de Dados ou usar o fornecido
        $nomeBanco = $nomeBancoManual ? trim($nomeBancoManual) : $this->extrairNomeBancoDoSql($filePath);
        if (empty($nomeBanco)) {
            $nomeBanco = 'banco_importado_' . date('Ymd_His');
        }

        // Sanitização do nome do banco
        $nomeBanco = preg_replace('/[^a-zA-Z0-9_]/', '', $nomeBanco);

        // Insere o novo banco de dados em mvc_creator
        $this->bancoService->insert($idUsuario, $nomeBanco, 'root', '', 'localhost', '3306');
        $bancoObj = $this->bancoService->getBancoEspecifico($nomeBanco, 'root', $idUsuario);

        if (!$bancoObj) {
            throw new Exception("Erro ao registrar o banco de dados no sistema.");
        }

        $idBanco = (int)$bancoObj['id_banco'];

        // 2. Realizar parsing em Stream das tabelas e atributos
        $tabelasImportadas = $this->parseStreamSql($filePath, $idBanco);

        return [
            'sucesso' => true,
            'id_banco' => $idBanco,
            'nome_banco' => $nomeBanco,
            'total_tabelas' => count($tabelasImportadas),
            'tabelas' => array_keys($tabelasImportadas),
            'mensagem' => sprintf("Importação concluída com sucesso! %d tabela(s) importada(s).", count($tabelasImportadas))
        ];
    }

    /**
     * Tenta identificar declarações de `CREATE DATABASE` ou `USE` no início do arquivo.
     */
    private function extrairNomeBancoDoSql(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) return '';

        $nomeBanco = '';
        while (($line = fgets($handle)) !== false) {
            // Procura por `CREATE DATABASE `schema_name`` ou `USE `schema_name``
            if (preg_match('/(?:CREATE\s+DATABASE|USE)\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $line, $matches)) {
                $nomeBanco = $matches[1];
                break;
            }
        }
        fclose($handle);
        return $nomeBanco;
    }

    /**
     * Lê o arquivo SQL linha a linha (Stream) e extrai comandos CREATE TABLE.
     */
    private function parseStreamSql(string $filePath, int $idBanco): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Falha ao abrir o arquivo para leitura.");
        }

        $tabelasCriadas = [];
        $insideCreateTable = false;
        $currentTableContent = '';

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);

            // Ignora comentários de linha e linhas vazias
            if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                continue;
            }

            // Identifica o início de um `CREATE TABLE`
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?([a-zA-Z0-9_]+)[`"]?/i', $line, $matches)) {
                $insideCreateTable = true;
                $tableName = $matches[1];
                $currentTableContent = $line;
                continue;
            }

            // Se está dentro de um CREATE TABLE, acumula o conteúdo até encontrar o fechamento
            if ($insideCreateTable) {
                $currentTableContent .= $line;

                // Fim do bloco CREATE TABLE (geralmente termina com `) ENGINE=...;` ou `;`)
                if (preg_match('/;\s*$/', $trimmed)) {
                    $insideCreateTable = false;
                    
                    // Processa a tabela acumulada
                    $this->processarBlocoCreateTable($currentTableContent, $idBanco, $tableName ?? 'tabela');
                    $tabelasCriadas[$tableName ?? 'tabela'] = true;
                    $currentTableContent = '';
                }
            }
        }

        fclose($handle);
        return $tabelasCriadas;
    }

    /**
     * Processa o texto completo de um comando CREATE TABLE e salva no mvc_creator.
     */
    private function processarBlocoCreateTable(string $sqlBlock, int $idBanco, string $nomeTabela): void
    {
        // 1. Inserir a tabela no mvc_creator
        $this->tabelaService->insert($nomeTabela, $idBanco);
        $tabelaObj = $this->tabelaService->getTabelaEspecifica($nomeTabela, $idBanco);
        if (!$tabelaObj) return;

        $idTabela = (int)$tabelaObj['id_tabela'];

        // 2. Extrair linhas de definições internas entre parênteses
        preg_match('/\((.*)\)[^)]*$/s', $sqlBlock, $matches);
        if (empty($matches[1])) return;

        $innerContent = $matches[1];
        $lines = explode("\n", $innerContent);

        $primaryKeys = [];
        $uniqueKeys = [];
        $atributosDefinidos = [];

        // Primeira passagem: Identificar PRIMARY KEY e UNIQUE declarados separadamente ao final
        foreach ($lines as $line) {
            $lineTrimmed = trim($line, " \t\n\r,");

            if (preg_match('/PRIMARY\s+KEY\s*\(([^)]+)\)/i', $lineTrimmed, $pkMatch)) {
                $cols = explode(',', $pkMatch[1]);
                foreach ($cols as $c) {
                    $primaryKeys[trim($c, " `\"")] = true;
                }
            }

            if (preg_match('/UNIQUE\s+(?:KEY\s+)?[`"]?([a-zA-Z0-9_]+)?[`"]?\s*\(([^)]+)\)/i', $lineTrimmed, $uqMatch)) {
                $cols = explode(',', $uqMatch[2]);
                foreach ($cols as $c) {
                    $uniqueKeys[trim($c, " `\"")] = true;
                }
            }
        }

        // Segunda passagem: Processar colunas
        foreach ($lines as $line) {
            $lineTrimmed = trim($line, " \t\n\r,");

            // Ignora linhas de declaração de chave isolada
            if (empty($lineTrimmed) || 
                preg_match('/^(?:PRIMARY\s+KEY|UNIQUE|KEY|INDEX|CONSTRAINT|FOREIGN\s+KEY)/i', $lineTrimmed)) {
                continue;
            }

            // Extrai Nome da Coluna e Tipo
            if (preg_match('/^[`"]?([a-zA-Z0-9_]+)[`"]?\s+([a-zA-Z0-9_\(\),\'"\s]+)/i', $lineTrimmed, $colMatch)) {
                $colName = $colMatch[1];
                $typeAndFlags = $colMatch[2];

                // Isola o tipo de dado principal (ex: VARCHAR(60), INT, ENUM('a','b'), DATETIME)
                preg_match('/^([a-zA-Z0-9_\(\),\'"]+)/', $typeAndFlags, $typeMatch);
                $tipoDado = strtoupper($typeMatch[1] ?? 'VARCHAR(60)');

                $isPk = isset($primaryKeys[$colName]) || preg_match('/PRIMARY\s+KEY/i', $typeAndFlags) ? 1 : 0;
                $isNn = preg_match('/NOT\s+NULL/i', $typeAndFlags) ? 1 : 0;
                $isAi = preg_match('/AUTO_INCREMENT/i', $typeAndFlags) ? 1 : 0;
                $isUq = isset($uniqueKeys[$colName]) || preg_match('/UNIQUE/i', $typeAndFlags) ? 1 : 0;

                // Salva o atributo na tabela atributo do mvc_creator
                $this->atributoService->insert($idTabela, null, $colName, $tipoDado, $isPk, $isNn, $isAi, $isUq);
            }
        }
    }
}
