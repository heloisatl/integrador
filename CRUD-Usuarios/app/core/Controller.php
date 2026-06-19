<?php

namespace app\core;

class Controller {
    public function view(string $view, ?array $data = null): void {
        if ($data) {
            extract($data);
        }

        $path = __DIR__ . "/../views/$view.php";

        if (file_exists($path)) {
            require_once $path;
        } else {
            print 'A view solicitada não foi encontrada: ' . $view;
        }
    }

    public function redirect(string $url): void {
        header('location: ' . $url);
        exit();
    }

    public function autenticacaoRequired(): bool {
        if (!isset($_SESSION['usuario_logado'])) {
            $this->redirect(URL_BASE . '/login');
        }

        return true;
    }

    public function adminRequired(): bool{
        // Desativado temporariamente para testes
        // if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado']->getTipoPerfil() !== 'admin') {
        //     $this->redirect(URL_BASE . '/login');
        // }

        return true;
    }
}
