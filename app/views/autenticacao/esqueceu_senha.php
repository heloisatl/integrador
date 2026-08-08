<?php require_once __DIR__ . '/../include/head.php'; ?>

<style>
    body {
        background: linear-gradient(135deg, #0f172a 0%, #111827 100%);
        overflow-x: hidden;
        overflow-y: auto;
    }

    .auth-card {
        max-width: 480px;
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
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .auth-subtitle {
        color: #cbd5e1;
        margin-bottom: 20px;
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

    @media (max-width: 640px) {
        .auth-card {
            margin: 32px auto;
            padding: 20px;
            border-radius: 16px;
        }
    }
</style>

<div class="auth-card">
    <h1 class="auth-title">Esqueceu a senha?</h1>
    <p class="auth-subtitle">Esta função ainda pode ser implementada com envio de e-mail. Por enquanto, basta voltar ao login e usar a conta cadastrada.</p>
    <a href="<?= URL_BASE ?>/login" class="btn" style="display:inline-block;text-align:center;text-decoration:none;">Voltar para o login</a>
</div>
