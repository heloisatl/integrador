<?php

namespace app\services;

use app\models\Usuario;
use app\repositories\UsuarioRepository;

class UsuarioService {
    private UsuarioRepository $repository;
    private string $mensagemErro = '';

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

    public function getMensagemErro(): string {
        return $this->mensagemErro;
    }

    public function updateSenhaPorEmail(string $email, string $senha): bool {
        $this->mensagemErro = '';
        if (strlen($senha) < 8) {
            $this->mensagemErro = 'A senha deve possuir no mínimo 8 caracteres.';
            return false;
        }

        $usuario = $this->repository->getUsuarioByEmail($email);
        if (!$usuario) {
            $this->mensagemErro = 'Usuário não encontrado.';
            return false;
        }

        if (password_verify($senha, $usuario->getSenhaUsuario())) {
            $this->mensagemErro = 'A nova senha não pode ser igual à senha atual.';
            return false;
        }

        return $this->repository->updateSenhaPorEmail($email, $senha);
    }

    public function deleteUsuario(int $id): bool {
        $usuario = $this->repository->getUsuarioById($id);

        if (!$usuario) {
            return false;
        }

        return $this->repository->deleteUsuario($id);
    }

    public function createUsuario(array $dados): bool {
        $this->mensagemErro = '';
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $senha = (string) ($dados['senha'] ?? '');
        $confirmacao = (string) ($dados['confirmacao'] ?? '');
        $tipoPerfil = trim((string) ($dados['tipo_perfil'] ?? ''));

        if ($nome === '' || $email === '' || $senha === '' || $confirmacao === '' || $tipoPerfil === '') {
            $this->mensagemErro = 'Preencha todos os campos obrigatórios.';
            return false;
        }

        if ($this->quantidadeCaracteres($nome) < 3) {
            $this->mensagemErro = 'O nome deve possuir no mínimo 3 caracteres.';
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mensagemErro = 'Informe um e-mail válido.';
            return false;
        }

        if (strlen($senha) < 8) {
            $this->mensagemErro = 'A senha deve possuir no mínimo 8 caracteres.';
            return false;
        }

        if ($senha !== $confirmacao) {
            $this->mensagemErro = 'As senhas não conferem.';
            return false;
        }

        if ($this->repository->emailExiste($email)) {
            $this->mensagemErro = 'Já existe um usuário com este e-mail.';
            return false;
        }

        $dados['nome'] = $nome;
        $dados['email'] = $email;
        $dados['tipo_perfil'] = $tipoPerfil;

        return $this->repository->createUsuario($dados);
    }

    public function updateUsuario(int $id, array $dados): bool {
        $this->mensagemErro = '';
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $senha = (string) ($dados['senha'] ?? '');
        $confirmacao = (string) ($dados['confirmacao'] ?? '');
        $tipoPerfil = trim((string) ($dados['tipo_perfil'] ?? ''));
        $usuario = $this->repository->getUsuarioByIdComSenha($id);

        if (!$usuario) {
            $this->mensagemErro = 'Usuário não encontrado.';
            return false;
        }

        if ($nome === '' || $email === '' || $tipoPerfil === '') {
            $this->mensagemErro = 'Preencha todos os campos obrigatórios.';
            return false;
        }

        if ($this->quantidadeCaracteres($nome) < 3) {
            $this->mensagemErro = 'O nome deve possuir no mínimo 3 caracteres.';
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mensagemErro = 'Informe um e-mail válido.';
            return false;
        }

        if ($this->repository->emailExiste($email, $id)) {
            $this->mensagemErro = 'Já existe outro usuário com este e-mail.';
            return false;
        }

        if ($senha !== '') {
            if (strlen($senha) < 8) {
                $this->mensagemErro = 'A senha deve possuir no mínimo 8 caracteres.';
                return false;
            }

            if ($senha !== $confirmacao) {
                $this->mensagemErro = 'As senhas não conferem.';
                return false;
            }

            if (password_verify($senha, $usuario['senha_usuario'])) {
                $this->mensagemErro = 'A nova senha não pode ser igual à senha atual.';
                return false;
            }
        }

        $dados['nome'] = $nome;
        $dados['email'] = $email;
        $dados['tipo_perfil'] = $tipoPerfil;

        if ($senha === '') {
            $dados['confirmacao'] = '';
        }

        return $this->repository->updateUsuario($id, $dados);
    }

    public function updatePerfil(int $id, array $dados): bool {
        $this->mensagemErro = '';
        $usuario = $this->repository->getUsuarioByIdComSenha($id);
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $senhaAtual = (string) ($dados['senha_atual'] ?? '');
        $senha = (string) ($dados['senha'] ?? '');
        $confirmacao = (string) ($dados['confirmacao'] ?? '');

        if (!$usuario || $nome === '' || $email === '') {
            $this->mensagemErro = 'Preencha todos os campos obrigatórios.';
            return false;
        }

        if ($this->quantidadeCaracteres($nome) < 3) {
            $this->mensagemErro = 'O nome deve possuir no mínimo 3 caracteres.';
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mensagemErro = 'Informe um e-mail válido.';
            return false;
        }

        if ($this->repository->emailExiste($email, $id)) {
            $this->mensagemErro = 'Já existe outro usuário com este e-mail.';
            return false;
        }

        if ($senha !== '') {
            if (!password_verify($senhaAtual, $usuario['senha_usuario'])) {
                $this->mensagemErro = 'A senha atual está incorreta.';
                return false;
            }

            if (strlen($senha) < 8) {
                $this->mensagemErro = 'A senha deve possuir no mínimo 8 caracteres.';
                return false;
            }

            if ($senha !== $confirmacao) {
                $this->mensagemErro = 'As senhas não conferem.';
                return false;
            }

            if (password_verify($senha, $usuario['senha_usuario'])) {
                $this->mensagemErro = 'A nova senha não pode ser igual à senha atual.';
                return false;
            }
        }

        return $this->repository->updateUsuario($id, [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'tipo_perfil' => $usuario['tipo_perfil'],
        ]);
    }

    private function quantidadeCaracteres(string $valor): int {
        return function_exists('mb_strlen') ? mb_strlen($valor) : strlen($valor);
    }
}
