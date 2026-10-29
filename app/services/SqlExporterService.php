<?php

namespace app\services;

use Exception;

class SqlExporterService {
    private BancoService $bancoService;
    private TabelaService $tabelaService;
    private AtributoService $atributoService;

    public function __construct() {
        $this->bancoService    = new BancoService();
        $this->tabelaService   = new TabelaService();
        $this->atributoService = new AtributoService();
    }

    /**
     * Gera o script SQL atualizado para o banco especificado pertencente ao usuário.
     *
     * @param int $id_banco
     * @param int $id_usuario
     * @return array ['sucesso' => true, 'nome_banco' => string, 'nome_arquivo' => string, 'conteudo_sql' => string]
     * @throws Exception
     */
    public function exportarSql(int $id_banco, int $id_usuario): array {
        $banco = $this->bancoService->getBancoById($id_banco);
        if (!$banco || (int)$banco['fk_usuario'] !== (int)$id_usuario) {
            throw new Exception("Banco de dados não encontrado ou acesso não autorizado.");
        }

        $nomeBancoRaw = $banco['nome_banco'];
        $nomeBanco = preg_replace('/[^a-zA-Z0-9_]/', '_', $nomeBancoRaw);
        if (empty($nomeBanco)) {
            $nomeBanco = 'banco_' . $id_banco;
        }

        $tabelas = $this->tabelaService->getTabelasRawByFk_banco($id_banco);

        // Mapeia tabelas e atributos para resolução relacional e Foreign Keys
        $tabelasMap = [];
        $atributosPorTabela = [];
        $atributosGlobal = [];

        foreach ($tabelas as $tab) {
            $idTab = (int)$tab['id_tabela'];
            $tabelasMap[$idTab] = $tab['nome_tabela'];
            $attrs = $this->atributoService->getAtributosRawByFk_tabela($idTab);
            $atributosPorTabela[$idTab] = $attrs ?: [];

            foreach ($atributosPorTabela[$idTab] as $attr) {
                $idAttr = (int)$attr['id_atributo'];
                $atributosGlobal[$idAttr] = [
                    'id_atributo'   => $idAttr,
                    'nome_atributo' => $attr['nome_atributo'],
                    'id_tabela'     => $idTab,
                    'nome_tabela'   => $tab['nome_tabela']
                ];
            }
        }

        $dt = new \DateTime('now', new \DateTimeZone('America/Sao_Paulo'));
        $dataGeracao = $dt->format('d/m/Y H:i:s');
        $sql = [];

        $sql[] = "-- ============================================================================";
        $sql[] = "-- DevStudio - Script SQL Exportado Atualizado";
        $sql[] = "-- Banco de Dados: `{$nomeBanco}`";
        $sql[] = "-- Data de Exportação: {$dataGeracao}";
        $sql[] = "-- Servidor de Referência: {$banco['host']}:{$banco['porta']}";
        $sql[] = "-- Módulo: PHPMeuAmigo";
        $sql[] = "-- ============================================================================";
        $sql[] = "";
        $sql[] = "SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;";
        $sql[] = "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;";
        $sql[] = "SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';";
        $sql[] = "";
        $sql[] = "CREATE SCHEMA IF NOT EXISTS `{$nomeBanco}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        $sql[] = "USE `{$nomeBanco}`;";
        $sql[] = "";

        if (empty($tabelas)) {
            $sql[] = "-- (Nenhuma tabela modelada neste banco de dados até o momento)";
            $sql[] = "";
        }

        foreach ($tabelas as $tab) {
            $idTab = (int)$tab['id_tabela'];
            $nomeTabela = $tab['nome_tabela'];
            $attrs = $atributosPorTabela[$idTab] ?? [];

            $sql[] = "-- -----------------------------------------------------";
            $sql[] = "-- Estrutura da tabela `{$nomeTabela}`";
            $sql[] = "-- -----------------------------------------------------";
            $sql[] = "DROP TABLE IF EXISTS `{$nomeTabela}`;";
            $sql[] = "CREATE TABLE `{$nomeTabela}` (";

            $corpoTabela = [];
            $primaryKeys = [];
            $uniques     = [];
            $fks         = [];

            if (empty($attrs)) {
                // Tabela sem colunas definidas: define chave primária padrão para integridade
                $corpoTabela[] = "  `id` INT NOT NULL AUTO_INCREMENT";
                $primaryKeys[] = "id";
            } else {
                foreach ($attrs as $attr) {
                    $nomeCol = $attr['nome_atributo'];
                    $tipoCol = $this->formatarTipoDado($attr['tipo'] ?? 'int');
                    $isPk    = (int)($attr['PK'] ?? 0) === 1;
                    $isNn    = (int)($attr['NN'] ?? 0) === 1;
                    $isAi    = (int)($attr['AI'] ?? 0) === 1;
                    $isUq    = (int)($attr['UQ'] ?? 0) === 1;

                    $colDef = "  `{$nomeCol}` {$tipoCol}";

                    if ($isAi) {
                        $colDef .= " NOT NULL AUTO_INCREMENT";
                    } elseif ($isNn) {
                        $colDef .= " NOT NULL";
                    } else {
                        $colDef .= " NULL DEFAULT NULL";
                    }

                    $corpoTabela[] = $colDef;

                    if ($isPk) {
                        $primaryKeys[] = $nomeCol;
                    }

                    if ($isUq && !$isPk) {
                        $uniques[] = $nomeCol;
                    }

                    // Verifica se possui Foreign Key
                    if (!empty($attr['fk_atributo'])) {
                        $fkAttrId = (int)$attr['fk_atributo'];
                        if (isset($atributosGlobal[$fkAttrId])) {
                            $destino = $atributosGlobal[$fkAttrId];
                            $fks[] = [
                                'coluna_origem'  => $nomeCol,
                                'tabela_destino' => $destino['nome_tabela'],
                                'coluna_destino' => $destino['nome_atributo']
                            ];
                        }
                    }
                }
            }

            // Cláusula PRIMARY KEY
            if (!empty($primaryKeys)) {
                $pksEscaped = array_map(fn($k) => "`{$k}`", $primaryKeys);
                $corpoTabela[] = "  PRIMARY KEY (" . implode(', ', $pksEscaped) . ")";
            }

            // Cláusulas UNIQUE KEY
            foreach ($uniques as $uqCol) {
                $uqName = substr("uq_{$nomeTabela}_{$uqCol}", 0, 60);
                $corpoTabela[] = "  UNIQUE KEY `{$uqName}` (`{$uqCol}`)";
            }

            // Índices e Cláusulas FOREIGN KEY
            foreach ($fks as $fk) {
                $colOrigem   = $fk['coluna_origem'];
                $tabDestino  = $fk['tabela_destino'];
                $colDestino  = $fk['coluna_destino'];
                $idxName     = substr("idx_{$nomeTabela}_{$colOrigem}", 0, 60);
                $fkName      = substr("fk_{$nomeTabela}_{$tabDestino}_{$colOrigem}", 0, 60);

                $corpoTabela[] = "  KEY `{$idxName}` (`{$colOrigem}`)";
                $corpoTabela[] = "  CONSTRAINT `{$fkName}` FOREIGN KEY (`{$colOrigem}`) REFERENCES `{$tabDestino}` (`{$colDestino}`) ON DELETE CASCADE ON UPDATE CASCADE";
            }

            $sql[] = implode(",\n", $corpoTabela);
            $sql[] = ") ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;";
            $sql[] = "";
        }

        $sql[] = "SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;";
        $sql[] = "SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;";
        $sql[] = "SET SQL_MODE=@OLD_SQL_MODE;";
        $sql[] = "";

        $conteudoSql = implode("\n", $sql);
        $nomeArquivo = "{$nomeBanco}.sql";

        return [
            'sucesso'      => true,
            'nome_banco'   => $nomeBanco,
            'nome_arquivo' => $nomeArquivo,
            'conteudo_sql' => $conteudoSql
        ];
    }

    /**
     * Normaliza e formata o tipo de dado SQL de forma segura e padronizada.
     *
     * @param string $tipo
     * @return string
     */
    private function formatarTipoDado(string $tipo): string {
        $tipoLimpo = trim($tipo);
        if (empty($tipoLimpo)) {
            return 'VARCHAR(60)';
        }

        // Se for enum sem opções explícitas, provê um conjunto padrão válido
        if (strcasecmp($tipoLimpo, 'enum') === 0) {
            return "ENUM('ativo','inativo')";
        }

        // Se contiver especificação entre parênteses como varchar(255), decimal(10,2) ou enum(...)
        if (preg_match('/^([a-zA-Z]+)(\(.*\))$/', $tipoLimpo, $matches)) {
            return strtoupper($matches[1]) . $matches[2];
        }

        return strtoupper($tipoLimpo);
    }
}
