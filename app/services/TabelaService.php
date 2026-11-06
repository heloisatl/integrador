<?php
namespace app\services;

use app\repositories\TabelaRepository;

class TabelaService{
    private ?TabelaRepository $tabela_repository = null;

    public function __construct(){
    }

    private function repo(): TabelaRepository {
        if ($this->tabela_repository === null) {
            $this->tabela_repository = new TabelaRepository();
        }
        return $this->tabela_repository;
    }


    public function insert($nome,$fk_banco){
        $result = null;
        $tabelas = $this->repo()->getTabelasByFk_banco($fk_banco);
        $tabelaJaExiste = false;
        foreach($tabelas as $tabela){
            if($tabela->getNome_tabela() == $nome){
                $tabelaJaExiste = true;
                break;
            }
        }
        
        if(!$tabelaJaExiste){
            $result = $this->repo()->insert($nome,$fk_banco);
        }

        return $result;
    }
    

    public function getTabelasByFk_banco($id_banco,$option = "DEFAULT"){
        $result = $this->repo()->getTabelasByFk_banco($id_banco,$option);
        return $result;
    }

    public function getTabela($value,$param = "tabela.id_tabela"){
        $result = $this->repo()->getTabela($value,$param);
        return $result;
    }

    public function getTabelaEspecifica($nome_tabela,$fk_banco){
        $result = $this->repo()->getTabelaEspecifica($nome_tabela,$fk_banco);
        return $result;
    }

    public function getAllTabelas(){
        $result = $this->repo()->getAllTabelas();

        return $result;
    }

    public function getTabelaById($id){
        $result = $this->repo()->getTabelaById($id);
        return $result;
    }

    public function getTabelasRawByFk_banco($id_banco){
        return $this->repo()->getTabelasRawByFk_banco($id_banco);
    }

    public function updateNome($id_tabela, $nome_tabela){
        return $this->repo()->updateNome($id_tabela, $nome_tabela);
    }

    public function delete($id_tabela){
        return $this->repo()->delete($id_tabela);
    }
}