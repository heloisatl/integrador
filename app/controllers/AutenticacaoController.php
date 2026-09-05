<?php

namespace app\controllers;

use app\core\Controller;
use app\services\AutenticacaoService;
use app\services\UsuarioService;

class AutenticacaoController extends Controller
{
    private AutenticacaoService $autenticacaoService;
    private UsuarioService $usuarioService;

    public function __construct()
    {
        $this->autenticacaoService = new AutenticacaoService();
        $this->usuarioService = new UsuarioService();
    }

    public function login(): void
    {
        $dados = [];

        if (!empty($_SESSION['flash_sucesso'])) {
            $dados['sucesso'] = $_SESSION['flash_sucesso'];
            unset($_SESSION['flash_sucesso']);
        }

        if (!empty($_SESSION['flash_erro'])) {
            $dados['erro'] = $_SESSION['flash_erro'];
            unset($_SESSION['flash_erro']);
        }

        $this->view('autenticacao/login', $dados);
    }

    public function logar(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        $resultado = $this->autenticacaoService->logar($email, $senha);

        if ($resultado) {
            $perfil = strtolower($_SESSION['usuario_logado']->getTipoPerfil());
            $destino = $perfil === 'admin' ? '/usuarios' : '/projetos';
            $this->redirect(URL_BASE . $destino);
        }

        $_SESSION['flash_erro'] = 'E-mail ou senha inválidos.';
        $this->redirect(URL_BASE . '/login');
    }

    public function logout(): void
    {
        $this->autenticacaoService->logout();
        $_SESSION['flash_sucesso'] = 'Você saiu do sistema.';
        $this->redirect(URL_BASE . '/login');
    }

    public function recuperarSenha(): void
    {
        $dados = [];

        if (!empty($_SESSION['flash_sucesso'])) {
            $dados['sucesso'] = $_SESSION['flash_sucesso'];
            unset($_SESSION['flash_sucesso']);
        }

        if (!empty($_SESSION['flash_erro'])) {
            $dados['erro'] = $_SESSION['flash_erro'];
            unset($_SESSION['flash_erro']);
        }

        $this->view('autenticacao/recuperar_senha', $dados);
    }

    public function solicitarRecuperacao(): void
    {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_erro'] = 'Informe um e-mail válido.';
            $this->redirect(URL_BASE . '/recuperar-senha');
        }

        // Trava de segurança (Rate Limiting): Bloqueia solicitações repetidas do mesmo e-mail em menos de 60s
        $tempoCooldown = 60;
        if (isset($_SESSION['last_reset_request'][$email])) {
            $tempoDecorrido = time() - $_SESSION['last_reset_request'][$email];
            if ($tempoDecorrido < $tempoCooldown) {
                $restante = $tempoCooldown - $tempoDecorrido;
                $_SESSION['flash_erro'] = "Aguarde {$restante} segundo(s) antes de solicitar uma nova senha.";
                $this->redirect(URL_BASE . '/recuperar-senha');
            }
        }

        // Verifica se o e-mail informado existe no banco de dados do sistema
        $usuario = $this->usuarioService->getUsuarioPorEmail($email);

        if (!$usuario) {
            $_SESSION['flash_erro'] = 'E-mail não encontrado em nosso sistema.';
            $this->redirect(URL_BASE . '/recuperar-senha');
        }

        // Registrar timestamp do envio
        $_SESSION['last_reset_request'][$email] = time();

        // Gerar nova senha temporária (10 caracteres)
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$';
        $novaSenha = substr(str_shuffle(str_repeat($caracteres, 3)), 0, 10);

        // Atualizar senha no banco de dados com hash de segurança
        $atualizado = $this->usuarioService->updateSenhaPorEmail($email, $novaSenha);

        if (!$atualizado) {
            $_SESSION['flash_erro'] = 'Erro ao atualizar a senha no sistema. Tente novamente.';
            $this->redirect(URL_BASE . '/recuperar-senha');
        }

        try {
            require_once __DIR__ . '/../tools/mail/EmailService.php';
            $emailService = new \EmailService();
            $emailService->enviarNovaSenha($email, $novaSenha);

            $_SESSION['flash_sucesso'] = 'Sua nova senha foi enviada para o e-mail informado. Faça login com a nova senha.';
            $this->redirect(URL_BASE . '/login');
        } catch (\Exception $e) {
            $_SESSION['flash_erro'] = 'Senha alterada no sistema, mas houve uma falha ao enviar o e-mail: ' . $e->getMessage();
            $this->redirect(URL_BASE . '/recuperar-senha');
        }
    }

    public function redefinirSenhaForm(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));

        if ($token === '' || empty($_SESSION['password_reset_tokens'][$token]) || $_SESSION['password_reset_tokens'][$token]['expires'] < time()) {
            $_SESSION['flash_erro'] = 'Token inválido ou expirado. Solicite recuperação novamente.';
            $this->redirect(URL_BASE . '/recuperar-senha');
        }

        $dados = ['token' => $token];

        if (!empty($_SESSION['flash_erro'])) {
            $dados['erro'] = $_SESSION['flash_erro'];
            unset($_SESSION['flash_erro']);
        }

        $this->view('autenticacao/redefinir_senha', $dados);
    }

    public function salvarNovaSenha(): void
    {
        $token = trim((string) ($_POST['token'] ?? ''));
        $senha = trim((string) ($_POST['senha'] ?? ''));
        $confirmacao = trim((string) ($_POST['confirmacao'] ?? ''));

        if ($token === '' || $senha === '' || $confirmacao === '') {
            $_SESSION['flash_erro'] = 'Preencha todos os mvc-campos.';
            $this->redirect(URL_BASE . '/redefinir-senha?token=' . urlencode($token));
        }

        if ($senha !== $confirmacao) {
            $_SESSION['flash_erro'] = 'As senhas não conferem.';
            $this->redirect(URL_BASE . '/redefinir-senha?token=' . urlencode($token));
        }

        if (empty($_SESSION['password_reset_tokens'][$token]) || $_SESSION['password_reset_tokens'][$token]['expires'] < time()) {
            $_SESSION['flash_erro'] = 'Token inválido ou expirado. Solicite novo pedido de recuperação.';
            $this->redirect(URL_BASE . '/recuperar-senha');
        }

        $email = $_SESSION['password_reset_tokens'][$token]['email'];

        if ($this->usuarioService->updateSenhaPorEmail($email, $senha)) {
            unset($_SESSION['password_reset_tokens'][$token]);
            $_SESSION['flash_sucesso'] = 'Senha alterada com sucesso. Faça login com a nova senha.';
            $this->redirect(URL_BASE . '/login');
        }

        $_SESSION['flash_erro'] = 'Não foi possível atualizar a senha. Tente novamente.';
        $this->redirect(URL_BASE . '/redefinir-senha?token=' . urlencode($token));
    }

    public function cadastro(): void
    {
        $dados = [];

        if (!empty($_SESSION['flash_erro'])) {
            $dados['erro'] = $_SESSION['flash_erro'];
            unset($_SESSION['flash_erro']);
        }

        $this->view('autenticacao/cadastro', $dados);
    }

    public function salvarCadastro(): void
    {
        $dados = [
            'nome' => trim((string) ($_POST['nome'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'senha' => (string) ($_POST['senha'] ?? ''),
            'tipo_perfil' => 'usuario',
            'usuario_banco' => '',
            'servidor' => 'localhost',
        ];

        if ($this->usuarioService->createUsuario($dados)) {
            $_SESSION['flash_sucesso'] = 'Cadastro realizado com sucesso. Faça login para entrar.';
            $this->redirect(URL_BASE . '/login');
        }

        $_SESSION['flash_erro'] = 'Não foi possível realizar o cadastro. Verifique se o e-mail já está cadastrado ou se os dados estão corretos.';
        $this->redirect(URL_BASE . '/cadastro');
    }

    public function esqueceuMinhaSenha(): void
    {
        $this->view('autenticacao/esqueceu_senha');
    }

    public function explorar(): void
    {
        $this->redirect(URL_BASE . '/projetos');
    }
}
