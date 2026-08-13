<?php

namespace app\tools\gerador;

class GeradorController {
    /**
     * Gera a classe Controller extendendo app\core\Controller e namespace app\controllers.
     *
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @return string
     */
    public function gerarController(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): string {
        $nomeClasse = ucfirst($nomeTabela);
        $nomeController = "{$nomeClasse}Controller";
        $nomeRepositorio = "{$nomeClasse}Repository";
        $varSingular = strtolower($nomeTabela);
        $varPlural = $varSingular . 's';

        $camposSetters = "";
        foreach ($atributos as $campo) {
            if ($campo === $chavePrimaria) continue;
            $metodo = ucfirst($campo);
            $camposSetters .= "        \${$varSingular}->set{$metodo}(\$_POST['{$campo}'] ?? '');\n";
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
            \$data['erro'] = 'Erro ao cadastrar {$nomeTabela}.';
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
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @param string $caminhoBase
     * @return string
     */
    public function salvarController(string $nomeTabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../controllers'): string {
        $nomeClasse = ucfirst($nomeTabela);
        $conteudo = $this->gerarController($nomeTabela, $atributos, $chavePrimaria);
        $arquivoDestino = "{$caminhoBase}/{$nomeClasse}Controller.php";

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($arquivoDestino, $conteudo);
        return $arquivoDestino;
    }
}
