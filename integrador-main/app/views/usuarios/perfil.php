<?php require_once __DIR__ . '/../include/head.php'; ?>

<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<style>
    .profile-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1100;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        overflow-y: auto;
        background: var(--bg);
    }

    .profile-modal {
        position: relative;
        width: min(100%, 560px);
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        padding: 32px;
        border: 1px solid var(--border);
        border-radius: 20px;
        background: var(--panel);
        backdrop-filter: blur(16px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        color: var(--text);
    }

    .profile-modal-title {
        margin-bottom: 8px;
        font-size: 28px;
        font-weight: 700;
    }

    .profile-modal-subtitle {
        margin-bottom: 24px;
        color: var(--muted);
    }

    .profile-modal-close {
        position: absolute;
        top: 14px;
        right: 16px;
        color: var(--muted);
        font-size: 24px;
        line-height: 1;
        text-decoration: none;
    }

    .profile-modal .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
    }

    .profile-modal label {
        color: var(--muted);
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .profile-modal input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
        color: var(--text);
    }

    .profile-modal .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .profile-modal .btn {
        flex: 1;
        margin-top: 0;
        border: 0;
        border-radius: 10px;
        padding: 12px 16px;
        font-weight: 600;
        text-align: center;
    }

    .profile-modal .btn-primary {
        background: var(--accent);
        color: white;
        cursor: pointer;
    }

    .profile-modal .btn-secondary {
        background: var(--surface);
        color: var(--text);
        text-decoration: none;
    }

    .profile-modal-alert {
        margin-bottom: 16px;
        padding: 12px;
        border: 1px solid rgba(248, 113, 113, 0.24);
        border-radius: 10px;
        background: rgba(248, 113, 113, 0.16);
        color: #fecaca;
    }

    .profile-modal-success {
        margin-bottom: 16px;
        padding: 12px;
        border: 1px solid rgba(34, 197, 94, 0.24);
        border-radius: 10px;
        background: rgba(34, 197, 94, 0.16);
        color: #dcfce7;
    }

    @media (max-width: 640px) {
        .profile-modal-backdrop {
            padding: 12px;
        }

        .profile-modal {
            padding: 24px 20px;
            border-radius: 16px;
        }

        .profile-modal .form-actions {
            flex-direction: column;
        }
    }
</style>

<div class="profile-modal-backdrop">
<div class="profile-modal" role="dialog" aria-modal="true" aria-labelledby="profile-modal-title">
    <a href="<?= URL_BASE ?>/projetos" class="profile-modal-close" aria-label="Fechar">&times;</a>
    <h1 class="profile-modal-title" id="profile-modal-title">Gerenciar perfil</h1>
    <p class="profile-modal-subtitle">Atualize seus dados de acesso e informações pessoais.</p>
    <div class="profile-modal-success" data-success-message hidden></div>

    <?php if (!empty($erro)) : ?>
        <div class="profile-modal-alert">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_sucesso'])) : ?>
        <div class="profile-modal-success">
            <?= htmlspecialchars($_SESSION['flash_sucesso']) ?>
        </div>
        <?php unset($_SESSION['flash_sucesso']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= URL_BASE ?>/perfil/atualizar" data-json-form data-close-on-success="true" data-password-confirmation-form>
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" minlength="3" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="senha_atual">Senha atual (necessária para alterar a senha)</label>
            <div style="position:relative;">
                <input type="password" id="senha_atual" name="senha_atual" style="padding-right:44px;">
                <button type="button" data-password-toggle="senha_atual" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <div class="form-group">
            <label for="senha">Nova senha</label>
            <div style="position:relative;">
                <input type="password" id="senha" name="senha" minlength="8" data-password-value data-password-strength="forca-senha" style="padding-right:44px;">
                <button type="button" data-password-toggle="senha" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
            </div>
            <small id="forca-senha"></small>
        </div>

        <div class="form-group">
            <label for="confirmacao">Confirme a nova senha</label>
            <div style="position:relative;">
                <input type="password" id="confirmacao" name="confirmacao" minlength="8" data-password-confirmation style="padding-right:44px;">
                <button type="button" data-password-toggle="confirmacao" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar alterações</button>
            <a href="<?= URL_BASE ?>/projetos" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
</div>

</main>
</div>

<script src="<?= URL_BASE ?>/assets/js/usuarios.js"></script>

</body>
</html>