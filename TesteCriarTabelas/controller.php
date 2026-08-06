<?php
require_once "db.php";

class Controller {

    public function listarBancos($conn) {
        $stmt = $conn->query("SHOW DATABASES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listarTabelas($conn) {
        $stmt = $conn->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function descreverTabela($conn, $tabela) {
        $stmt = $conn->query("DESCRIBE `$tabela`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Constrói e executa o CREATE TABLE a partir dos dados dinâmicos do formulário.
     * Espera $_POST['mvc-campos'] como array de:
     *   [ nome, tipo, tamanho, pk, nn, ai ]
     */
    public function criarTabela($conn, $nomeTabela, $mvc-campos) {
        $partes = [];
        $temPK  = false;

        foreach ($mvc-campos as $mvc-campo) {
            $nome    = trim($mvc-campo['nome'] ?? '');
            $tipo    = $mvc-campo['tipo']    ?? 'VARCHAR';
            $tamanho = trim($mvc-campo['tamanho'] ?? '');
            $pk      = !empty($mvc-campo['pk']);
            $nn      = !empty($mvc-campo['nn']);
            $ai      = !empty($mvc-campo['ai']);

            if ($nome === '') continue;

            // Tipos sem tamanho
            $semTamanho = ['INT','TINYINT','SMALLINT','MEDIUMINT','BIGINT',
                           'TEXT','MEDIUMTEXT','LONGTEXT','DATE','DATETIME',
                           'TIMESTAMP','BOOLEAN','FLOAT','DOUBLE'];

            if ($tamanho !== '' && !in_array(strtoupper($tipo), $semTamanho)) {
                $definicao = "`$nome` $tipo($tamanho)";
            } else {
                $definicao = "`$nome` $tipo";
            }

            if ($nn || $pk) $definicao .= " NOT NULL";
            if ($ai)        $definicao .= " AUTO_INCREMENT";
            if ($pk) {
                $temPK = true;
                $definicao .= " PRIMARY KEY";
            }

            $partes[] = $definicao;
        }

        if (empty($partes)) {
            throw new Exception("Nenhuma coluna válida informada.");
        }

        $sql = "CREATE TABLE `$nomeTabela` (" . implode(", ", $partes) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->exec($sql);
        return $sql; // retorna para feedback
    }
}