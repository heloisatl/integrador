<?php
namespace app\models;



class Projeto{
    private int $id_projeto;
    private int $fk_usuario;
    private int $fk_banco;
    private ?int $fk_estilo;
    private string $nome_projeto;
    private string $data_criacao;
    private string $caminho_armazenamento;
    private $comentarios = false;
    private $views = false;
    private ?int $ultimo_download;

    
    public function __construct($user,$banco,$estilo,$nome,$dataC,array $options,$ultDown){
        $this->fk_usuario = $user;
        $this->fk_banco = $banco;
        $this->fk_estilo = $estilo;
        $this->nome_projeto = $nome;
        $this->data_criacao = $dataC;
        $this->caminho_armazenamento = "caminho/ainda/nao/definido/";

        foreach($options as $key=>$value){
            $this->$key = $value;
        }

        $this->ultimo_download = $ultDown;

    }


    // GETTERS
    public function getId_projeto(){
        return $this->id_projeto;
    }
    public function getFk_usuario(){
        return $this->fk_usuario;
    }
    public function getFk_banco(){
        return $this->fk_banco;
    }
    public function getFk_estilo(){
        return $this->fk_estilo;
    }
    public function getNome_projeto(){
        return $this->nome_projeto;
    }
    public function getData_criacao(){
        return $this->data_criacao;
    }
    public function getCaminho_armazenamento(){
        return $this->caminho_armazenamento;
    }
    public function getComentarios(){
        return $this->comentarios;
    }
    public function getViews(){
        return $this->views;
    }
    public function getUltimo_download(){
        return $this->ultimo_download;
    }

    
    // SETTERS
    public function setComentarios($comentarios){
        $this->comentarios = $comentarios;
    }
    public function setViews($views){
        $this->views = $views;
    }
}