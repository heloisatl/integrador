<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Usuario;
use PDO;

class UsuarioRepository {
    private PDO $connection;

    public function __construct() {
        $this->connection = ConnectionFactory::getConnection();
    }

    public function getUsuarios(): array {
        $sql  = "SELECT id_usuario, nome, email, tipo_perfil FROM usuario ORDER BY id_usuario ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getUsuarioById(int $id): array|false {
        $sql  = "SELECT id_usuario, nome, email, tipo_perfil FROM usuario WHERE id_usuario = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getUsuarioByEmail(string $email): Usuario|false {
        $sql  = "SELECT * FROM usuario WHERE email = :email";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $dados = $stmt->fetch();

        if (!$dados) {
            return false;
        }

        return Usuario::arrayParaObjeto($dados);
    }

    public function deleteUsuario(int $id): bool {
        $sql  = "DELETE FROM usuario WHERE id_usuario = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
