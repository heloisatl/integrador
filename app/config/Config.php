<?php

// Configuração do ambiente
define('DEV_ENVIRONMENT', true);

if (DEV_ENVIRONMENT == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração do sistema
define('APP_NAME', 'DevStudio');

// ---------------------------------------------------------------------
// BASE_PATH / URL_BASE calculados dinamicamente
// ---------------------------------------------------------------------
// Antes esses valores eram fixos (ex: 'http://localhost:8081' e, no
// Router, '/integrador/CRUD-Merged/public'), o que só funcionava na
// máquina/porta de quem escreveu o código. Em qualquer outra pasta,
// porta ou host as rotas simplesmente não batiam e tudo dava
// "Rota não encontrada".
//
// Agora descobrimos isso automaticamente a partir da própria requisição:
// - BASE_PATH = a pasta onde o index.php está (ex: /integrador-main/public,
//   ou vazio se o site estiver na raiz do domínio).
// - URL_BASE  = protocolo + host + BASE_PATH, montado na hora.
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

// Caminho absoluto para as views da Helo (usadas nas páginas projeto/*)
define('VIEWS_HELO_PATH', __DIR__ . '/../views');

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'mvc_creator');

define('DB_USER', 'root');
define('DB_PASS', 'bancodedados');