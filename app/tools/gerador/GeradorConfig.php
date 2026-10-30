<?php
namespace app\tools\gerador;


//Gera o app/config/config.php do projeto exportado
 
class GeradorConfig{

    public static function gerarConfig(string $host = 'localhost', string $porta = '3306', string $nomeBanco = '', string $usuario = 'root', bool $comComentarios = false): string {
        $docConfig = "";
        if ($comComentarios) {
            $docConfig = <<<'DOC'

/**
 * Configuração Geral e Conexão com o Banco de Dados
 *
 * Este arquivo centraliza as diretivas globais e os parâmetros de conexão do sistema:
 * 
 * 1. AMBIENTE DE DESENVOLVIMENTO (DEV_ENVIRONMENT):
 *    - Exibe erros, avisos e notificações diretamente no navegador para agilizar a depuração.
 *
 * 2. PORTABILIDADE DE ROTAS E ASSETS (BASE_PATH e URL_BASE):
 *    - Calcula a URL base dinamicamente, permitindo que o projeto execute em qualquer pasta,
 *      porta ou servidor web (Apache, Nginx, PHP Built-in Server) sem ajustes manuais.
 *
 * 3. PARÂMETROS DE CONEXÃO DO PDO (MySQL):
 *    - DB_HOST: Endereço do servidor MySQL (ex: 'localhost' ou '127.0.0.1');
 *    - DB_PORT: Porta de rede do serviço MySQL (padrão '3306');
 *    - DB_NAME: Nome do banco de dados relacional selecionado;
 *    - DB_USER: Usuário de autenticação do MySQL (ex: 'root');
 *    - DB_PASS: Senha de acesso ao banco (defina conforme as credenciais do seu ambiente).
 */
DOC;
        }

        $arquivo = <<<'PHP'
<?php
{{DOC_CONFIG}}
define('DEV_ENVIRONMENT', true);

if (DEV_ENVIRONMENT == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptDir = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) : '';
$scriptDir = rtrim($scriptDir, '/');
if ($scriptDir === '/' || $scriptDir === '.') {
    $scriptDir = '';
}

define('BASE_PATH', $scriptDir);

$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

define('URL_BASE', $protocolo . '://' . $host . BASE_PATH);

define('DB_HOST', {{DB_HOST}});
define('DB_PORT', {{DB_PORT}});
define('DB_NAME', {{DB_NAME}});
define('DB_USER', {{DB_USER}});
define('DB_PASS', '');

PHP;

        return strtr($arquivo, [
            '{{DOC_CONFIG}}' => $docConfig ? $docConfig . "\n" : "",
            '{{DB_HOST}}' => var_export($host !== '' ? $host : 'localhost', true),
            '{{DB_PORT}}' => var_export($porta !== '' ? $porta : '3306', true),
            '{{DB_NAME}}' => var_export($nomeBanco, true),
            '{{DB_USER}}' => var_export($usuario !== '' ? $usuario : 'root', true),
        ]);
    }

    public function salvarConfig(string $caminhoBase, string $host = 'localhost', string $porta = '3306', string $nomeBanco = '', string $usuario = 'root', bool $comComentarios = false): string {
        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }
        $arquivoDestino = $caminhoBase . '/config.php';
        file_put_contents($arquivoDestino, self::gerarConfig($host, $porta, $nomeBanco, $usuario, $comComentarios));
        return $arquivoDestino;
    }
}