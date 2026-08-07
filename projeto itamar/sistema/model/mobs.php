<?php
class Mobs {
	private $id;
	private $nome;
	private $tipo;
	private $dificuldade;
	private $versao;
	private $imagem;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id=$id;
	}
	function getNome(){
		return $this->nome;
	}
	function setNome($nome){
		$this->nome=$nome;
	}
	function getTipo(){
		return $this->tipo;
	}
	function setTipo($tipo){
		$this->tipo=$tipo;
	}
	function getDificuldade(){
		return $this->dificuldade;
	}
	function setDificuldade($dificuldade){
		$this->dificuldade=$dificuldade;
	}
	function getVersao(){
		return $this->versao;
	}
	function setVersao($versao){
		$this->versao=$versao;
	}
	function getImagem(){
		return $this->imagem;
	}
	function setImagem($imagem){
		$this->imagem=$imagem;
	}

}
?>