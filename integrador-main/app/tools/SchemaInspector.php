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
        $validTable = preg_match('/^[a-zA-Z0-9_]+$/',$nomeTabela) ? $nomeTabela : die('Invalid table name');
        $sql = "show columns from `$validTable`";
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

    
}