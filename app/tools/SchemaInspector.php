<?php
namespace app\tools;

use app\database\ConnectionFactory;
use app\models\Atributo;
use PDO;

class SchemaInspector{
    private PDO $conn;
    private PDO $specialConn;



    public function __construct($dsn,$user,$pass){
        $this->conn = ConnectionFactory::getConnection();
        $this->specialConn = ConnectionFactory::specialConn($dsn,$user,$pass);
    }

     public function getTabelas(){
        $sql = "SHOW TABLES";
        $stm = $this->specialConn->prepare($sql);
        $stm->execute([]);
        return $stm->fetchAll(PDO::FETCH_NUM);
    }

    

    public function getAtributos($nomeTabela){
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $nomeTabela)) {
            throw new \InvalidArgumentException("Nome de tabela invalido: $nomeTabela");
        }
        $sql = "SHOW COLUMNS FROM `$nomeTabela`";
        $stm = $this->specialConn->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDatabases($option){
        // $specialConn = ConnectionFactory::specialConn($dsn,$user,$pass);
        $sql = "SHOW DATABASES";
        $stm = $this->specialConn->prepare($sql);
        $stm->execute();
        $databases = $stm->fetchAll(PDO::FETCH_ASSOC);
        switch(strtolower($option)){
            case 'db_options':
                $ops = "";
                foreach($databases as $database){
                    $ops .= "<option>". $database['Database'] ."</option>\n";
                }
                unset($specialConn);
                // print $aa;
                return $ops;
            break;
            
            case 'default':
            default:
                return $databases;
            break;
        }
    }

    public function getReferenciaFk($nomeTabela, $nomeColuna = null){
        $sql = "SELECT 
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME,
                    CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND REFERENCED_TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = :tabela
                  AND REFERENCED_TABLE_NAME IS NOT NULL
                  AND REFERENCED_COLUMN_NAME IS NOT NULL";

        if ($nomeColuna !== null) {
            $sql .= " AND COLUMN_NAME = :coluna";
            $stm = $this->specialConn->prepare($sql);
            $stm->execute([
                ':tabela' => $nomeTabela,
                ':coluna' => $nomeColuna
            ]);
            $res = $stm->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        }

        $stm = $this->specialConn->prepare($sql);
        $stm->execute([':tabela' => $nomeTabela]);
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFkReferencia($nomeTabela, $nomeColuna = null){
        return $this->getReferenciaFk($nomeTabela, $nomeColuna);
    }
}