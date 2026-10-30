<?php

namespace app\tools\gerador;

use app\models\Atributo;
use app\models\Tabela;
use InvalidArgumentException;


//Motor de geração do MVC Creator.

class MotorGeracao {
    private GeradorModel $geradorModel;
    private GeradorRepositorio $geradorRepositorio;
    private GeradorService $geradorService;
    private GeradorController $geradorController;
    private GeradorView $geradorView;
    private GeradorDatabase $geradorDatabase;

    public function __construct() {
        $this->geradorModel = new GeradorModel();
        $this->geradorRepositorio = new GeradorRepositorio();
        $this->geradorService = new GeradorService();
        $this->geradorController = new GeradorController();
        $this->geradorView = new GeradorView();
        $this->geradorDatabase = new GeradorDatabase();
    }

    
    public function planejar(array $tabelas, array $atributosPorTabela, OpcoesGeracao $opcoes, array $banco = []): array {
        $plano = [];
        $comComentarios = $opcoes->comentariosAtivos();

        foreach ($tabelas as $tabela) {
            $this->validarNome($tabela);
            $classe = $tabela->getNome_tabelaUC();
            $plural = strtolower($tabela->getNome_tabela()) . 's';
            $atributos = $atributosPorTabela[$tabela->getId_tabela()] ?? [];
            $pk = $this->chavePrimaria($atributos);

            if ($opcoes->ativa('model')) {
                // Models são gerados como entidades limpas (POPO)
                $plano["app/models/{$classe}.php"] = fn() => $this->geradorModel->gerarModel($tabela, $atributos);
            }
            if ($opcoes->ativa('repository')) {
                // DAO / Repositório: recebe comentários didáticos de PDO/SQL Injection se ativo
                $plano["app/repositories/{$classe}Repository.php"] = fn() => $this->geradorRepositorio->gerarRepositorio($tabela, $atributos, $pk, $comComentarios);
            }
            if ($opcoes->ativa('service')) {
                // REGRA DE EXCLUSÃO ESTRITA: Services gerados SEMPRE LIMPOS
                $plano["app/services/{$classe}Service.php"] = fn() => $this->geradorService->gerarService($tabela);
            }
            if ($opcoes->ativa('controller')) {
                // Controller: recebe comentários didáticos de MVC/rotas se ativo
                $usarService = $opcoes->ativa('service');
                $plano["app/controllers/{$classe}Controller.php"] = fn() => $this->geradorController->gerarController($tabela, $atributos, $pk, $usarService, $comComentarios);
            }
            if ($opcoes->ativa('views')) {
                // REGRA DE EXCLUSÃO ESTRITA: Views geradas SEMPRE LIMPAS
                $plano["app/views/{$plural}/index.php"] = fn() => $this->geradorView->gerarIndexView($tabela, $atributos, $pk);
                $plano["app/views/{$plural}/create.php"] = fn() => $this->geradorView->gerarCreateView($tabela, $atributos, $pk);
                $plano["app/views/{$plural}/edit.php"] = fn() => $this->geradorView->gerarEditView($tabela, $atributos, $pk);
            }
        }

        // Infraestrutura que cada camada exige para funcionar
        if ($opcoes->ativa('repository')) {
            // ConnectionFactory: recebe comentários didáticos sobre PDO/Singleton se ativo
            $plano['app/database/ConnectionFactory.php'] = fn() => GeradorRepositorio::gerarConnectionFactory($comComentarios);
        }
        if ($opcoes->ativa('controller')) {
            $plano['app/core/Controller.php'] = fn() => GeradorCore::gerarController();
        }
        if ($opcoes->ativa('index')) {
            $plano['app/core/Autoload.php'] = fn() => GeradorCore::gerarAutoload();
            $plano['app/core/Router.php'] = fn() => GeradorCore::gerarRouter();
            $comControllers = $opcoes->ativa('controller');
            $comConfig = $opcoes->ativa('config');
            // index.php: recebe comentários didáticos sobre Front Controller/Autoloading se ativo
            $plano['public/index.php'] = fn() => geradorIndex::gerarIndex($tabelas, $comControllers, $comConfig, $comComentarios);
        }
        if ($opcoes->ativa('config')) {
            // config.php: recebe comentários didáticos sobre credenciais e ambiente se ativo
            $plano['app/config/config.php'] = fn() => GeradorConfig::gerarConfig(
                (string) ($banco['host'] ?? 'localhost'),
                (string) ($banco['porta'] ?? '3306'),
                (string) ($banco['nome_banco'] ?? ''),
                (string) ($banco['usuario_banco'] ?? 'root'),
                $comComentarios
            );
        }
        if ($opcoes->ativa('database')) {
            $nomeBanco = (string) ($banco['nome_banco'] ?? 'meu_banco');
            $plano['database.sql'] = fn() => $this->geradorDatabase->gerarDatabase($nomeBanco, $tabelas, $atributosPorTabela);
        }

        ksort($plano);
        return $plano;
    }

    
    //Caminhos relativos que serão criados (usado pela pré-visualização)
     
    public function listarCaminhos(array $tabelas, array $atributosPorTabela, OpcoesGeracao $opcoes, array $banco = []): array {
        return array_keys($this->planejar($tabelas, $atributosPorTabela, $opcoes, $banco));
    }

    
    //Grava somente os arquivos planejados dentro de $pastaDestino.
    //Diretórios são criados sob demanda: camada desativada = nenhuma pasta criada.
   
    public function gerar(array $tabelas, array $atributosPorTabela, OpcoesGeracao $opcoes, string $pastaDestino, array $banco = []): array {
        $gravados = [];

        foreach ($this->planejar($tabelas, $atributosPorTabela, $opcoes, $banco) as $caminho => $conteudo) {
            $destino = rtrim($pastaDestino, '/') . '/' . $caminho;
            $dir = dirname($destino);
            if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException("Não foi possível criar a pasta {$dir}.");
            }
            file_put_contents($destino, $conteudo());
            $gravados[] = $caminho;
        }

        return $gravados;
    }


    private function chavePrimaria(array $atributos): string {
        foreach ($atributos as $att) {
            if ($att->getPk()) {
                return $att->getNome_atributo();
            }
        }
        return 'id';
    }


    
    private function validarNome(Tabela $tabela): void {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $tabela->getNome_tabela()) !== 1) {
            throw new InvalidArgumentException(
                "O nome da tabela \"{$tabela->getNome_tabela()}\" não pode ser usado como nome de classe PHP (use apenas letras, números e _)."
            );
        }
    }
}
