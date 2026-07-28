<?php

namespace app\services;

use app\repositories\UsuarioRepository;

class AutenticacaoService {
    private UsuarioRepository $usuarioRepository;

    public function __construct() {
        $this->usuarioRepository = new UsuarioRepository();
    }

    public function logar(string $email, string $senha): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($email);
        $usuario = $this->usuarioRepository->getUsuarioByEmail($email);

        if ($usuario && password_verify($senha, $usuario->getSenhaUsuario())) {
            $_SESSION['usuario_logado'] = $usuario;
            return true;
        }

        return false;
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['usuario_logado']);
        session_destroy();
    }
}
