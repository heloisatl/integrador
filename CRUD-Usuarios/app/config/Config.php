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
define('URL_BASE', 'http://localhost');

define('URL_BASE_CSS', URL_BASE . '/assets/css');

define('UPLOAD_PATH', __DIR__ . '/../../public/assets/uploads');

// Caminho absoluto para as views da Helo (usadas nas páginas projeto/*)
define('VIEWS_HELO_PATH', __DIR__ . '/../views');

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'mvc_creator');

define('DB_USER', 'root');
define('DB_PASS', '');
