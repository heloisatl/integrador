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

    <form method="POST" action="<?= URL_BASE ?>/usuarios/salvar" data-password-confirmation-form data-json-form>
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" placeholder="Digite o nome" minlength="3" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" placeholder="seu@email.com" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha *</label>
            <div style="position:relative;">
                <input type="password" id="senha" name="senha" placeholder="Digite uma senha segura" minlength="8" required data-password-value data-password-strength="forca-senha" style="width:100%;padding-right:44px;">
                <button type="button" data-password-toggle="senha" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
            </div>
            <small id="forca-senha"></small>
        </div>

        <div class="form-group">
            <label for="confirmacao">Confirme a senha *</label>
            <div style="position:relative;">
                <input type="password" id="confirmacao" name="confirmacao" placeholder="Repita a senha" minlength="8" required data-password-confirmation style="width:100%;padding-right:44px;">
                <button type="button" data-password-toggle="confirmacao" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
            </div>
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

<script src="<?= URL_BASE ?>/assets/js/usuarios.js"></script>

</body>

</html>