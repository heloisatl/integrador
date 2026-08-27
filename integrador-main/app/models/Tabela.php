<?php
namespace app\models;

use app\repositories\ProjetoRepository;
use Throwable;

class Tabela{
    public readonly ?int $id_tabela;
    public readonly ?int $fk_banco;
    public readonly string $nome_tabela;
    public readonly string $nome_tabelaUC;
    // public readonly ?array $atributos;
    // private ?array $attInputs;



    public function __construct($id_tabela,$fk_banco,$nomeTabela){
        try{
            $projetoRepository = new ProjetoRepository();
            $this->id_tabela = $id_tabela;
            $this->fk_banco = $fk_banco;
            $this->nome_tabela = $nomeTabela;
            $this->nome_tabelaUC = ucfirst($this->nome_tabela);
            // $atributos = $projetoRepository->getAtributos($this->nome_tabela);
            // $this->attInputs = [];
           
            // foreach($this->atributos as $att){
            //     if($att->Key!='PRI')$this->attInputs[] = Tabela::inputs($att->Type,$att->Field);
            // }
        }catch(Throwable $th){
            throw $th;
        }
    }

    // public function getAttInputs(){
    //     return $this->attInputs;
    // }

    

    /**
     * Get the value of id_tabela
     */ 
    public function getId_tabela()
    {
        return $this->id_tabela;
    }

    /**
     * Get the value of fk_banco
     */ 
    public function getFk_banco()
    {
        return $this->fk_banco;
    }

    /**
     * Get the value of nome_tabelaUC
     */ 
    public function getNome_tabelaUC()
    {
        return $this->nome_tabelaUC;
    }

    /**
     * Get the value of nome_tabela
     */ 
    public function getNome_tabela()
    {
        return $this->nome_tabela;
    }
}
