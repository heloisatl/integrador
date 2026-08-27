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
        <form method="POST" action="<?= URL_BASE ?>/usuarios/atualizar" data-password-confirmation-form data-json-form>
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">

            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o nome" value="<?= htmlspecialchars($usuario['nome']) ?>" minlength="3" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha (deixe em branco para manter a atual)</label>
                <div style="position:relative;">
                    <input type="password" id="senha" name="senha" placeholder="Digite uma nova senha ou deixe em branco" minlength="8" data-password-value data-password-strength="forca-senha" style="width:100%;padding-right:44px;">
                    <button type="button" data-password-toggle="senha" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
                </div>
                <small id="forca-senha"></small>
            </div>

            <div class="form-group">
                <label for="confirmacao">Confirme a nova senha</label>
                <div style="position:relative;">
                    <input type="password" id="confirmacao" name="confirmacao" placeholder="Repita a nova senha" minlength="8" data-password-confirmation style="width:100%;padding-right:44px;">
                    <button type="button" data-password-toggle="confirmacao" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
                </div>
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

<script src="<?= URL_BASE ?>/assets/js/usuarios.js"></script>

</body>

</html>