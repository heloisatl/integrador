<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Log;
use PDO;
use Exception;

class LogRepository {
    private ?PDO $connection = null;

    public function __construct() {
        try {
            $this->connection = ConnectionFactory::getConnection();
            $this->garantirTabela();
        } catch (Exception $e) {
            error_log("Erro ao conectar no banco para LogRepository: " . $e->getMessage());
        }
    }

    private function getConn(): ?PDO {
        if ($this->connection === null) {
            try {
                $this->connection = ConnectionFactory::getConnection();
            } catch (Exception $e) {
                return null;
            }
        }
        return $this->connection;
    }

    public function garantirTabela(): void {
        $conn = $this->getConn();
        if (!$conn) return;

        $sql = "CREATE TABLE IF NOT EXISTS `log` (
            `id_log` INT NOT NULL AUTO_INCREMENT,
            `fk_usuario` INT NOT NULL,
            `acao` VARCHAR(255) NOT NULL,
            `data` DATETIME NOT NULL,
            PRIMARY KEY (`id_log`),
            INDEX `idx_log_usuario` (`fk_usuario`)
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;";

        try {
            $conn->exec($sql);
        } catch (Exception $e) {
            error_log("Falha ao garantir tabela log: " . $e->getMessage());
        }
    }

    public function create(int $fkUsuario, string $acao, ?string $data = null): bool {
        $conn = $this->getConn();
        if (!$conn) return false;

        $dataHora = $data ?? date('Y-m-d H:i:s');
        $sql = "INSERT INTO `log` (`fk_usuario`, `acao`, `data`) VALUES (:fk_usuario, :acao, :data)";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':fk_usuario', $fkUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':acao', mb_substr($acao, 0, 255));
            $stmt->bindValue(':data', $dataHora);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao salvar log: " . $e->getMessage());
            return false;
        }
    }

    public function getAll(array $filtros = [], int $limite = 250, int $offset = 0): array {
        $conn = $this->getConn();
        if (!$conn) return [];

        $where = [];
        $params = [];

        if (!empty($filtros['busca'])) {
            $where[] = "(l.acao LIKE :busca OR u.nome LIKE :busca OR u.email LIKE :busca)";
            $params[':busca'] = '%' . trim($filtros['busca']) . '%';
        }

        if (!empty($filtros['usuario_id']) && is_numeric($filtros['usuario_id'])) {
            $where[] = "l.fk_usuario = :fk_usuario";
            $params[':fk_usuario'] = (int) $filtros['usuario_id'];
        }

        if (!empty($filtros['data_inicio'])) {
            $where[] = "l.data >= :data_inicio";
            $params[':data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['data_fim'])) {
            $where[] = "l.data <= :data_fim";
            $params[':data_fim'] = $filtros['data_fim'] . ' 23:59:59';
        }

        if (!empty($filtros['tipo_perfil'])) {
            $where[] = "u.tipo_perfil = :tipo_perfil";
            $params[':tipo_perfil'] = trim($filtros['tipo_perfil']);
        }

        $clausulaWhere = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT l.id_log, l.fk_usuario, l.acao, l.data,
                       u.nome AS nome_usuario, u.email AS email_usuario, u.tipo_perfil
                FROM `log` l
                LEFT JOIN `usuario` u ON u.id_usuario = l.fk_usuario
                {$clausulaWhere}
                ORDER BY l.data DESC, l.id_log DESC
                LIMIT :limite OFFSET :offset";

        try {
            $stmt = $conn->prepare($sql);
            foreach ($params as $chave => $valor) {
                $stmt->bindValue($chave, $valor);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $logs = [];
            foreach ($linhas as $linha) {
                $logs[] = new Log(
                    (int) $linha['id_log'],
                    (int) $linha['fk_usuario'],
                    (string) $linha['acao'],
                    (string) $linha['data'],
                    $linha['nome_usuario'] ?? null,
                    $linha['email_usuario'] ?? null,
                    $linha['tipo_perfil'] ?? null
                );
            }
            return $logs;
        } catch (Exception $e) {
            error_log("Erro ao buscar logs: " . $e->getMessage());
            return [];
        }
    }

    public function contarTotal(array $filtros = []): int {
        $conn = $this->getConn();
        if (!$conn) return 0;

        $where = [];
        $params = [];

        if (!empty($filtros['busca'])) {
            $where[] = "(l.acao LIKE :busca OR u.nome LIKE :busca OR u.email LIKE :busca)";
            $params[':busca'] = '%' . trim($filtros['busca']) . '%';
        }

        if (!empty($filtros['usuario_id']) && is_numeric($filtros['usuario_id'])) {
            $where[] = "l.fk_usuario = :fk_usuario";
            $params[':fk_usuario'] = (int) $filtros['usuario_id'];
        }

        if (!empty($filtros['data_inicio'])) {
            $where[] = "l.data >= :data_inicio";
            $params[':data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['data_fim'])) {
            $where[] = "l.data <= :data_fim";
            $params[':data_fim'] = $filtros['data_fim'] . ' 23:59:59';
        }

        if (!empty($filtros['tipo_perfil'])) {
            $where[] = "u.tipo_perfil = :tipo_perfil";
            $params[':tipo_perfil'] = trim($filtros['tipo_perfil']);
        }

        $clausulaWhere = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) FROM `log` l
                LEFT JOIN `usuario` u ON u.id_usuario = l.fk_usuario
                {$clausulaWhere}";

        try {
            $stmt = $conn->prepare($sql);
            foreach ($params as $chave => $valor) {
                $stmt->bindValue($chave, $valor);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getEstatisticas(): array {
        $conn = $this->getConn();
        $estatisticas = [
            'total'               => 0,
            'hoje'                => 0,
            'usuarios_distintos'  => 0,
            'ultimo_evento_texto' => '-',
            'ultimo_evento_data'  => '-'
        ];

        if (!$conn) return $estatisticas;

        try {
            $estatisticas['total'] = (int) $conn->query("SELECT COUNT(*) FROM `log`")->fetchColumn();
            $estatisticas['hoje'] = (int) $conn->query("SELECT COUNT(*) FROM `log` WHERE DATE(`data`) = CURDATE()")->fetchColumn();
            $estatisticas['usuarios_distintos'] = (int) $conn->query("SELECT COUNT(DISTINCT `fk_usuario`) FROM `log`")->fetchColumn();

            $ultimo = $conn->query("SELECT `acao`, `data` FROM `log` ORDER BY `data` DESC, `id_log` DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($ultimo) {
                $estatisticas['ultimo_evento_texto'] = $ultimo['acao'];
                $estatisticas['ultimo_evento_data'] = date('d/m/Y H:i:s', strtotime($ultimo['data']));
            }
        } catch (Exception $e) {
            error_log("Erro ao carregar estatísticas de logs: " . $e->getMessage());
        }

        return $estatisticas;
    }

    public function getUsuariosParaFiltro(): array {
        $conn = $this->getConn();
        if (!$conn) return [];

        try {
            $sql = "SELECT DISTINCT u.id_usuario, u.nome, u.email, u.tipo_perfil
                    FROM `usuario` u
                    INNER JOIN `log` l ON l.fk_usuario = u.id_usuario
                    ORDER BY u.nome ASC";
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function limparHistoricoLogs(): bool {
        $conn = $this->getConn();
        if (!$conn) return false;

        try {
            $conn->exec("TRUNCATE TABLE `log`");
            return true;
        } catch (Exception $e) {
            error_log("Erro ao truncar tabela de logs: " . $e->getMessage());
            return false;
        }
    }
}
