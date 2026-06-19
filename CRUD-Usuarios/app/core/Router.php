<?php

namespace app\core;

use Exception;

class Router {
    private array $routes = [];

    public function get(string $route, string $action): void {
        $this->routes[] = [
            'method' => 'GET',
            'route'  => $route,
            'action' => $action,
        ];
    }

    public function post(string $route, string $action): void {
        $this->routes[] = [
            'method' => 'POST',
            'route'  => $route,
            'action' => $action,
        ];
    }

    public function run(): void {
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        foreach ($this->routes as $route) {

            if ($route['route'] == $uri && $route['method'] == $method) {
                $this->dispatch($route);
                return;
            }
        }

        http_response_code(404);
        exit('Rota não encontrada');
    }

    private function dispatch(array $route): void {
        list($controller, $method) = explode('@', $route['action']);

        $controllerClass = "app\\controllers\\$controller";

        if (!class_exists($controllerClass)) {
            print "Controller $controller não encontrado";
            die;
        }

        if (!method_exists($controllerClass, $method)) {
            print "Método $method não encontrado em $controllerClass";
            die;
        }

        $controller = new $controllerClass;
        $controller->$method();
    }

    public function getAllRoutes(): array {
        return $this->routes;
    }
}
