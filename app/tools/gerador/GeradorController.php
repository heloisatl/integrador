<?php

namespace app\tools\gerador;
use app\models\Tabela;
use app\models\Atributo;

class GeradorController {
    /**
     * Gera a classe Controller extendendo app\core\Controller e namespace app\controllers.
     *
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @return string
     */
    public function gerarController(Tabela $tabela, array $atributos, string $chavePrimaria = 'id'): string {
        $nomeClasse = ucfirst($tabela->getNome_tabelaUC());
        $nomeController = "{$nomeClasse}Controller";
        $nomeRepositorio = "{$nomeClasse}Repository";
        $varSingular = strtolower($tabela->getNome_tabela());
        $varPlural = $varSingular . 's';

        $camposSetters = "";
        foreach ($atributos as $atributo) {
            foreach($atributo as $campo){
                if($campo->getPk()) continue;
                $metodo = ucfirst($campo->getNome_atributo());
                $camposSetters .= "        \${$varSingular}->set{$metodo}(\$_POST['{$campo->getNome_atributo()}'] ?? '');\n";
            }
            
        }

        return <<<PHP
<?php

namespace app\controllers;

use app\core\Controller;
use app\models\\{$nomeClasse};
use app\repositories\\{$nomeRepositorio};

class {$nomeController} extends Controller {
    private {$nomeRepositorio} \$repository;

    public function __construct() {
        \$this->repository = new {$nomeRepositorio}();
    }

    public function index(): void {
        \$data['{$varPlural}'] = \$this->repository->listarTodos();
        \$this->view('{$varPlural}/index', \$data);
    }

    public function cadastrar(): void {
        \$this->view('{$varPlural}/create');
    }

    public function salvar(): void {
        \${$varSingular} = new {$nomeClasse}();
{$camposSetters}
        if (\$this->repository->inserir(\${$varSingular})) {
            \$this->redirect(URL_BASE . '/{$varPlural}');
        } else {
            \$data['erro'] = 'Erro ao cadastrar {$tabela->getNome_tabela()}.';
            \$this->view('{$varPlural}/create', \$data);
        }
    }

    public function editar(): void {
        if (!isset(\$_GET['id'])) {
            \$this->redirect(URL_BASE . '/{$varPlural}');
        }

        \$id = (int) \$_GET['id'];
        \$data['{$varSingular}'] = \$this->repository->buscarPorId(\$id);

        if (!\$data['{$varSingular}']) {
            \$this->redirect(URL_BASE . '/{$varPlural}');
        }

        \$this->view('{$varPlural}/edit', \$data);
    }

    public function atualizar(): void {
        if (!isset(\$_POST['id'])) {
            \$this->redirect(URL_BASE . '/{$varPlural}');
        }

        \$id = (int) \$_POST['id'];
        \${$varSingular} = new {$nomeClasse}();
{$camposSetters}
        if (\$this->repository->alterar(\${$varSingular}, \$id)) {
            \$this->redirect(URL_BASE . '/{$varPlural}');
        } else {
            \$this->redirect(URL_BASE . '/{$varPlural}/editar?id=' . \$id);
        }
    }

    public function excluir(): void {
        if (isset(\$_GET['id'])) {
            \$id = (int) \$_GET['id'];
            \$this->repository->excluir(\$id);
        }

        \$this->redirect(URL_BASE . '/{$varPlural}');
    }
}
PHP;
    }

    /**
     * Salva o Controller em app/controllers/
     *
     * @param Tabela $tabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @param string $caminhoBase
     * @return string
     */
    public function salvarController(Tabela $tabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../controllers'): string {
        $nomeClasse = ucfirst($tabela->getNome_tabelaUC());
        $conteudo = $this->gerarController($tabela, $atributos, $chavePrimaria);
        $arquivoDestino = "{$caminhoBase}/{$nomeClasse}Controller.php";

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($arquivoDestino, $conteudo);
        return $arquivoDestino;
    }
}
