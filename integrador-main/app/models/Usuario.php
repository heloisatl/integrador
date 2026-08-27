<?php

namespace app\models;

class Usuario {
    private int    $idUsuario;
    private string $nome;
    private string $email;
    private string $senhaUsuario;
    private string $tipoPerfil;

    public function __construct(
        int    $idUsuario,
        string $nome,
        string $email,
        string $senhaUsuario,
        string $tipoPerfil
    ) {
        $this->idUsuario    = $idUsuario;
        $this->nome         = $nome;
        $this->email        = $email;
        $this->senhaUsuario = $senhaUsuario;
        $this->tipoPerfil   = $tipoPerfil;
    }

    public function getIdUsuario(): int {
        return $this->idUsuario;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getSenhaUsuario(): string {
        return $this->senhaUsuario;
    }

    

    public function getTipoPerfil(): string {
        return $this->tipoPerfil;
    }

    public static function arrayParaObjeto(array $dados): self {
        return new self(
            (int) $dados['id_usuario'],
            $dados['nome'],
            $dados['email'],
            $dados['senha_usuario'],
            $dados['tipo_perfil']
        );
    }
}
