<?php

namespace app\tools\gerador;

use app\models\Tabela;


//Filtro das tabelas escolhidas pelo usuário na etapa "Tabelas Detectadas".
 
class FiltroTabelas {
    private array $idsExcluidos = [];
    //$idsExcluidos IDs das tabelas que NÃO devem ser geradas
     
    public function __construct(array $idsExcluidos = []) {
        $ids = array_map('intval', $idsExcluidos);
        $this->idsExcluidos = array_values(array_unique(array_filter($ids, fn(int $id) => $id > 0)));
    }

    
    //Cria o filtro a partir do valor enviado pelo front-end (mvcLoad.js), que serializa
    //o sessionStorage `mvc_tabelasDisabled` como JSON no formato:
    //  [{"id_tabela":"3","checked":false}, ...]
    //Também aceita um array já decodificado e valores vazios/inválidos (nenhuma exclusão).
     
    public static function fromRequest(string|array|null $tabelasDesativadas): self {
        if (is_string($tabelasDesativadas)) {
            $tabelasDesativadas = trim($tabelasDesativadas) === '' ? [] : json_decode($tabelasDesativadas, true);
        }

        if (!is_array($tabelasDesativadas)) {
            return new self();
        }

        $ids = [];
        foreach ($tabelasDesativadas as $item) {
            $ids[] = is_array($item) ? ($item['id_tabela'] ?? 0) : $item;
        }

        return new self($ids);
    }

    
    // Mantém somente as tabelas que não foram desmarcadas, preservando a ordem original.
     
    public function filtrar(array $tabelas): array {
        return array_values(array_filter(
            $tabelas,
            fn(Tabela $tabela) => $this->estaSelecionada($tabela)
        ));
    }

    public function estaSelecionada(Tabela $tabela): bool {
        return !in_array((int) $tabela->getId_tabela(), $this->idsExcluidos, true);
    }

    
    public function getIdsExcluidos(): array {
        return $this->idsExcluidos;
    }
}
