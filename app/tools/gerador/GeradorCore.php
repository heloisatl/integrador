<?php
namespace app\tools\gerador;



class GeradorCore{


    public static function gerarController(){
        $arquivo = <<<PHP
<?php

namespace app\core;

class Controller {
    public function view(string \$view, ?array \$data = null): void {
        if (\$data) {
            extract(\$data);
        }

        \$path = __DIR__ . "/../views/\$view.php";

        if (file_exists(\$path)) {
            require_once \$path;
        } else {
            print 'A view solicitada não foi encontrada: ' . \$view;
        }
    }

    public function redirect(string \$url): void {
        header('location: ' . \$url);
        exit();
    }
};
PHP;
    
        return $arquivo;
    }
    

    public static function gerarAutoload(){
        
        $arquivo = <<<PHP
<?php

spl_autoload_register(function (\$class) {

    \$prefix  = 'app\\';
    \$baseDir = __DIR__ . '/../';

    if (str_starts_with(\$class, \$prefix)) {

        \$path = substr(\$class, strlen(\$prefix));

        \$classFile = \$baseDir . str_replace('\\', '/', \$path) . '.php';

        if (file_exists(\$classFile)) {
            require_once \$classFile;
        }
    } else {
        throw new Exception("A classe {\$class} não pode ser carregada");
    }
});

PHP;
        return $arquivo;
    }

    public static function gerarRouter(){
        $arquivo = <<<PHP
<?php

namespace app\core;

use Exception;

class Router {
    private array \$routes = [];

    public function get(string \$route, string \$action): void {
        \$this->routes[] = [
            'method' => 'GET',
            'route'  => \$route,
            'action' => \$action,
        ];
    }

    public function post(string \$route, string \$action): void {
        \$this->routes[] = [
            'method' => 'POST',
            'route'  => \$route,
            'action' => \$action,
        ];
    }

    public function run(): void {
        \$uri    = parse_url(\$_SERVER['REQUEST_URI'], PHP_URL_PATH);
        \$method = strtoupper(\$_SERVER['REQUEST_METHOD']);

        // Remove o path base da URI (calculado dinamicamente em Config.php,
        // funciona em qualquer pasta/porta/host, não só em uma máquina fixa)
        \$basePath = defined('BASE_PATH') ? BASE_PATH : '';
        if (\$basePath !== '' && strpos(\$uri, \$basePath) === 0) {
            \$uri = substr(\$uri, strlen(\$basePath));
        }
        if (empty(\$uri)) {
            \$uri = '/';
        }

        foreach (\$this->routes as \$route) {

            if (\$route['route'] == \$uri && \$route['method'] == \$method) {
                \$this->dispatch(\$route);
                return;
            }
        }

        http_response_code(404);
        exit('Rota não encontrada');
    }

    private function dispatch(array \$route): void {
        list(\$controller, \$method) = explode('@', \$route['action']);

        \$controllerClass = "app\\controllers\\\$controller";

        if (!class_exists(\$controllerClass)) {
            print "Controller \$controller não encontrado";
            die;
        }

        if (!method_exists(\$controllerClass, \$method)) {
            print "Método \$method não encontrado em \$controllerClass";
            die;
        }

        \$controller = new \$controllerClass;
        \$controller->\$method();
    }

    public function getAllRoutes(): array {
        return \$this->routes;
    }
}

PHP;
    
        return $arquivo;
    }


    public function salvarCore(string $caminhoBase = __DIR__ . '/../../core'): void {
        $controllerContent = self::gerarController();
        $autoloadContent = self::gerarAutoload();
        $routerContent = self::gerarRouter();

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($caminhoBase . '/Controller.php', $controllerContent);
        file_put_contents($caminhoBase . '/Autoload.php', $autoloadContent);
        file_put_contents($caminhoBase . '/Router.php', $routerContent);

    }
}