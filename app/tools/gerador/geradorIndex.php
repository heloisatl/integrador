<?php
namespace app\tools\gerador;

use app\models\Atributo;
use app\models\Tabela;

class geradorIndex{
    
    //Gera o public/index.php (Front Controller).
    
    public static function gerarIndex(array $tabelas, bool $comControllers = true, bool $comConfig = true, bool $comComentarios = false): string {
        $arquivo = "<?php\n\n";

        if ($comComentarios) {
            $arquivo .= <<<DOC
/**
 * Ponto de Entrada Único (Front Controller) - Arquitetura MVC
 *
 * No padrão Front Controller, TODAS as requisições HTTP da aplicação passam por este arquivo (index.php):
 * 
 * 1. INICIALIZAÇÃO E AMBIENTE:
 *    - Configura diretivas de tratamento e exibição de erros (error_reporting / display_errors);
 *    - Carrega as definições globais de ambiente e credenciais do banco (config.php).
 *
 * 2. AUTOLOADING (Compatível com PSR-4):
 *    - O Autoload.php registra o carregamento automático via spl_autoload_register,
 *      instanciando Controllers, Models e Repositories sob demanda sem requires manuais.
 *
 * 3. ROTEAMENTO (Router):
 *    - Mapeia URLs e verbos HTTP (GET / POST) para o Controller e Método correspondentes;
 *    - Ao final, \$router->run() inspeciona a URL atual e despacha a execução da ação solicitada.
 */


DOC;
        }

        $arquivo .= "use app\\core\\Router;\n\n";
        $arquivo .= "error_reporting(E_ALL);\n";
        $arquivo .= "ini_set('display_errors', 1);\n\n";
        $arquivo .= "require_once __DIR__ . '/../app/core/Autoload.php';\n";
        if ($comConfig) {
            $arquivo .= "require_once __DIR__ . '/../app/config/config.php';\n";
        }
        $arquivo .= "\n\$router = new Router();\n\n";

        if (!$comControllers || empty($tabelas)) {
            $arquivo .= "// A camada Controller não foi gerada: nenhuma rota foi registrada.\n";
        } else {
            if ($comComentarios) {
                $arquivo .= "// Mapeamento das Rotas RESTful da Aplicação:\n";
            }
            $primeira = true;
            foreach($tabelas as $tabela){
                $nomeClasse = ucfirst($tabela->getNome_tabelaUC());
                $rota = '/' . strtolower($tabela->getNome_tabela()) . 's';
                $controller = $nomeClasse . 'Controller';

                if ($primeira) {
                    $arquivo .= "\$router->get('/', '{$controller}@index');\n";
                    $primeira = false;
                }
                $arquivo .= "\$router->get('{$rota}', '{$controller}@index');\n";
                $arquivo .= "\$router->get('{$rota}/cadastrar', '{$controller}@cadastrar');\n";
                $arquivo .= "\$router->post('{$rota}/salvar', '{$controller}@salvar');\n";
                $arquivo .= "\$router->get('{$rota}/editar', '{$controller}@editar');\n";
                $arquivo .= "\$router->post('{$rota}/atualizar', '{$controller}@atualizar');\n";
                $arquivo .= "\$router->get('{$rota}/excluir', '{$controller}@excluir');\n\n";
            }
        }

        if ($comComentarios) {
            $arquivo .= "// Despacha a requisição de acordo com o método HTTP e URI solicitada\n";
        }
        $arquivo .= "\$router->run();\n";
        return $arquivo;
    }


    public function salvarIndex(array $tabelas, string $caminho = __DIR__ . '/../../../public', bool $comControllers = true, bool $comConfig = true, bool $comComentarios = false): void {
        if (!is_dir($caminho)) {
            mkdir($caminho, 0777, true);
        }
        $conteudo = self::gerarIndex($tabelas, $comControllers, $comConfig, $comComentarios);
        file_put_contents($caminho . '/index.php', $conteudo);
    }
}