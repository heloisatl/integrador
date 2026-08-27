<?php require_once __DIR__ . '/../include/head.php'; ?>

<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Usuários</h1>
    <div class="page-actions">
        <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="btn btn-primary">
            Novo Usuário
        </a>
    </div>
</div>

<?php if (empty($usuarios)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">👤</div>
        <div class="empty-state-text">Nenhum usuário cadastrado</div>
        <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="btn btn-primary">
            Criar Primeiro Usuário
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
                                    Editar
                                </a>
                                <a href="<?= URL_BASE ?>/usuarios/excluir?id=<?= $usuario['id_usuario'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                    Excluir
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