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
        $sql  = "SELECT id_usuario, nome, email, usuario_banco, servidor, tipo_perfil FROM usuario WHERE id_usuario = :id";
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

    public function createUsuario(array $dados): bool {
        $sql = "INSERT INTO usuario (nome, email, senha_usuario, usuario_banco, servidor, tipo_perfil) 
                VALUES (:nome, :email, :senha, :usuario_banco, :servidor, :tipo_perfil)";
        $stmt = $this->connection->prepare($sql);
        
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':senha', password_hash($dados['senha'], PASSWORD_DEFAULT));
        $stmt->bindValue(':usuario_banco', $dados['usuario_banco'] ?? '');
        $stmt->bindValue(':servidor', $dados['servidor'] ?? 'localhost');
        $stmt->bindValue(':tipo_perfil', $dados['tipo_perfil']);

        return $stmt->execute();
    }

    public function updateUsuario(int $id, array $dados): bool {
        $sql = "UPDATE usuario SET 
                nome = :nome, 
                email = :email, 
                usuario_banco = :usuario_banco, 
                servidor = :servidor, 
                tipo_perfil = :tipo_perfil";
        
        if (!empty($dados['senha'])) {
            $sql .= ", senha_usuario = :senha";
        }
        
        $sql .= " WHERE id_usuario = :id";
        
        $stmt = $this->connection->prepare($sql);
        
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':usuario_banco', $dados['usuario_banco'] ?? '');
        $stmt->bindValue(':servidor', $dados['servidor'] ?? 'localhost');
        $stmt->bindValue(':tipo_perfil', $dados['tipo_perfil']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if (!empty($dados['senha'])) {
            $stmt->bindValue(':senha', password_hash($dados['senha'], PASSWORD_DEFAULT));
        }

        return $stmt->execute();
    }
} 