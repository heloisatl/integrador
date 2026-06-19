<?php

namespace app\services;

use app\repositories\UsuarioRepository;

class AutenticacaoService {
    private UsuarioRepository $usuarioRepository;

    public function __construct() {
        $this->usuarioRepository = new UsuarioRepository();
    }

    public function logar(string $email, string $senha): bool {
        $usuario = $this->usuarioRepository->getUsuarioByEmail($email);

        if ($usuario && password_verify($senha, $usuario->getSenhaUsuario())) {
            $_SESSION['usuario_logado'] = $usuario;
            return true;
        }

        return false;
    }

    public function logout(): void {
        session_destroy();
    }
}
