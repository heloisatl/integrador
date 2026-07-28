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

    .auth-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .auth-subtitle {
        color: #cbd5e1;
        margin-bottom: 24px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        color: white;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        margin-top: 16px;
    }

    .code-box {
        padding: 18px 16px;
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 14px;
        margin-top: 20px;
        color: #c7d2fe;
        word-break: break-all;
        font-family: monospace;
    }
</style>

<div class="auth-card">
    <h1 class="auth-title">Link de recuperação</h1>
    <p class="auth-subtitle">Clique no link abaixo para redefinir sua senha.</p>

    <div class="code-box">
        <?= htmlspecialchars(URL_BASE . '/redefinir-senha?token=' . ($token ?? '')) ?>
    </div>

    <a href="<?= URL_BASE ?>/redefinir-senha?token=<?= urlencode($token) ?>" class="btn">Redefinir senha agora</a>
</div>
