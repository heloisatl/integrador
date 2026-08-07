<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Projeto;
use PDO;

class ProjetoRepository{
    private PDO $conn;
    
    public function __construct(){
        $this->conn =  ConnectionFactory::getConnection();
    }


    public function insert(Projeto $projeto){
        $sql = "INSERT INTO `mvc_creator`.`projeto`(id_usuario,fk_banco,fk_estilo,nome_projeto,data_criacao,status_permanencia,caminho_armazenamento,comentarios,views,ultimo_download)
         VALUES (?,?,?,?,?,?,?,?,?,?);";
        $stm = $this->conn->prepare($sql);

        return $stm->execute($this->projetoParams($projeto));
    }

    public function getById(int $id){
        $sql = "SELECT nome_projeto,comentarios,views FROM projeto WHERE id_projeto = ?;";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$id]);

        return $stm->fetch();
    }

    

    private function projetoParams(Projeto $projeto){
        
        return [$projeto->getFk_usuario() ?? 1,
        $projeto->getFk_banco() ?? 1,
        $projeto->getFk_estilo() ?? null,
        $projeto->getNome_projeto() ?? '',
        $projeto->getData_criacao() ?? 'NOW',
        60,
        $projeto->getCaminho_armazenamento() ?? 'isso/Nem/Ta/Definido',
        $projeto->getComentarios() ? 1 : 0 ,
        $projeto->getViews() ? 1 : 0,
        null];
    }

    public function getDatabases($dsn,$user,$pass){
        $specialConn = ProjetoRepository::specialConn($dsn,$user,$pass);
        $sql = "SHOW DATABASES";
        $stm = $specialConn->prepare($sql);
        $stm->execute();
        $databases = $stm->fetchAll(PDO::FETCH_ASSOC);
        $aa = "";
        foreach($databases as $database){
            $aa .= "<option>". $database['Database'] ."</option>\n";
        }
        unset($specialConn);
        // print $aa;
        return $aa;
    }

    //CELSO REVISAR ISSO AQUI - FELIPE
    public function getTabelas($dsn, $user, $pass, $banco) {
        $dsnComBanco = "$dsn;dbname=$banco";
        $specialConn = self::specialConn($dsnComBanco, $user, $pass);
        $sql = "SHOW TABLES";
        $stm = $specialConn->prepare($sql);
        $stm->execute();
        $tabelas = $stm->fetchAll(PDO::FETCH_COLUMN);
        unset($specialConn);
        return $tabelas;
    }

    public function getColunas($dsn, $user, $pass, $banco, $tabela) {
        $dsnComBanco = "$dsn;dbname=$banco";
        $specialConn = self::specialConn($dsnComBanco, $user, $pass);
        $sql = "SHOW COLUMNS FROM `$tabela`";
        $stm = $specialConn->prepare($sql);
        $stm->execute();
        $colunas = $stm->fetchAll(PDO::FETCH_ASSOC);
        unset($specialConn);
        return $colunas;
    }

    private static function specialConn($dsn,$user,$pass){
        // print $dsn;
        $connection = new PDO($dsn, $user, $pass);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $connection;
    }
}