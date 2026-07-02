<?php
namespace app\services;

use app\models\Projeto;
use app\repositories\ProjetoRepository;

class ProjetoService{
    private ProjetoRepository $projetoRepository;


    public function __construct(){
        $this->projetoRepository = new ProjetoRepository();
    }


    public function getDatabases($dsn,$user,$pass){
        return $this->projetoRepository->getDatabases($dsn,$user,$pass);
    }

    public function getById(int $id){   
        return $this->projetoRepository->getById($id);
    }

    public function insert(Projeto $projeto){
        

    
        // Service vai depois da Silva


        return $this->projetoRepository->insert($projeto);
    }


}



