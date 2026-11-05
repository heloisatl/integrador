<?php

namespace app\controllers;

use app\core\Controller;
use app\services\LogService;

class LogController extends Controller {
    private LogService $service;

    public function __construct() {
        $this->service = new LogService();
    }

    /**
     * Tela restrita de acompanhamento cronológico de logs (RF 15 / UC 15)
     * Acesso exclusivo para administradores
     */
    public function index(): void {
        $this->adminRequired();

        $filtros = [
            'busca'       => $_GET['q'] ?? '',
            'usuario_id'  => $_GET['usuario_id'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim'    => $_GET['data_fim'] ?? '',
            'tipo_perfil' => $_GET['tipo_perfil'] ?? ''
        ];

        $dados = [
            'logs'           => $this->service->listarLogs($filtros),
            'totalLogs'      => $this->service->contarLogs($filtros),
            'estatisticas'   => $this->service->obterEstatisticas(),
            'usuariosFiltro' => $this->service->obterUsuariosFiltro(),
            'filtros'        => $filtros
        ];

        $this->view('logs/index', $dados);
    }

    /**
     * Exportação de logs para arquivo CSV
     */
    public function exportar(): void {
        $this->adminRequired();

        $filtros = [
            'busca'       => $_GET['q'] ?? '',
            'usuario_id'  => $_GET['usuario_id'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim'    => $_GET['data_fim'] ?? '',
            'tipo_perfil' => $_GET['tipo_perfil'] ?? ''
        ];

        $this->service->exportarCsv($filtros);
    }

    /**
     * Limpeza dos logs (ação opcional do administrador)
     */
    public function limpar(): void {
        $this->adminRequired();

        $this->service->limparLogs();
        $this->redirect(URL_BASE . '/logs');
    }
}
