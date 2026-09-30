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
        $tabelas = $this->tabela_repository->getTabelasByFk_banco($fk_banco);
        $tabelaJaExiste = false;
        foreach($tabelas as $tabela){
            if($tabela->getNome_tabela() == $nome){
                $tabelaJaExiste = true;
                break;
            }
        }
        
        if(!$tabelaJaExiste){
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

    public function getTabelasRawByFk_banco($id_banco){
        return $this->tabela_repository->getTabelasRawByFk_banco($id_banco);
    }

    public function updateNome($id_tabela, $nome_tabela){
        return $this->tabela_repository->updateNome($id_tabela, $nome_tabela);
    }

    public function delete($id_tabela){
        return $this->tabela_repository->delete($id_tabela);
    }
}