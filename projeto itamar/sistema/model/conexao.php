
<?php
class Conexao {
    private $server;
    private $banco;
    private $usuario;
    private $senha;
    function __construct() {
        $this->server = 'localhost';
        $this->banco = 'minecraft_db';
        $this->usuario = 'felipe';
        $this->senha = 'bancodedados';
    }
    
    function conectar() {
        try {
            $conn = new PDO(
                "mysql:host=" . $this->server . ";dbname=" . $this->banco,$this->usuario,
                $this->senha
            );
            return $conn;
        } catch (Exception $e) {
            echo "Erro ao conectar com o Banco de dados: " . $e->getMessage();
        }
    }
}
?>