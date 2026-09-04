<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;


class BancoRepository{
    private PDO $conn;


    public function __construct(){
        $this->conn = ConnectionFactory::getConnection();
    }

    public function insert($fk_usuario,$nome_banco,$usuario_banco,$senha_banco,$host,$porta){
        $sql = "INSERT INTO banco(fk_usuario,nome_banco,usuario_banco,senha_banco,host,porta) VALUES (:fk_usuario,:nome_banco,:usuario_banco,:senha_banco,:host,:porta);";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':fk_usuario',$fk_usuario);
        $stm->bindValue(':nome_banco',$nome_banco);
        $stm->bindValue(':usuario_banco',$usuario_banco);
        $stm->bindValue(':senha_banco',$senha_banco);
        $stm->bindValue(':host',$host);
        $stm->bindValue(':porta',$porta);
        return $stm->execute();
    }

    public function getBancoById($value){
        $sql = 'SELECT * FROM banco WHERE ? = banco.id_banco;';
        $stm = $this->conn->prepare($sql);
        $stm->execute([$value]);
        return $stm->fetch();
    }

    public function getBanco($value, $param){
        $sql = "SELECT * FROM banco WHERE ? = $param;";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$value]);
        return $stm->fetchAll();
    }

    public function getBancoByUsuario($fk_usuario,$opt,$sel){
        $sql = "SELECT * FROM banco WHERE ? = banco.fk_usuario;";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$fk_usuario]);
        $result = $stm->fetchAll();
        switch(strtolower($opt)){
            case'db_options':
                $options = "";
                foreach($result as $banco){
                    $options .= $banco['id_banco']==$sel ? "<option value='".$banco['id_banco']."' selected>".$banco['nome_banco']."</option>" : "<option value='".$banco['id_banco']."'>".$banco['nome_banco']."</option>";
                }
            return $options;
            
            default:

            return $result;
        }
    }

    public function getBancoEspecifico($nome_banco,$usuario_banco,$fk_usuario){
        $sql = "SELECT * FROM banco WHERE ? = banco.nome_banco AND ? = banco.usuario_banco AND ? = banco.fk_usuario;";
        $stm = $this->conn->prepare($sql);
        $stm->execute([$nome_banco,$usuario_banco,$fk_usuario]);
        return $stm->fetch();
    }

    public function getAllBancos(){
        $sql = "SELECT * FROM banco";
        $stm = $this->conn->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_NUM);
    }
 
}