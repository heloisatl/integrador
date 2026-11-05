<?php

namespace app\services;

use app\repositories\LogRepository;

class LogService {
    private LogRepository $repository;

    public function __construct() {
        $this->repository = new LogRepository();
    }

    /**
     * Registra uma ação cronológica no log do sistema (UC 15 / RF 15)
     */
    public static function registrar(string $acao, ?int $fkUsuario = null): bool {
        try {
            $idUsuarioFinal = $fkUsuario;

            if ($idUsuarioFinal === null && isset($_SESSION['usuario_logado'])) {
                $usuario = $_SESSION['usuario_logado'];
                if (method_exists($usuario, 'getIdUsuario')) {
                    $idUsuarioFinal = (int) $usuario->getIdUsuario();
                }
            }

            // Se ainda for nulo (ação do sistema ou convidado), atrela ao ID 1 (Admin padrão)
            if ($idUsuarioFinal === null || $idUsuarioFinal <= 0) {
                $idUsuarioFinal = 1;
            }

            $repo = new LogRepository();
            return $repo->create($idUsuarioFinal, $acao);
        } catch (\Throwable $e) {
            error_log("Falha ao registrar log de auditoria: " . $e->getMessage());
            return false;
        }
    }

    public function listarLogs(array $filtros = []): array {
        $this->popularLogsIniciaisSeVazio();
        return $this->repository->getAll($filtros);
    }

    public function contarLogs(array $filtros = []): int {
        return $this->repository->contarTotal($filtros);
    }

    public function obterEstatisticas(): array {
        $this->popularLogsIniciaisSeVazio();
        return $this->repository->getEstatisticas();
    }

    public function obterUsuariosFiltro(): array {
        return $this->repository->getUsuariosParaFiltro();
    }

    public function limparLogs(): bool {
        $sucesso = $this->repository->limparHistoricoLogs();
        if ($sucesso) {
            self::registrar('O histórico de logs foi limpo pelo Administrador.');
        }
        return $sucesso;
    }

    /**
     * Caso o banco esteja zerado, insere os logs essenciais de inicialização
     */
    private function popularLogsIniciaisSeVazio(): void {
        try {
            $total = $this->repository->contarTotal();
            if ($total === 0) {
                $dataBase = date('Y-m-d H:i:s', strtotime('-2 hours'));
                $this->repository->create(1, 'Inicialização do banco de dados e schema DevStudio', $dataBase);
                $this->repository->create(1, 'Usuário Administrador cadastrado com privilégios completos', date('Y-m-d H:i:s', strtotime('-90 minutes')));
                $this->repository->create(1, 'Módulos MVC Creator e PageMaker disponibilizados', date('Y-m-d H:i:s', strtotime('-30 minutes')));
                $this->repository->create(1, 'Módulo de Auditoria e Logs do Sistema (RF 15) ativado', date('Y-m-d H:i:s'));
            }
        } catch (\Throwable $e) {
            error_log("Aviso ao popular logs iniciais: " . $e->getMessage());
        }
    }

    /**
     * Exporta os registros filtrados em formato CSV estruturado
     */
    public function exportarCsv(array $filtros = []): void {
        $logs = $this->repository->getAll($filtros, 2000, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=devstudio_logs_' . date('Ymd_His') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM UTF-8 para Excel abrir sem corromper acentos
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeçalho CSV
        fputcsv($output, ['ID Log', 'Data e Hora', 'ID Usuário', 'Nome do Usuário', 'E-mail', 'Tipo Perfil', 'Ação / Evento Registrado'], ';');

        foreach ($logs as $log) {
            fputcsv($output, [
                $log->getIdLog(),
                $log->getDataFormatada(),
                $log->getFkUsuario(),
                $log->getNomeUsuario(),
                $log->getEmailUsuario(),
                $log->getTipoPerfil(),
                $log->getAcao()
            ], ';');
        }

        fclose($output);
        exit();
    }
}
