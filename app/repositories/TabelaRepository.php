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

    public function getTabelasByFk_banco($id_banco,$option = "DEFAULT"){
        $sql = "SELECT tabela.* FROM tabela WHERE tabela.fk_banco = ?";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$id_banco]);
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        switch(strtolower($option)){
            case'prox_etapa':

            return $id_banco;
            
            case 'default':
            default:
                
            return $this->mapTabela($result);
        }

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


    public function getTabelasRawByFk_banco($id_banco){
        $sql = "SELECT * FROM tabela WHERE fk_banco = ?";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$id_banco]);
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateNome($id_tabela, $nome_tabela){
        $sql = "UPDATE tabela SET nome_tabela = :nome_tabela WHERE id_tabela = :id_tabela;";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':nome_tabela', $nome_tabela);
        $stm->bindValue(':id_tabela', $id_tabela);
        return $stm->execute();
    }

    public function delete($id_tabela){
        /**
         * Como funciona e o que faz:
         * 1. Desvincula qualquer Foreign Key (`fk_atributo = NULL`) que aponte para atributos desta tabela
         *    ou que pertença a ela antes da exclusão.
         * 2. Isso impede que o MySQL dispare o erro 1451 (ON DELETE RESTRICT da constraint `fk_atributo_atributo`)
         *    quando a tabela e seus atributos forem removidos em cascata.
         */
        $sqlCleanFks = "UPDATE atributo SET fk_atributo = NULL 
                        WHERE fk_tabela = :id_tabela 
                           OR fk_atributo IN (SELECT id_atributo FROM (SELECT id_atributo FROM atributo WHERE fk_tabela = :id_tabela_sub) AS sub)";
        $stmClean = $this->conn->prepare($sqlCleanFks);
        $stmClean->bindValue(':id_tabela', $id_tabela, PDO::PARAM_INT);
        $stmClean->bindValue(':id_tabela_sub', $id_tabela, PDO::PARAM_INT);
        $stmClean->execute();

        $sql = "DELETE FROM tabela WHERE id_tabela = :id_tabela;";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id_tabela', $id_tabela, PDO::PARAM_INT);
        return $stm->execute();
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