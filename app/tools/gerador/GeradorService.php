<?php

namespace app\tools\gerador;

use app\models\Tabela;


class GeradorService {
    public function gerarService(Tabela $tabela): string {
        $nomeClasse = ucfirst($tabela->getNome_tabelaUC());

        return <<<PHP
<?php

namespace app\\services;

use app\\models\\{$nomeClasse};
use app\\repositories\\{$nomeClasse}Repository;

class {$nomeClasse}Service {
    private {$nomeClasse}Repository \$repository;

    public function __construct() {
        \$this->repository = new {$nomeClasse}Repository();
    }

    public function listarTodos(): array {
        return \$this->repository->listarTodos();
    }

    public function buscarPorId(int \$id): ?array {
        return \$this->repository->buscarPorId(\$id);
    }

    public function inserir({$nomeClasse} \$obj): bool {
        return \$this->repository->inserir(\$obj);
    }

    public function alterar({$nomeClasse} \$obj, int \$id): bool {
        return \$this->repository->alterar(\$obj, \$id);
    }

    public function excluir(int \$id): bool {
        return \$this->repository->excluir(\$id);
    }
}

PHP;
    }

    /**
     * Salva o Service em app/services/
     */
    public function salvarService(Tabela $tabela, string $caminhoBase = __DIR__ . '/../../services'): string {
        $nomeClasse = ucfirst($tabela->getNome_tabelaUC());
        $arquivoDestino = "{$caminhoBase}/{$nomeClasse}Service.php";

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($arquivoDestino, $this->gerarService($tabela));
        return $arquivoDestino;
    }
}
