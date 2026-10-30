<?php

namespace app\tools\gerador;
use app\models\Tabela;
use app\models\Atributo;

class GeradorController {
    
    public function gerarController(Tabela $tabela, array $atributos, string $chavePrimaria = 'id', bool $usarService = false, bool $comComentarios = false): string {
        $nomeClasse = ucfirst($tabela->getNome_tabelaUC());
        $nomeController = "{$nomeClasse}Controller";
        $nomeRepositorio = $usarService ? "{$nomeClasse}Service" : "{$nomeClasse}Repository";
        $namespaceDependencia = $usarService ? 'services' : 'repositories';
        $varSingular = strtolower($tabela->getNome_tabela());
        $varPlural = $varSingular . 's';

        $camposSetters = "";
        foreach ($atributos as $campo) {
            if($campo->getPk()) continue;
            $metodo = ucfirst($campo->getNome_atributo());
            $camposSetters .= "        \${$varSingular}->set{$metodo}(\$_POST['{$campo->getNome_atributo()}'] ?? '');\n";
        }

        // Blocos de comentários didáticos (ativos apenas quando solicitado)
        $docClasse = "";
        $docConstruct = "";
        $docIndex = "";
        $docCadastrar = "";
        $docSalvar = "";
        $docEditar = "";
        $docAtualizar = "";
        $docExcluir = "";

        if ($comComentarios) {
            $docClasse = <<<DOC
/**
 * Camada de Controle (Controller) - Arquitetura MVC
 *
 * O Controller atua como o intermediador entre as requisições do usuário e o sistema:
 * 1. Recebe os dados enviados pelas rotas (HTTP GET / POST);
 * 2. Aciona a camada de persistência/negócio ({$nomeRepositorio}) para processar as informações;
 * 3. Prepara os dados de resposta e renderiza a View correspondente ou executa um redirecionamento.
 */
DOC;
            $docConstruct = "    /**\n     * Inicializa a dependência da camada de persistência/dados ({$nomeRepositorio}).\n     */\n";
            $docIndex = "    /**\n     * Listagem (GET): Recupera todos os registros e renderiza a View de listagem ({$varPlural}/index).\n     */\n";
            $docCadastrar = "    /**\n     * Formulário de Cadastro (GET): Exibe a View com o formulário para criação de um novo registro.\n     */\n";
            $docSalvar = "    /**\n     * Processamento de Cadastro (POST): Recebe os dados do formulário (\$_POST),\n     * popula o Model {$nomeClasse} e solicita a inserção no banco de dados.\n     */\n";
            $docEditar = "    /**\n     * Formulário de Edição (GET): Localiza o registro existente pelo ID recebido via URL\n     * e o envia preenchido para a View de edição.\n     */\n";
            $docAtualizar = "    /**\n     * Processamento de Atualização (POST): Captura os dados submetidos pelo formulário,\n     * atualiza as propriedades do Model {$nomeClasse} e executa o update do registro.\n     */\n";
            $docExcluir = "    /**\n     * Exclusão (GET): Remove com segurança o registro indicado pelo ID e retorna à listagem.\n     */\n";
        }

        $blocoDocClasse = $docClasse ? "\n" . $docClasse : "";

        return <<<PHP
<?php

namespace app\\controllers;

use app\\core\\Controller;
use app\\models\\{$nomeClasse};
use app\\{$namespaceDependencia}\\{$nomeRepositorio};
{$blocoDocClasse}
class {$nomeController} extends Controller {
    private {$nomeRepositorio} \$repository;

{$docConstruct}    public function __construct() {
        \$this->repository = new {$nomeRepositorio}();
    }

{$docIndex}    public function index(): void {
        \$data['{$varPlural}'] = \$this->repository->listarTodos();
        \$this->view('{$varPlural}/index', \$data);
    }

{$docCadastrar}    public function cadastrar(): void {
        \$this->view('{$varPlural}/create');
    }

{$docSalvar}    public function salvar(): void {
        \${$varSingular} = new {$nomeClasse}();
{$camposSetters}
        if (\$this->repository->inserir(\${$varSingular})) {
            \$this->redirect(URL_BASE . '/{$varPlural}');
        } else {
            \$data['erro'] = 'Erro ao cadastrar {$tabela->getNome_tabela()}.';
            \$this->view('{$varPlural}/create', \$data);
        }
    }

{$docEditar}    public function editar(): void {
        \$id = (int) (\$_GET['id'] ?? 0);
        \$data['{$varSingular}'] = \$this->repository->buscarPorId(\$id);

        if (!\$data['{$varSingular}']) {
            \$this->redirect(URL_BASE . '/{$varPlural}');
        }

        \$this->view('{$varPlural}/edit', \$data);
    }

{$docAtualizar}    public function atualizar(): void {
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

{$docExcluir}    public function excluir(): void {
        if (isset(\$_GET['id'])) {
            \$id = (int) \$_GET['id'];
            \$this->repository->excluir(\$id);
        }

        \$this->redirect(URL_BASE . '/{$varPlural}');
    }
}
PHP;
    }

    
    //Salva o Controller em app/controllers/
     
    public function salvarController(Tabela $tabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../controllers', bool $usarService = false, bool $comComentarios = false): string {
        $nomeClasse = ucfirst($tabela->getNome_tabelaUC());
        $conteudo = $this->gerarController($tabela, $atributos, $chavePrimaria, $usarService, $comComentarios);
        $arquivoDestino = "{$caminhoBase}/{$nomeClasse}Controller.php";

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($arquivoDestino, $conteudo);
        return $arquivoDestino;
    }
}
