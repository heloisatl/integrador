<?php
namespace app\services;

use app\repositories\AtributoRepository;

class AtributoService{
    private AtributoRepository $atributo_repository;

    public function __construct(){
        $this->atributo_repository = new AtributoRepository;
    }


    public function insert($fk_tabela,$fk_atributo,$nome_atributo,$tipo,$PK,$NN,$AI,$UQ){
        $result = null;
        if($this->atributo_repository->getAtributo($nome_atributo,'atributo.nome_atributo')){
            if(!$this->atributo_repository->getAtributosByFk_tabela($fk_tabela)){
                $result = $this->atributo_repository->insert($fk_tabela,$fk_atributo,$nome_atributo,$tipo,$PK,$NN,$AI,$UQ);
            }
        }else{
            $result = $this->atributo_repository->insert($fk_tabela,$fk_atributo,$nome_atributo,$tipo,$PK,$NN,$AI,$UQ);
        }

        

        return $result;
    }

    public function getAtributo($value, $param = "atributo.id_atributo"){
        $result = $this->atributo_repository->getAtributo($value,$param);

        return $result;
    }

    public function getAtributosByFk_tabela($id_tabela){
        $result = $this->atributo_repository->getAtributosByFk_tabela($id_tabela);

        return $result;
    }

    public function getAtributoById($id){
        $result = $this->atributo_repository->getAtributoById($id);

        return $result;
    }
    
    public function getAllAtributos(){
        $result = $this->atributo_repository->getAllAtributos();

        return $result;
    }


}