<?php
namespace app\tools\gerador;

use app\models\Atributo;
use app\models\Tabela;

class geradorIndex{
    public static function gerarIndex(array $tabelas){
        $arquivo = <<<PHP
        <?php
        <?php

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        require_once __DIR__ . '/../app/core/Autoload.php';
        require_once __DIR__ . '/../app/config/Config.php';

        use app\core\Router;

        \$router = new Router();\n

PHP;
        foreach($tabelas as $tabela){
            $nomeClasse = $tabela->getNome_tabelaUC();
            $arquivo .= "\$router->get('/" . strtolower($tabela->getNome_tabela()) . "', '" . $nomeClasse . "Controller@index');\n";
            $arquivo .= "\$router->get('/" . strtolower($tabela->getNome_tabela()) . "/cadastrar', '" . $nomeClasse . "Controller@cadastrar');\n";
            $arquivo .= "\$router->post('/" . strtolower($tabela->getNome_tabela()) . "/salvar', '" . $nomeClasse . "Controller@salvar');\n";
            $arquivo .= "\$router->get('/" . strtolower($tabela->getNome_tabela()) . "/editar/', '" . $nomeClasse . "Controller@editar');\n";
            $arquivo .= "\$router->post('/" . strtolower($tabela->getNome_tabela()) . "/atualizar/', '" . $nomeClasse . "Controller@atualizar');\n";
            $arquivo .= "\$router->get('/" . strtolower($tabela->getNome_tabela()) . "/excluir/', '" . $nomeClasse . "Controller@excluir');\n";
            $arquivo .= "\$router->run();\n";
        }
        return $arquivo;
    }


    public function salvarIndex(array $tabelas, string $caminho = __DIR__ . '/../../../public'): void {

        $conteudo = self::gerarIndex($tabelas);
        file_put_contents($caminho . '/index.php', $conteudo);
    }
}