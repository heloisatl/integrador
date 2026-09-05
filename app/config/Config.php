<?php

define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__, 2)));

/**
 * Carrega variáveis de ambiente de um arquivo .env se ele existir
 */
function carregaEnv(string $caminho): bool {
    if (!file_exists($caminho)) {
        return false;
    }
    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || strpos($linha, '#') === 0) {
            continue;
        }
        if (strpos($linha, '=') !== false) {
            list($chave, $valor) = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim($valor);
            $valor = trim($valor, '"\'');
            if (!array_key_exists($chave, $_SERVER) && !array_key_exists($chave, $_ENV)) {
                putenv("{$chave}={$valor}");
                $_ENV[$chave] = $valor;
                $_SERVER[$chave] = $valor;
            }
        }
    }
    return true;
}

carregaEnv(ROOT_PATH . '/.env');

function env(string $chave, $padrao = null) {
    $valor = getenv($chave);
    if ($valor === false) {
        $valor = $_ENV[$chave] ?? $_SERVER[$chave] ?? $padrao;
    }
    return $valor;
}


define('DEV_ENVIRONMENT', filter_var(env('DEV_ENVIRONMENT', true), FILTER_VALIDATE_BOOLEAN));

if (DEV_ENVIRONMENT == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração do sistema
define('APP_NAME', env('APP_NAME', 'DevStudio'));

// ---------------------------------------------------------------------
// BASE_PATH / URL_BASE calculados dinamicamente
// ---------------------------------------------------------------------
$scriptDir = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) : '';
$scriptDir = rtrim($scriptDir, '/');
if ($scriptDir === '/' || $scriptDir === '.') {
    $scriptDir = '';
}

define('BASE_PATH', $scriptDir);

$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

define('URL_BASE', $protocolo . '://' . $host . BASE_PATH);

define('URL_BASE_CSS', URL_BASE . '/assets/css');

define('UPLOAD_PATH', __DIR__ . '/../../public/assets/uploads');

// Caminho absoluto para as views
define('VIEWS_HELO_PATH', __DIR__ . '/../views');

// Configurações do banco de dados (lidas do .env com fallback seguro)
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'mvc_creator'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));