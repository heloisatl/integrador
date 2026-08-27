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

    public function perfil(): void {
        $this->autenticacaoRequired();

        $usuarioLogado = $_SESSION['usuario_logado'];
        $data['usuario'] = $this->service->getUsuarioById($usuarioLogado->getIdUsuario());
        $data['erro'] = $_SESSION['flash_erro'] ?? '';
        unset($_SESSION['flash_erro']);

        $this->view('usuarios/perfil', $data);
    }

    public function atualizarPerfil(): void {
        $this->autenticacaoRequired();

        $usuarioLogado = $_SESSION['usuario_logado'];
        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'senha_atual' => $_POST['senha_atual'] ?? '',
            'senha' => $_POST['senha'] ?? '',
            'confirmacao' => $_POST['confirmacao'] ?? '',
        ];

        if ($this->service->updatePerfil($usuarioLogado->getIdUsuario(), $dados)) {
            $_SESSION['usuario_logado'] = $this->service->getUsuarioPorEmail($dados['email']);
            if ($this->requisicaoJson()) {
                $this->responderJson('sucesso', 'Alterações concluídas com sucesso.', URL_BASE . '/projetos');
            }
            $_SESSION['flash_sucesso'] = 'Perfil atualizado com sucesso.';
            $this->redirect(URL_BASE . '/perfil');
        }

        $dados['usuario'] = $this->service->getUsuarioById($usuarioLogado->getIdUsuario());
        $dados['erro'] = $this->service->getMensagemErro();
        if ($this->requisicaoJson()) {
            $this->responderJson('erro', $dados['erro']);
        }
        $this->view('usuarios/perfil', $dados);
    }

    public function salvar(): void {
        $this->adminRequired();

        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'senha' => $_POST['senha'] ?? '',
            'confirmacao' => $_POST['confirmacao'] ?? '',
            'tipo_perfil' => $_POST['tipo_perfil'] ?? '',
        ];

        if ($this->service->createUsuario($dados)) {
            if ($this->requisicaoJson()) {
                $this->responderJson('sucesso', 'Usuário cadastrado com sucesso.', URL_BASE . '/usuarios');
            }
            $this->redirect(URL_BASE . '/usuarios');
        } else {
            $dados['erro'] = $this->service->getMensagemErro();
            if ($this->requisicaoJson()) {
                $this->responderJson('erro', $dados['erro']);
            }
            $this->view('usuarios/usuario_create', $dados);
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
            'confirmacao' => $_POST['confirmacao'] ?? '',
            'tipo_perfil' => $_POST['tipo_perfil'] ?? '',
        ];

        if ($this->service->updateUsuario($id, $dados)) {
            if ($this->requisicaoJson()) {
                $this->responderJson('sucesso', 'Usuário atualizado com sucesso.', URL_BASE . '/usuarios');
            }
            $this->redirect(URL_BASE . '/usuarios');
        } else {
            $dados['erro'] = $this->service->getMensagemErro();
            if ($this->requisicaoJson()) {
                $this->responderJson('erro', $dados['erro']);
            }
            $dados['usuario'] = $this->service->getUsuarioById($id);
            $this->view('usuarios/usuario_edit', $dados);
        }
    }
}
