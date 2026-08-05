<?php require_once __DIR__ . '/../include/head.php'; ?>

<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Criar Novo Usuário</h1>
</div>

<div class="form-container">
    <?php if (!empty($erro)) : ?>
        <div class="alert" style="margin-bottom: 16px; padding: 12px; border-radius: 8px; background: rgba(240, 68, 68, 0.12); color: #b42318; border: 1px solid rgba(240, 68, 68, 0.2);">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= URL_BASE ?>/usuarios/salvar">
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" placeholder="Digite o nome">
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" placeholder="seu@email.com">
        </div>

        <div class="form-group">
            <label for="senha">Senha *</label>
            <input type="password" id="senha" name="senha" placeholder="Digite uma senha segura">
        </div>

        <div class="form-group">
            <label for="usuario_banco">Usuário do Banco de Dados</label>
            <input type="text" id="usuario_banco" name="usuario_banco" placeholder="Usuário para acesso ao BD">
        </div>

        <div class="form-group">
            <label for="servidor">Servidor</label>
            <input type="text" id="servidor" name="servidor" placeholder="localhost">
        </div>

        <div class="form-group">
            <label for="tipo_perfil">Tipo de Perfil *</label>
            <select id="tipo_perfil" name="tipo_perfil">
                <option value="">Selecione um perfil</option>
                <option value="admin">Administrador</option>
                <option value="usuario">Usuário</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                Salvar Usuário
            </button>
            <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

</main>
</div>

</body>

</html>