<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/Config.php';

use app\core\Router;

$router = new Router();

$router->get('/', 'UsuarioController@index');

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



$router->get("/projetos",'ProjetoController@index');
$router->get("/projetos/cadastrar","ProjetoController@cadastrar");
$router->post("/projetos/cadastrar/opcoes","ProjetoController@bools");
$router->post("/projetos/criar","ProjetoController@criar");
$router->post("/projetos/editar","ProjetoController@editar");
$router->post("/projetos/editar/opcoes","ProjetoController@editBools");
$router->post("/projetos/getDatabases","ProjetoController@getDatabases");
$router->post("/projetos/getTabelas","ProjetoController@getTabelas");
$router->post("/projetos/gerarMvc","ProjetoController@gerarMvc");
$router->get("/projetos/downloadZip","ProjetoController@downloadZip");

// Páginas do "menu principal" (topbar/sidebar) que já tinham view pronta
// mas nunca tiveram rota registrada -> por isso davam 404 / não abriam.
$router->get("/projetos/config-global", "ProjetoController@configGlobal");
$router->get("/projetos/guia", "ProjetoController@guia");
$router->get("/projetos/mvc-creator", "ProjetoController@mvcCreator");
$router->get("/projetos/pagemaker", "ProjetoController@pageMaker");
$router->get("/projetos/historico", "ProjetoController@historico");
$router->get("/projetos/saida", "ProjetoController@saida");

$router->run();