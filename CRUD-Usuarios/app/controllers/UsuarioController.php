<?php

namespace app\controllers;

use app\core\Controller;
use app\services\UsuarioService;

class UsuarioController extends Controller {
    private UsuarioService $service;

    public function __construct() {
        $this->service = new UsuarioService();
    }

    public function index(): void {
        $this->adminRequired();

        $data['usuarios'] = $this->service->getUsuarios();

        $this->view('usuarios/usuario_list', $data);
    }

    public function excluir(): void {
        $this->adminRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/usuarios');
        }

        $id = (int) $_GET['id'];

        $this->service->deleteUsuario($id);

        $this->redirect(URL_BASE . '/usuarios');
    }

    public function cadastrar(): void {
        $this->view('usuarios/usuario_create');
    }

    public function salvar(): void {
        $this->redirect(URL_BASE . '/usuarios');
    }

    public function editar(): void {
        $this->redirect(URL_BASE . '/usuarios');
    }

    public function atualizar(): void {
        $this->redirect(URL_BASE . '/usuarios');
    }
}
