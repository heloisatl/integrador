<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Banco;
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

    public function updateConfig($id_banco, $nome_banco, $usuario_banco, $senha_banco, $host, $porta){
        $sql = "UPDATE banco SET nome_banco = :nome_banco, usuario_banco = :usuario_banco, senha_banco = :senha_banco, host = :host, porta = :porta WHERE id_banco = :id_banco;";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':nome_banco', $nome_banco);
        $stm->bindValue(':usuario_banco', $usuario_banco);
        $stm->bindValue(':senha_banco', $senha_banco);
        $stm->bindValue(':host', $host);
        $stm->bindValue(':porta', $porta);
        $stm->bindValue(':id_banco', $id_banco);
        return $stm->execute();
    }

    public function delete($id_banco){
        /**
         * Como funciona e o que faz:
         * 1. A tabela `atributo` possui uma constraint de auto-relacionamento (`fk_atributo_atributo`) 
         *    configurada com 'ON DELETE RESTRICT'.
         * 2. Quando o banco é excluído, o MySQL tenta excluir em cascata: banco -> tabela -> atributo.
         * 3. Se houver algum atributo referenciando outro (ex: Foreign Key apontando para uma Primary Key),
         *    o MySQL bloqueia a exclusão com o erro 1451 (Integrity constraint violation).
         * 4. Para evitar esse bloqueio, desvinculamos previamente as Foreign Keys (`fk_atributo = NULL`)
         *    de todos os atributos pertencentes às tabelas deste banco. Com isso, os registros ficam livres
         *    para que o MySQL realize a exclusão em cascata completa e com total integridade.
         */
        $sqlCleanFks = "UPDATE atributo SET fk_atributo = NULL 
                        WHERE fk_tabela IN (SELECT id_tabela FROM tabela WHERE fk_banco = :id_banco)";
        $stmClean = $this->conn->prepare($sqlCleanFks);
        $stmClean->bindValue(':id_banco', $id_banco, PDO::PARAM_INT);
        $stmClean->execute();

        $sql = "DELETE FROM banco WHERE id_banco = :id_banco;";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id_banco', $id_banco, PDO::PARAM_INT);
        return $stm->execute();
    }

    public function getBancoObjetoById(int $id): ?Banco {
        $dados = $this->getBancoById($id);
        if (!$dados) {
            return null;
        }
        return Banco::arrayParaObjeto($dados);
    }

    public function insertModel(Banco $banco): bool {
        return $this->insert(
            $banco->getFkUsuario(),
            $banco->getNomeBanco(),
            $banco->getUsuarioBanco(),
            $banco->getSenhaBanco(),
            $banco->getHost(),
            $banco->getPorta()
        );
    }

    public function updateModel(Banco $banco): bool {
        if ($banco->getIdBanco() === null) {
            return false;
        }

        return $this->updateConfig(
            $banco->getIdBanco(),
            $banco->getNomeBanco(),
            $banco->getUsuarioBanco(),
            $banco->getSenhaBanco(),
            $banco->getHost(),
            $banco->getPorta()
        );
    }
}