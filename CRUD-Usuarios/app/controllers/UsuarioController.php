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
        $this->adminRequired();

        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'senha' => $_POST['senha'] ?? '',
            'usuario_banco' => $_POST['usuario_banco'] ?? '',
            'servidor' => $_POST['servidor'] ?? '',
            'tipo_perfil' => $_POST['tipo_perfil'] ?? '',
        ];

        if ($this->service->createUsuario($dados)) {
            $this->redirect(URL_BASE . '/usuarios');
        } else {
            $this->redirect(URL_BASE . '/usuarios/cadastrar');
        }
    }

    public function editar(): void {
        $this->adminRequired();

        if (!isset($_GET['id'])) {
            $this->redirect(URL_BASE . '/usuarios');
        }

        $id = (int) $_GET['id'];
        $data['usuario'] = $this->service->getUsuarioById($id);

        if (!$data['usuario']) {
            $this->redirect(URL_BASE . '/usuarios');
        }

        $this->view('usuarios/usuario_edit', $data);
    }

    public function atualizar(): void {
        $this->adminRequired();

        if (!isset($_POST['id'])) {
            $this->redirect(URL_BASE . '/usuarios');
        }

        $id = (int) $_POST['id'];

        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'senha' => $_POST['senha'] ?? '',
            'usuario_banco' => $_POST['usuario_banco'] ?? '',
            'servidor' => $_POST['servidor'] ?? '',
            'tipo_perfil' => $_POST['tipo_perfil'] ?? '',
        ];

        if ($this->service->updateUsuario($id, $dados)) {
            $this->redirect(URL_BASE . '/usuarios');
        } else {
            $this->redirect(URL_BASE . '/usuarios/editar?id=' . $id);
        }
    }
}
