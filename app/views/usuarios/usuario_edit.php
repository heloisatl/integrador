<?php require_once __DIR__ . '/../include/head.php'; ?>

<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Editar Usuário</h1>
</div>

<?php if (empty($usuario)): ?>
    <div class="alert alert-danger">
        ⚠️ Usuário não encontrado
    </div>
    <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
        ← Voltar
    </a>
<?php else: ?>
    <div class="form-container">
        <form method="POST" action="<?= URL_BASE ?>/usuarios/atualizar">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">

            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha (deixe em branco para manter a atual)</label>
                <input type="password" id="senha" name="senha" placeholder="Digite uma nova senha ou deixe em branco">
            </div>

            <div class="form-group">
                <label for="usuario_banco">Usuário do Banco de Dados</label>
                <input type="text" id="usuario_banco" name="usuario_banco" placeholder="Usuário para acesso ao BD" value="<?= htmlspecialchars($usuario['usuario_banco'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="servidor">Servidor</label>
                <input type="text" id="servidor" name="servidor" placeholder="localhost" value="<?= htmlspecialchars($usuario['servidor'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="tipo_perfil">Tipo de Perfil *</label>
                <select id="tipo_perfil" name="tipo_perfil" required>
                    <option value="">Selecione um perfil</option>
                    <option value="admin" <?= ($usuario['tipo_perfil'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                    <option value="usuario" <?= ($usuario['tipo_perfil'] === 'usuario') ? 'selected' : '' ?>>Usuário</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Atualizar Usuário
                </button>
                <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

</main>
</div>

</body>

</html>