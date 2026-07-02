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
$router->post("/projeto/editar/opcoes","ProjetoController@editBools");
$router->post("/projetos/getDatabases","ProjetoController@getDatabases");

$router->run();