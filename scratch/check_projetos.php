<?php
require_once __DIR__ . '/../app/config/Config.php';
require_once __DIR__ . '/../app/database/ConnectionFactory.php';

try {
    $c = \app\database\ConnectionFactory::getConnection();
    $stmt = $c->query('SELECT id_projeto, id_usuario, fk_banco, nome_projeto, data_criacao, caminho_armazenamento FROM projeto ORDER BY id_projeto DESC LIMIT 20');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
