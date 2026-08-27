<?php require_once __DIR__ . '/../include/head.php'; ?>

<style>
    body {
        background: linear-gradient(135deg, #0f172a 0%, #111827 100%);
        overflow-x: hidden;
        overflow-y: auto;
    }

    .auth-card {
        max-width: 500px;
        margin: 80px auto;
        padding: 32px;
        border-radius: 20px;
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.14);
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        color: #f8fafc;
    }

    .auth-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .auth-subtitle {
        color: #cbd5e1;
        margin-bottom: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
    }

    label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.5px;
    }

    input {
        padding: 12px 14px;
        border: 1px solid rgba(255,255,255,0.16);
        border-radius: 10px;
        background: rgba(255,255,255,0.08);
        color: white;
    }

    .btn {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        color: white;
        font-weight: 600;
        cursor: pointer;
        margin-top: 8px;
    }

    .alert {
        padding: 12px;
        border-radius: 10px;
        background: rgba(248,113,113,0.16);
        color: #fecaca;
        border: 1px solid rgba(248,113,113,0.24);
        margin-bottom: 16px;
    }

    @media (max-width: 640px) {
        .auth-card {
            margin: 32px auto;
            padding: 20px;
            border-radius: 16px;
        }
    }
</style>

<div class="auth-card">
    <h1 class="auth-title">Redefinir senha</h1>
    <p class="auth-subtitle">Digite a nova senha para sua conta.</p>

    <?php if (!empty($sucesso)) : ?>
        <div class="success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <?php if (!empty($erro)) : ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= URL_BASE ?>/redefinir-senha" data-password-confirmation-form data-json-form>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

        <div class="form-group">
            <label for="senha">Nova senha</label>
            <div style="position:relative;">
                <input type="password" id="senha" name="senha" minlength="8" required data-password-value data-password-strength="forca-senha" style="width:100%;padding-right:44px;">
                <button type="button" data-password-toggle="senha" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
            </div>
            <small id="forca-senha"></small>
        </div>

        <div class="form-group">
            <label for="confirmacao">Confirme a nova senha</label>
            <div style="position:relative;">
                <input type="password" id="confirmacao" name="confirmacao" minlength="8" required data-password-confirmation style="width:100%;padding-right:44px;">
                <button type="button" data-password-toggle="confirmacao" aria-label="Mostrar senha" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:inherit;cursor:pointer;"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <button type="submit" class="btn">Salvar nova senha</button>
    </form>
</div>

<script src="<?= URL_BASE ?>/assets/js/usuarios.js"></script>
