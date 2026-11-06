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
        $sql = "INSERT INTO `mvc_creator`.`projeto`(id_usuario,fk_banco,fk_estilo,nome_projeto,data_criacao,prazo_de_vida,caminho_armazenamento,comentarios,views,ultimo_download)
         VALUES (?,?,?,?,?,?,?,?,?,?);";
        $stm = $this->conn->prepare($sql);
        $stm->execute($this->projetoParams($projeto));
        return $this->conn->lastInsertId() ? $this->conn->lastInsertId() : false;
    }

    /**
     * Mantém o limite máximo de 10 projetos armazenados por usuário autenticado (RN 01),
     * removendo os registros mais antigos caso a cota seja ultrapassada.
     */
    public function limitarCotaPorUsuario(int $idUsuario, int $limite = 10): void {
        if ($idUsuario <= 0 || $limite <= 0) {
            return;
        }
        $sqlIds = "SELECT id_projeto FROM projeto WHERE id_usuario = :id_usuario ORDER BY data_criacao DESC, id_projeto DESC";
        $stm = $this->conn->prepare($sqlIds);
        $stm->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stm->execute();
        $ids = $stm->fetchAll(PDO::FETCH_COLUMN);

        if (count($ids) > $limite) {
            $excedentes = array_slice($ids, $limite);
            $placeholders = implode(',', array_fill(0, count($excedentes), '?'));
            $sqlDelete = "DELETE FROM projeto WHERE id_usuario = ? AND id_projeto IN ({$placeholders})";
            $stmDel = $this->conn->prepare($sqlDelete);
            $stmDel->execute(array_merge([$idUsuario], array_map('intval', $excedentes)));
        }
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