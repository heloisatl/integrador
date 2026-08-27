<?php
namespace app\services;

use app\repositories\TabelaRepository;

class TabelaService{
    private TabelaRepository $tabela_repository;

    public function __construct(){
        $this->tabela_repository = new TabelaRepository;
    }


    public function insert($nome,$fk_banco){
        $result = null;
        if($this->tabela_repository->getTabela($nome,'tabela.nome_tabela')){
            if(!$this->tabela_repository->getTabelasByFk_banco($fk_banco)){
                $result = $this->tabela_repository->insert($nome,$fk_banco);
            }
        }else{
            $result = $this->tabela_repository->insert($nome,$fk_banco);
        }

        
        return $result;
    }
    

    public function getTabelasByFk_banco($id_banco){
        $result = $this->tabela_repository->getTabelasByFk_banco($id_banco);
        return $result;
    }

    public function getTabela($value,$param = "tabela.id_tabela"){
        $result = $this->tabela_repository->getTabela($value,$param);
        return $result;
    }

    public function getTabelaEspecifica($nome_tabela,$fk_banco){
        $result = $this->tabela_repository->getTabelaEspecifica($nome_tabela,$fk_banco);
        return $result;
    }

    public function getAllTabelas(){
        $result = $this->tabela_repository->getAllTabelas();

        return $result;
    }

    public function getTabelaById($id){
        $result = $this->tabela_repository->getTabelaById($id);
        return $result;
    }


}