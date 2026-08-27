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

    protected function requisicaoJson(): bool {
        return isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
    }

    protected function responderJson(string $status, string $mensagem, ?string $redirect = null): never {
        header('Content-Type: application/json; charset=utf-8');
        $resposta = ['status' => $status, 'mensagem' => $mensagem];

        if ($redirect !== null) {
            $resposta['redirect'] = $redirect;
        }

        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function autenticacaoRequired(): bool {
        if (!isset($_SESSION['usuario_logado'])) {
            $this->redirect(URL_BASE . '/login');
        }

        return true;
    }

    public function adminRequired(): bool {
        $this->autenticacaoRequired();

        if (!isset($_SESSION['usuario_logado']) || strtolower($_SESSION['usuario_logado']->getTipoPerfil()) !== 'admin') {
            $this->redirect(URL_BASE . '/login');
        }

        return true;
    }
}
