<?php

namespace app\helpers;

class Validador {
    private array $erros = [];

    public function obrigatorio(string $campo, mixed $valor, ?string $mensagem = null): static {
        if (empty($valor) && $valor !== '0') {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} é obrigatório";
        }

        return $this;
    }

    public function temErros(): bool {
        return !empty($this->erros);
    }

    public function getErros(): array {
        return $this->erros;
    }
}
