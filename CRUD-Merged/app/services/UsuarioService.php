<?php

namespace app\services;

use app\models\Usuario;
use app\repositories\UsuarioRepository;

class UsuarioService {
    private UsuarioRepository $repository;

    public function __construct() {
        $this->repository = new UsuarioRepository();
    }

    public function getUsuarios(): array {
        return $this->repository->getUsuarios();
    }

    public function getUsuarioById(int $id): array|false {
        return $this->repository->getUsuarioById($id);
    }

    public function getUsuarioPorEmail(string $email): Usuario|false {
        return $this->repository->getUsuarioByEmail($email);
    }

    public function updateSenhaPorEmail(string $email, string $senha): bool {
        return $this->repository->updateSenhaPorEmail($email, $senha);
    }

    public function deleteUsuario(int $id): bool {
        $usuario = $this->repository->getUsuarioById($id);

        if (!$usuario) {
            return false;
        }

        return $this->repository->deleteUsuario($id);
    }

    public function createUsuario(array $dados): bool {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $senha = (string) ($dados['senha'] ?? '');
        $tipoPerfil = trim((string) ($dados['tipo_perfil'] ?? ''));

        if ($nome === '' || $email === '' || $senha === '' || $tipoPerfil === '') {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($this->repository->emailExiste($email)) {
            return false;
        }

        $dados['nome'] = $nome;
        $dados['email'] = $email;
        $dados['tipo_perfil'] = $tipoPerfil;

        return $this->repository->createUsuario($dados);
    }

    public function updateUsuario(int $id, array $dados): bool {
        if (empty($dados['nome']) || empty($dados['email'])) {
            return false;
        }

        return $this->repository->updateUsuario($id, $dados);
    }
}
