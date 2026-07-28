<?php require_once __DIR__ . '/../include/head.php'; ?>

<style>
    body {
        background: linear-gradient(135deg, #0f172a 0%, #111827 100%);
    }

    .auth-card {
        max-width: 520px;
        margin: 80px auto;
        padding: 32px;
        border-radius: 20px;
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.14);
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        color: #f8fafc;
    }

    .btn {
        display: inline-block;
        margin-top: 16px;
        padding: 12px 16px;
        border-radius: 10px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        color: white;
        text-decoration: none;
        font-weight: 600;
    }
</style>

<div class="auth-card">
    <h1 style="font-size:26px;font-weight:700;margin-bottom:10px;">Você está acessando sem login</h1>
    <p style="color:#cbd5e1;line-height:1.6;">Este bloco pode ser usado como área pública para visualizar informações sem autenticação. Para entrar no painel completo, faça login ou crie uma conta.</p>
    <a href="<?= URL_BASE ?>/login" class="btn">Entrar agora</a>
</div>
