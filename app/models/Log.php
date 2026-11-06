<?php

namespace app\models;

class Log {
    private int     $idLog;
    private int     $fkUsuario;
    private string  $acao;
    private string  $data;
    private ?string $nomeUsuario;
    private ?string $emailUsuario;
    private ?string $tipoPerfil;

    public function __construct(
        int     $idLog,
        int     $fkUsuario,
        string  $acao,
        string  $data,
        ?string $nomeUsuario = null,
        ?string $emailUsuario = null,
        ?string $tipoPerfil = null
    ) {
        $this->idLog        = $idLog;
        $this->fkUsuario    = $fkUsuario;
        $this->acao         = $acao;
        $this->data         = $data;
        $this->nomeUsuario  = $nomeUsuario;
        $this->emailUsuario = $emailUsuario;
        $this->tipoPerfil   = $tipoPerfil;
    }

    public function getIdLog(): int {
        return $this->idLog;
    }

    public function getFkUsuario(): int {
        return $this->fkUsuario;
    }

    public function getAcao(): string {
        return $this->acao;
    }

    public function getData(): string {
        return $this->data;
    }

    public function getDataFormatada(): string {
        $timestamp = strtotime($this->data);
        return $timestamp ? date('d/m/Y H:i:s', $timestamp) : $this->data;
    }

    public function getNomeUsuario(): string {
        return $this->nomeUsuario ?? 'Usuário #' . $this->fkUsuario;
    }

    public function getEmailUsuario(): string {
        return $this->emailUsuario ?? '-';
    }

    public function getTipoPerfil(): string {
        return $this->tipoPerfil ?? 'usuario';
    }

    public function ehAdmin(): bool {
        return in_array(strtolower(trim($this->getTipoPerfil())), ['admin', 'administrador'], true);
    }

    public function toArray(): array {
        return [
            'id_log'        => $this->idLog,
            'fk_usuario'    => $this->fkUsuario,
            'acao'          => $this->acao,
            'data'          => $this->data,
            'data_formatada'=> $this->getDataFormatada(),
            'nome_usuario'  => $this->getNomeUsuario(),
            'email_usuario' => $this->getEmailUsuario(),
            'tipo_perfil'   => $this->getTipoPerfil(),
            'eh_admin'      => $this->ehAdmin()
        ];
    }
}
