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

    public function autenticacaoJsonRequired(): bool {
        if (!isset($_SESSION['usuario_logado'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Sessão expirada. Faça login novamente.']);
            exit();
        }

        return true;
    }

    public function adminRequired(): bool {
        $this->autenticacaoRequired();

        $usuario = $_SESSION['usuario_logado'] ?? null;
        $tipo = ($usuario && method_exists($usuario, 'getTipoPerfil')) ? strtolower(trim($usuario->getTipoPerfil())) : '';
        if (!in_array($tipo, ['admin', 'administrador'], true)) {
            $this->redirect(URL_BASE . '/login');
        }

        return true;
    }
}
