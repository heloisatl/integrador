<?php

namespace app\tools\gerador;

use app\models\Atributo;
use app\models\Tabela;


//Gera o database.sql do projeto a partir das tabelas SELECIONADAS e dos seus atributos.

class GeradorDatabase {
   
    public function gerarDatabase(string $nomeBanco, array $tabelas, array $atributosPorTabela): string {
        $sql = "-- Script gerado automaticamente pelo DevStudio (MVC Creator)\n";
        $sql .= "CREATE DATABASE IF NOT EXISTS " . $this->ident($nomeBanco) . " DEFAULT CHARACTER SET utf8mb4;\n";
        $sql .= "USE " . $this->ident($nomeBanco) . ";\n\n";

        // id_atributo => [tabela, coluna, pk/unique] apenas das tabelas selecionadas
        $mapa = [];
        foreach ($tabelas as $tabela) {
            foreach ($atributosPorTabela[$tabela->getId_tabela()] ?? [] as $att) {
                $mapa[(int) $att->getId_atributo()] = [
                    'tabela' => $tabela->getNome_tabela(),
                    'coluna' => $att->getNome_atributo(),
                    'indexada' => (bool) $att->getPk() || (bool) $att->getUq(),
                ];
            }
        }

        $fks = '';
        foreach ($tabelas as $tabela) {
            $atributos = $atributosPorTabela[$tabela->getId_tabela()] ?? [];
            $nomeTabela = $tabela->getNome_tabela();

            if (empty($atributos)) {
                $sql .= "-- Tabela " . $nomeTabela . " ignorada: nenhum atributo cadastrado.\n\n";
                continue;
            }

            $linhas = [];
            $pks = [];
            foreach ($atributos as $att) {
                $linhas[] = '    ' . $this->definirColuna($att);
                if ($att->getPk()) {
                    $pks[] = $this->ident($att->getNome_atributo());
                }
            }
            if (!empty($pks)) {
                $linhas[] = '    PRIMARY KEY (' . implode(', ', $pks) . ')';
            }

            $sql .= "CREATE TABLE IF NOT EXISTS " . $this->ident($nomeTabela) . " (\n";
            $sql .= implode(",\n", $linhas) . "\n";
            $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

            foreach ($atributos as $att) {
                $idRef = $att->getFk_atributo();
                if (empty($idRef)) {
                    continue;
                }
                $ref = $mapa[(int) $idRef] ?? null;
                $coluna = $att->getNome_atributo();

                if ($ref === null) {
                    $fks .= "-- FK omitida: {$nomeTabela}.{$coluna} referencia uma tabela que não foi selecionada.\n";
                    continue;
                }
                if (!$ref['indexada']) {
                    $fks .= "-- FK omitida: {$ref['tabela']}.{$ref['coluna']} não é PK nem UNIQUE.\n";
                    continue;
                }

                $constraint = substr('fk_' . $nomeTabela . '_' . $coluna, 0, 64);
                $fks .= 'ALTER TABLE ' . $this->ident($nomeTabela)
                    . ' ADD CONSTRAINT ' . $this->ident($constraint)
                    . ' FOREIGN KEY (' . $this->ident($coluna) . ')'
                    . ' REFERENCES ' . $this->ident($ref['tabela']) . ' (' . $this->ident($ref['coluna']) . ");\n";
            }
        }

        if ($fks !== '') {
            $sql .= "-- Chaves estrangeiras\n" . $fks;
        }

        return $sql;
    }

    public function salvarDatabase(string $nomeBanco, array $tabelas, array $atributosPorTabela, string $caminhoBase): string {
        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }
        $arquivoDestino = "{$caminhoBase}/database.sql";
        file_put_contents($arquivoDestino, $this->gerarDatabase($nomeBanco, $tabelas, $atributosPorTabela));
        return $arquivoDestino;
    }

    private function definirColuna(Atributo $att): string {
        $def = $this->ident($att->getNome_atributo()) . ' ' . $this->tipoSeguro((string) $att->getTipo());

        if ($att->getNn() || $att->getPk()) {
            $def .= ' NOT NULL';
        }
        if ($att->getAi()) {
            $def .= ' AUTO_INCREMENT';
        }
        if ($att->getUq() && !$att->getPk()) {
            $def .= ' UNIQUE';
        }
        return $def;
    }

    
    //Aceita apenas algo como "VARCHAR(60)", "INT UNSIGNED" ou "ENUM('a','b')". Qualquer outra
    //coisa (ex.: tentativa de injetar SQL no nome do tipo) vira VARCHAR(255).
    
    private function tipoSeguro(string $tipo): string {
        $tipo = trim($tipo);
        if (preg_match('/^[A-Za-z]+(\([^;\r\n]*\))?(\s+[A-Za-z]+)*$/', $tipo) === 1) {
            return $tipo;
        }
        return 'VARCHAR(255)';
    }

    private function ident(string $nome): string {
        return '`' . str_replace('`', '``', $nome) . '`';
    }
}
