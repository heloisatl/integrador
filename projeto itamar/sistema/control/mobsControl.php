<?php
require_once("../model/mobs.php");
require_once("../dao/mobsDao.php");
class MobsControl {
    private $mobs;
    private $acao;
    private $dao;
    public function __construct(){
       $this->mobs=new Mobs();
      $this->dao=new MobsDao();
      $this->acao=$_GET["a"];
      $this->verificaAcao(); 
    }
    function verificaAcao(){
       switch($this->acao){
          case 1:
            $this->inserir();
          break;
          case 2:
            $this->excluir();
          break;
          case 3:
            $this->alterar();
          break;
       }
    }
  
    function inserir(){
        $this->mobs->setNome($_POST['nome']);
		$this->mobs->setTipo($_POST['tipo']);
		$this->mobs->setDificuldade($_POST['dificuldade']);
		$this->mobs->setVersao($_POST['versao']);
		$this->mobs->setImagem($_POST['imagem']);
		
        $this->dao->inserir($this->mobs);
    }
    function excluir(){
        $this->dao->excluir($_REQUEST['id']);
    }
    function alterar(){
        $this->mobs->setNome($_POST['nome']);
		$this->mobs->setTipo($_POST['tipo']);
		$this->mobs->setDificuldade($_POST['dificuldade']);
		$this->mobs->setVersao($_POST['versao']);
		$this->mobs->setImagem($_POST['imagem']);
		
        $this->dao->alterar($this->mobs, $_POST['id']);
    }
    function buscarId($id){
        return $this->dao->buscarId($id);
    }
    function buscaTodos(){}

}
new MobsControl();
?>