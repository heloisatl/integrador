<?php require_once __DIR__ . '/../include/head.php'; ?>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
    }

    .page-title {
        font-family: 'Syne', sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--text);
    }

    .page-actions {
        display: flex;
        gap: 12px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
    }

    .btn-primary {
        background-color: var(--accent);
        color: #ffffff;
    }

    .btn-primary:hover {
        background-color: var(--accent-h);
    }

    .btn-danger {
        background-color: #ef4444;
        color: #ffffff;
    }

    .btn-danger:hover {
        background-color: #dc2626;
    }

    .btn-edit {
        background-color: #3b82f6;
        color: #ffffff;
    }

    .btn-edit:hover {
        background-color: #2563eb;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .table-container {
        background-color: var(--panel);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background-color: var(--surface);
        border-bottom: 1px solid var(--border);
    }

    th {
        padding: 16px;
        text-align: left;
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        text-transform: uppercase;
        color: var(--muted);
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background-color 0.2s ease;
    }

    tbody tr:hover {
        background-color: var(--surface);
    }

    tbody tr:last-child {
        border-bottom: none;
    }

    td {
        padding: 16px;
        color: var(--text);
    }

    .table-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .empty-state {
        text-align: center;
        padding: 64px 32px;
        color: var(--muted);
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .empty-state-text {
        font-size: 16px;
        margin-bottom: 24px;
    }
</style>

<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Usuários</h1>
    <div class="page-actions">
        <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="btn btn-primary">
            ➕ Novo Usuário
        </a>
    </div>
</div>

<?php if (empty($usuarios)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">👤</div>
        <div class="empty-state-text">Nenhum usuário cadastrado</div>
        <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="btn btn-primary">
            ➕ Criar Primeiro Usuário
        </a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo de Perfil</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= htmlspecialchars($usuario['tipo_perfil']) ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?= URL_BASE ?>/usuarios/editar?id=<?= $usuario['id_usuario'] ?>" class="btn btn-edit btn-sm">
                                ✏️ Editar
                            </a>
                            <a href="<?= URL_BASE ?>/usuarios/excluir?id=<?= $usuario['id_usuario'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                🗑️ Excluir
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

</main>
</div>

</body>
</html>
