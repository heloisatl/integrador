<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/Config.php';

use app\core\Router;

// Servir arquivos estáticos solicitados na raiz (como /favicon.svg, /favicon.ico)
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($uriPath !== '/' && file_exists(__DIR__ . $uriPath) && is_file(__DIR__ . $uriPath)) {
    $ext = strtolower(pathinfo($uriPath, PATHINFO_EXTENSION));
    $mimes = [
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'png' => 'image/png',
        'css' => 'text/css',
        'js'  => 'text/javascript'
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        readfile(__DIR__ . $uriPath);
        exit;
    }
}

$router = new Router();

$router->get("/projetos",'ProjetoController@index');

$router->get('/', 'ProjetoController@index');
$router->get('/login', 'AutenticacaoController@login');
$router->post('/logar', 'AutenticacaoController@logar');
$router->get('/logout', 'AutenticacaoController@logout');
$router->get('/cadastro', 'AutenticacaoController@cadastro');
$router->post('/cadastro/salvar', 'AutenticacaoController@salvarCadastro');
$router->get('/recuperar-senha', 'AutenticacaoController@recuperarSenha');
$router->post('/recuperar-senha', 'AutenticacaoController@solicitarRecuperacao');
$router->get('/redefinir-senha', 'AutenticacaoController@redefinirSenhaForm');
$router->post('/redefinir-senha', 'AutenticacaoController@salvarNovaSenha');
$router->get('/explorar', 'AutenticacaoController@explorar');

$router->get('/usuarios', 'UsuarioController@index');
$router->get('/usuarios/excluir', 'UsuarioController@excluir');

$router->get('/usuarios/cadastrar', 'UsuarioController@cadastrar');
$router->post('/usuarios/salvar', 'UsuarioController@salvar');
$router->get('/usuarios/editar', 'UsuarioController@editar');
$router->post('/usuarios/atualizar', 'UsuarioController@atualizar');
$router->get('/perfil', 'UsuarioController@perfil');
$router->get('/perfil/editar', 'UsuarioController@perfil');



$router->get("/projetos/cadastrar","ProjetoController@cadastrar");
$router->post("/projetos/cadastrar/opcoes","ProjetoController@bools");
$router->post("/projetos/criar","ProjetoController@criar");
$router->post("/projetos/editar","ProjetoController@editar");
$router->post("/projetos/editar/opcoes","ProjetoController@editBools");
$router->post("/projetos/getDatabases","ProjetoController@getDatabases");
$router->post("/projetos/getTabelas","ProjetoController@getTabelas");
$router->post("/projetos/gerarMvc","ProjetoController@gerarMvc");
$router->get("/projetos/downloadZip","ProjetoController@downloadZip");

$router->get("/testeLeitura","ProjetoController@TesteLeituraDeBanco");
$router->get("/testeInserirBanco","ProjetoController@testeInserirBanco");
// Páginas do "menu principal" (topbar/sidebar) que já tinham view pronta
// mas nunca tiveram rota registrada -> por isso davam 404 / não abriam.
$router->get("/projetos/guia", "ProjetoController@guia");
$router->get("/projetos/mvc-creator", "ProjetoController@mvcCreator");
$router->get("/projetos/pagemaker", "ProjetoController@pageMaker");
$router->get("/projetos/historico", "ProjetoController@historico");
$router->get("/projetos/saida", "ProjetoController@saida");
$router->get("/projetos/phpmeuamigo", "PhpMeuAmigoController@index");

// Rotas de API AJAX do módulo PHPMeuAmigo
$router->get("/phpmeuamigo/bancos", "PhpMeuAmigoController@listarBancos");
$router->post("/phpmeuamigo/bancos/salvar", "PhpMeuAmigoController@salvarBanco");
$router->post("/phpmeuamigo/bancos/excluir", "PhpMeuAmigoController@excluirBanco");
$router->get("/phpmeuamigo/tabelas", "PhpMeuAmigoController@listarTabelas");
$router->post("/phpmeuamigo/tabelas/salvar", "PhpMeuAmigoController@salvarTabela");
$router->post("/phpmeuamigo/tabelas/excluir", "PhpMeuAmigoController@excluirTabela");
$router->get("/phpmeuamigo/atributos", "PhpMeuAmigoController@listarAtributos");
$router->post("/phpmeuamigo/atributos/salvar", "PhpMeuAmigoController@salvarAtributo");
$router->post("/phpmeuamigo/atributos/excluir", "PhpMeuAmigoController@excluirAtributo");
$router->post("/phpmeuamigo/importar-sql", "PhpMeuAmigoController@importarSql");
$router->post("/phpmeuamigo/conectar-mysql-local", "PhpMeuAmigoController@conectarMysqlLocal");
$router->post("/phpmeuamigo/importar-banco-local", "PhpMeuAmigoController@importarBancoLocal");

$router->run();