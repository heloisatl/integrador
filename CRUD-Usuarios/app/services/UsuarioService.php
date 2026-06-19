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

    public function deleteUsuario(int $id): bool {
        $usuario = $this->repository->getUsuarioById($id);

        if (!$usuario) {
            return false;
        }

        return $this->repository->deleteUsuario($id);
    }

    public function createUsuario(array $dados): bool {
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            return false;
        }

        return $this->repository->createUsuario($dados);
    }

    public function updateUsuario(int $id, array $dados): bool {
        if (empty($dados['nome']) || empty($dados['email'])) {
            return false;
        }

        return $this->repository->updateUsuario($id, $dados);
    }
}
