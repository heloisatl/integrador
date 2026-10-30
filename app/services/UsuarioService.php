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

    public function getUsuarioPorEmail(string $email): Usuario|false {
        return $this->repository->getUsuarioByEmail($email);
    }

    public function updateSenhaPorEmail(string $email, string $senha): bool {
        return $this->repository->updateSenhaPorEmail($email, $senha);
    }

    public function atualizarPerfil(int $id, string $emailAtual, array $dados): array {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $senhaAtual = (string) ($dados['senha_atual'] ?? '');
        $senhaNova = (string) ($dados['senha_nova'] ?? '');
        $confirmarSenha = (string) ($dados['confirmar_senha'] ?? '');
        $alterarSenha = $senhaAtual !== '' || $senhaNova !== '' || $confirmarSenha !== '';

        if ($nome === '' || (function_exists('mb_strlen') ? mb_strlen($nome) : strlen($nome)) > 60) {
            return ['sucesso' => false, 'mensagem' => 'Informe um nome válido com até 60 caracteres.'];
        }
        if ($email === '' || (function_exists('mb_strlen') ? mb_strlen($email) : strlen($email)) > 60 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'mensagem' => 'Informe um e-mail válido com até 60 caracteres.'];
        }

        // Bloquear SQL Injection em nome e e-mail
        $padraoSqlInjection = '/(--|\/\*|\*\/|;|[\'"`=]|#|\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|ALTER|CREATE|TRUNCATE|EXEC|BENCHMARK|SLEEP)\b)/i';
        if (preg_match($padraoSqlInjection, $nome) || preg_match($padraoSqlInjection, $email)) {
            return ['sucesso' => false, 'mensagem' => 'Ação bloqueada: termos suspeitos ou caracteres de SQL Injection não são permitidos.'];
        }

        // Bloquear caracteres especiais e números no nome
        if (!preg_match('/^[\p{L}\s]+$/u', $nome)) {
            return ['sucesso' => false, 'mensagem' => 'O nome deve conter apenas letras e espaços, sem caracteres especiais ou números.'];
        }

        if ($this->repository->emailExiste($email, $id)) {
            return ['sucesso' => false, 'mensagem' => 'Este e-mail já está sendo usado por outra conta.'];
        }

        $senhaParaSalvar = null;
        if ($alterarSenha) {
            $usuarioAtual = $this->repository->getUsuarioByEmail($emailAtual);
            if (!$usuarioAtual || !password_verify($senhaAtual, $usuarioAtual->getSenhaUsuario())) {
                return ['sucesso' => false, 'mensagem' => 'A senha atual está incorreta.'];
            }
            if ($senhaNova !== $confirmarSenha) {
                return ['sucesso' => false, 'mensagem' => 'A nova senha e a confirmação não correspondem.'];
            }
            if (strlen($senhaNova) < 10 || !preg_match('/[a-z]/', $senhaNova) || !preg_match('/[A-Z]/', $senhaNova) || !preg_match('/[0-9]/', $senhaNova) || !preg_match('/[^a-zA-Z0-9]/', $senhaNova)) {
                return ['sucesso' => false, 'mensagem' => 'A nova senha deve ter ao menos 10 caracteres, incluindo maiúscula, minúscula, número e símbolo.'];
            }
            $senhaParaSalvar = $senhaNova;
        }

        if (!$this->repository->updatePerfil($id, $nome, $email, $senhaParaSalvar)) {
            return ['sucesso' => false, 'mensagem' => 'Não foi possível salvar as alterações. Tente novamente.'];
        }

        return ['sucesso' => true, 'mensagem' => 'Perfil atualizado com sucesso.'];
    }

    public function deleteUsuario(int $id): bool {
        $usuario = $this->repository->getUsuarioById($id);

        if (!$usuario) {
            return false;
        }

        return $this->repository->deleteUsuario($id);
    }

    public function createUsuario(array $dados): bool {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $senha = (string) ($dados['senha'] ?? '');
        $tipoPerfil = trim((string) ($dados['tipo_perfil'] ?? ''));

        if ($nome === '' || $email === '' || $senha === '' || $tipoPerfil === '') {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($this->repository->emailExiste($email)) {
            return false;
        }

        $dados['nome'] = $nome;
        $dados['email'] = $email;
        $dados['tipo_perfil'] = $tipoPerfil;

        return $this->repository->createUsuario($dados);
    }

    public function updateUsuario(int $id, array $dados): bool {
        if (empty($dados['nome']) || empty($dados['email'])) {
            return false;
        }

        return $this->repository->updateUsuario($id, $dados);
    }
}
