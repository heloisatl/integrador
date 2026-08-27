<?php
namespace app\services;

use app\repositories\BancoRepository;

class BancoService{
    private BancoRepository $banco_repository;



    public function __construct(){
        $this->banco_repository = new BancoRepository();
    }



    public function insert($fk_usuario,$nome_banco,$usuario_banco,$senha_banco,$host,$porta){
        $result = null;
        if($this->getBanco($nome_banco,'nome_banco')){
            if(!$this->getBanco($usuario_banco,'usuario_banco')){
                $result = $this->banco_repository->insert($fk_usuario,$nome_banco,$usuario_banco,$senha_banco,$host,$porta);
            }
        }else{
            $result = $this->banco_repository->insert($fk_usuario,$nome_banco,$usuario_banco,$senha_banco,$host,$porta);
        }
        

        return $result;
    }

    public function getBancoById($value){
        $result = $this->banco_repository->getBancoById($value);

        return $result;
    }
    public function getBanco($value,$param = 'id_banco'){
        $result = $this->banco_repository->getBanco($value,$param);

        return $result;
    }

    public function getBancoEspecifico($nome_banco,$usuario_banco,$fk_usuario){
        $result = $this->banco_repository->getBancoEspecifico($nome_banco,$usuario_banco,$fk_usuario);

        return $result;
    }

    public function getBancoByUsuario($fk_usuario){
        $result = $this->banco_repository->getBancoByUsuario($fk_usuario);

        return $result;
    }

    public function getAllBancos(){
        $result = $this->banco_repository->getAllBancos();

        return $result;
    }
}