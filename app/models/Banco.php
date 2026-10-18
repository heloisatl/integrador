<?php

namespace app\models;

class Banco {
    private ?int $id_banco;
    private int $fk_usuario;
    private string $nome_banco;
    private string $usuario_banco;
    private ?string $senha_banco;
    private string $host;
    private string $porta;

    public function __construct(
        ?int $id_banco,
        int $fk_usuario,
        string $nome_banco,
        string $usuario_banco,
        ?string $senha_banco = null,
        string $host = 'localhost',
        string $porta = '3306'
    ) {
        $this->id_banco = $id_banco;
        $this->fk_usuario = $fk_usuario;
        $this->nome_banco = $nome_banco;
        $this->usuario_banco = $usuario_banco;
        $this->senha_banco = $senha_banco;
        $this->host = $host;
        $this->porta = $porta;
    }

    public function getIdBanco(): ?int {
        return $this->id_banco;
    }

    public function getFkUsuario(): int {
        return $this->fk_usuario;
    }

    public function getNomeBanco(): string {
        return $this->nome_banco;
    }

    public function getUsuarioBanco(): string {
        return $this->usuario_banco;
    }

    public function getSenhaBanco(): ?string {
        return $this->senha_banco;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getPorta(): string {
        return $this->porta;
    }

    public function setNomeBanco(string $nome_banco): void {
        $this->nome_banco = $nome_banco;
    }

    public function setUsuarioBanco(string $usuario_banco): void {
        $this->usuario_banco = $usuario_banco;
    }

    public function setSenhaBanco(?string $senha_banco): void {
        $this->senha_banco = $senha_banco;
    }

    public function setHost(string $host): void {
        $this->host = $host;
    }

    public function setPorta(string $porta): void {
        $this->porta = $porta;
    }

    public static function arrayParaObjeto(array $dados): self {
        return new self(
            isset($dados['id_banco']) ? (int) $dados['id_banco'] : null,
            (int) ($dados['fk_usuario'] ?? 0),
            (string) ($dados['nome_banco'] ?? ''),
            (string) ($dados['usuario_banco'] ?? ''),
            isset($dados['senha_banco']) && $dados['senha_banco'] !== '' ? (string) $dados['senha_banco'] : null,
            (string) ($dados['host'] ?? 'localhost'),
            (string) ($dados['porta'] ?? '3306')
        );
    }
}
