<?php

namespace app\services;

use Exception;

class SqlImporterService{
    private BancoService $bancoService;
    private TabelaService $tabelaService;
    private AtributoService $atributoService;

    public function __construct(){
        $this->bancoService    = new BancoService();
        $this->tabelaService   = new TabelaService();
        $this->atributoService = new AtributoService();
    }

    public function importarSql(string $filePath, int $id_usuario, ?string $nome_banco_manual = null): array{
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("Arquivo de dump SQL nao encontrado ou sem permissao de leitura.");
        }

        // Valida extensao; se for arquivo temporario sem extensao, verifica o conteudo
        $extensao = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extensao !== 'sql') {
            $primeiros_bytes = file_get_contents($filePath, false, null, 0, 500);
            if (!preg_match('/CREATE TABLE|INSERT INTO|-- phpMyAdmin/i', $primeiros_bytes)) {
                throw new Exception("Arquivo fornecido nao possui formato SQL valido.");
            }
        }

        // Usa o nome manual se fornecido, senao tenta extrair do proprio SQL
        $nome_banco = $nome_banco_manual ? trim($nome_banco_manual) : $this->extrairNomeBanco($filePath);
        if (empty($nome_banco)) {
            $nome_banco = 'banco_importado_' . date('Ymd_His');
        }

        $nome_banco = preg_replace('/[^a-zA-Z0-9_]/', '', $nome_banco);

        $this->bancoService->insert($id_usuario, $nome_banco, 'root', '', 'localhost', '3306');
        $banco_obj = $this->bancoService->getBancoEspecifico($nome_banco, 'root', $id_usuario);

        if (!$banco_obj) {
            throw new Exception("Erro ao registrar o banco no sistema.");
        }

        $id_banco = (int)$banco_obj['id_banco'];
        $tabelas_importadas = $this->parseStreamSql($filePath, $id_banco);

        return [
            'sucesso'       => true,
            'id_banco'      => $id_banco,
            'nome_banco'    => $nome_banco,
            'total_tabelas' => count($tabelas_importadas),
            'tabelas'       => array_keys($tabelas_importadas),
            'mensagem'      => sprintf("Importacao concluida! %d tabela(s) importada(s).", count($tabelas_importadas))
        ];
    }

    // Tenta encontrar um CREATE DATABASE ou USE no topo do arquivo
    private function extrairNomeBanco(string $filePath): string{
        $handle = fopen($filePath, 'r');
        if (!$handle) return '';

        $nome_banco = '';
        while (($line = fgets($handle)) !== false) {
            if (preg_match('/(?:CREATE\s+DATABASE|USE)\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $line, $matches)) {
                $nome_banco = $matches[1];
                break;
            }
        }
        fclose($handle);
        return $nome_banco;
    }

    // Le o arquivo linha a linha para evitar consumo excessivo de memoria com arquivos grandes
    private function parseStreamSql(string $filePath, int $id_banco): array{
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Falha ao abrir o arquivo para leitura.");
        }

        $tabelas_criadas      = [];
        $inside_create_table  = false;
        $current_table_content = '';

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);

            if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?([a-zA-Z0-9_]+)[`"]?/i', $line, $matches)) {
                $inside_create_table  = true;
                $table_name           = $matches[1];
                $current_table_content = $line;
                continue;
            }

            if ($inside_create_table) {
                $current_table_content .= $line;

                if (preg_match('/;\s*$/', $trimmed)) {
                    $inside_create_table = false;
                    $this->processarBlocoCreateTable($current_table_content, $id_banco, $table_name ?? 'tabela');
                    $tabelas_criadas[$table_name ?? 'tabela'] = true;
                    $current_table_content = '';
                }
            }
        }

        fclose($handle);
        return $tabelas_criadas;
    }

    private function processarBlocoCreateTable(string $sql_block, int $id_banco, string $nome_tabela): void{
        $this->tabelaService->insert($nome_tabela, $id_banco);
        $tabela_obj = $this->tabelaService->getTabelaEspecifica($nome_tabela, $id_banco);
        if (!$tabela_obj) return;

        $id_tabela = (int)$tabela_obj['id_tabela'];

        preg_match('/\((.*)\)[^)]*$/s', $sql_block, $matches);
        if (empty($matches[1])) return;

        $inner_content = $matches[1];
        $lines = explode("\n", $inner_content);

        $primary_keys = [];
        $unique_keys  = [];

        // Primeira passagem: coleta PKs e UQs declarados em linhas separadas no final do CREATE TABLE
        foreach ($lines as $line) {
            $line_trimmed = trim($line, " \t\n\r,");

            if (preg_match('/PRIMARY\s+KEY\s*\(([^)]+)\)/i', $line_trimmed, $pkMatch)) {
                foreach (explode(',', $pkMatch[1]) as $c) {
                    $primary_keys[trim($c, " `\"")] = true;
                }
            }

            if (preg_match('/UNIQUE\s+(?:KEY\s+)?[`"]?([a-zA-Z0-9_]+)?[`"]?\s*\(([^)]+)\)/i', $line_trimmed, $uqMatch)) {
                foreach (explode(',', $uqMatch[2]) as $c) {
                    $unique_keys[trim($c, " `\"")] = true;
                }
            }
        }

        // Segunda passagem: processa cada coluna
        foreach ($lines as $line) {
            $line_trimmed = trim($line, " \t\n\r,");

            if (empty($line_trimmed) ||
                preg_match('/^(?:PRIMARY\s+KEY|UNIQUE|KEY|INDEX|CONSTRAINT|FOREIGN\s+KEY)/i', $line_trimmed)) {
                continue;
            }

            if (preg_match('/^[`"]?([a-zA-Z0-9_]+)[`"]?\s+([a-zA-Z0-9_()\',"\s]+)/i', $line_trimmed, $colMatch)) {
                $col_name      = $colMatch[1];
                $type_and_flags = $colMatch[2];

                preg_match('/^([a-zA-Z0-9_()\',\"]+)/', $type_and_flags, $typeMatch);
                $tipo_dado = strtoupper($typeMatch[1] ?? 'VARCHAR(60)');

                $isPk = isset($primary_keys[$col_name]) || preg_match('/PRIMARY\s+KEY/i', $type_and_flags) ? 1 : 0;
                $isNn = preg_match('/NOT\s+NULL/i', $type_and_flags) ? 1 : 0;
                $isAi = preg_match('/AUTO_INCREMENT/i', $type_and_flags) ? 1 : 0;
                $isUq = isset($unique_keys[$col_name]) || preg_match('/UNIQUE/i', $type_and_flags) ? 1 : 0;

                $this->atributoService->insert($id_tabela, null, $col_name, $tipo_dado, $isPk, $isNn, $isAi, $isUq);
            }
        }
    }
}
