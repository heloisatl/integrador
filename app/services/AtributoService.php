<?php
namespace app\services;

use app\repositories\AtributoRepository;

class AtributoService{
    private ?AtributoRepository $atributo_repository = null;

    public function __construct(){
    }

    private function repo(): AtributoRepository {
        if ($this->atributo_repository === null) {
            $this->atributo_repository = new AtributoRepository();
        }
        return $this->atributo_repository;
    }


    public function insert($fk_tabela,$fk_atributo,$nome_atributo,$tipo,$PK,$NN,$AI,$UQ){
        $result = null;
        if($this->repo()->getAtributo($nome_atributo,'atributo.nome_atributo')){
            if(!$this->repo()->getAtributosByFk_tabela($fk_tabela)){
                $result = $this->repo()->insert($fk_tabela,$fk_atributo,$nome_atributo,$tipo,$PK,$NN,$AI,$UQ);
            }
        }else{
            $result = $this->repo()->insert($fk_tabela,$fk_atributo,$nome_atributo,$tipo,$PK,$NN,$AI,$UQ);
        }

        return $result;
    }

    public function getAtributo($value, $param = "atributo.id_atributo"){
        $result = $this->repo()->getAtributo($value,$param);

        return $result;
    }

    public function getAtributosByFk_tabela($id_tabela){
        $result = $this->repo()->getAtributosByFk_tabela($id_tabela);

        return $result;
    }

    public function getAtributoById($id){
        $result = $this->repo()->getAtributoById($id);

        return $result;
    }
    
    public function getAllAtributos(){
        $result = $this->repo()->getAllAtributos();

        return $result;
    }

    public function getAtributosRawByFk_tabela($id_tabela){
        return $this->repo()->getAtributosRawByFk_tabela($id_tabela);
    }

    public function update($id_atributo, $fk_atributo, $nome_atributo, $tipo, $PK, $NN, $AI, $UQ){
        return $this->repo()->update($id_atributo, $fk_atributo, $nome_atributo, $tipo, $PK, $NN, $AI, $UQ);
    }

    public function updateFkAtributo($id_atributo, $fk_atributo){
        return $this->repo()->updateFkAtributo($id_atributo, $fk_atributo);
    }

    public function delete($id_atributo){
        return $this->repo()->delete($id_atributo);
    }
}