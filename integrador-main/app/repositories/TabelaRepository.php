<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Tabela;
use PDO;
use ValueError;

class TabelaRepository{
    private PDO $conn;


    public function __construct(){
        $this->conn = ConnectionFactory::getConnection();
    }


    public function insert($nome,$fk_banco){
        $sql = "INSERT INTO tabela(nome_tabela,fk_banco) VALUES(:nome_tabela,:fk_banco)";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':nome_tabela',$nome);
        $stm->bindValue(':fk_banco',$fk_banco);
        return $stm->execute();
    }

    public function getTabelasByFk_banco($id_banco){
        $sql = "SELECT tabela.* FROM tabela WHERE tabela.fk_banco = ?";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$id_banco]);
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        return $this->mapTabela($result);
    }

    public function getTabelaEspecifica($nome_tabela,$fk_banco){
        $sql = "SELECT tabela.* FROM tabela WHERE tabela.nome_tabela = ? AND tabela.fk_banco = ?";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$nome_tabela,$fk_banco]);
        return $stm->fetch();
    }

    public function getTabelaById($id){
        $sql = "SELECT tabela.* FROM tabela WHERE ? = tabela.id_tabela";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$id]);
        return $stm->fetch();
    }
    
    public function getAllTabelas(){
        $sql = "SELECT tabela.* FROM tabela;";
        $stm = $this->conn->prepare($sql);
        $stm->execute();
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        return $this->mapTabela($result);
    }

    public function getTabela($value,$param){
        $sql = "SELECT * from tabela where ? = $param";       
        $stm = $this->conn->prepare($sql);
        $stm->execute([$value]);
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        return $this->mapTabela($result);
    }


    private function mapTabela($tabelas){
        $result = [];

        foreach($tabelas as $key => $tabela){
            $id_tabela = $tabela['id_tabela'];
            $fk_banco = $tabela['fk_banco'];
            $nome_tabela = $tabela['nome_tabela'];

            $result[] = new Tabela($id_tabela,$fk_banco,$nome_tabela);
        }

        return $result;
    }
}