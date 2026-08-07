<?php
require_once("../model/conexao.php");
class MobsDao {
    private $con;
    public function __construct(){
       $this->con=(new Conexao())->conectar();
    }
function inserir($obj) {
    $sql = "INSERT INTO mobs (nome, tipo, dificuldade, versao, imagem) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->con->prepare($sql);
    $nome=$obj->getNome();
$tipo=$obj->getTipo();
$dificuldade=$obj->getDificuldade();
$versao=$obj->getVersao();
$imagem=$obj->getImagem();

    $stmt->execute([$nome,$tipo,$dificuldade,$versao,$imagem]);
}
function listaGeral(){
    $sql = "select * from mobs";
    $query = $this->con->query($sql);
    $dados = $query->fetchAll(PDO::FETCH_ASSOC);
    return $dados;
}
function excluir($id){
    $sql = "delete from mobs where id=$id";
    $query = $this->con->query($sql);
    header("Location:../view/listaMobs.php");
}
function alterar($obj, $id){
    $nome=$obj->getNome();
$tipo=$obj->getTipo();
$dificuldade=$obj->getDificuldade();
$versao=$obj->getVersao();
$imagem=$obj->getImagem();

    $campos = [];
    $valores = [];
        $campos[] = 'nome = ?';
    $valores[] = $nome;
    $campos[] = 'tipo = ?';
    $valores[] = $tipo;
    $campos[] = 'dificuldade = ?';
    $valores[] = $dificuldade;
    $campos[] = 'versao = ?';
    $valores[] = $versao;
    $campos[] = 'imagem = ?';
    $valores[] = $imagem;
    
    $sql = "UPDATE mobs SET " . implode(", ", $campos) . " WHERE id = ?";
    $valores[] = $id;
    
    $stmt = $this->con->prepare($sql);
    $stmt->execute($valores);
    header("Location:../view/listaMobs.php");
}
function buscarId($id){
    $sql = "SELECT * FROM mobs WHERE id = ?";
    $stmt = $this->con->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>