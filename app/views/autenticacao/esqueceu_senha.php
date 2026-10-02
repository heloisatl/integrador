<?php require_once __DIR__ . '/../include/head.php'; ?>

<style>
    body {
        background: linear-gradient(135deg, #0f172a 0%, #111827 100%);
        overflow-x: hidden;
        overflow-y: auto;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .auth-wrapper {
        width: 100%;
        max-width: 480px;
        padding: 40px 16px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .auth-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        text-decoration: none;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .auth-brand:hover {
        transform: translateY(-2px);
        opacity: 0.95;
    }

    .auth-brand .logo {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
    }

    .auth-brand .logo::after {
        width: 13px;
        height: 13px;
        border-width: 2.5px;
    }

    .auth-brand-text {
        font-family: 'Syne', sans-serif;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .auth-card {
        width: 100%;
        padding: 32px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        color: #f8fafc;
        box-sizing: border-box;
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
        background-color: #5b6af0;
        color: white;
        font-weight: 600;
        cursor: pointer;
        margin-top: 8px;
        display: inline-block;
        text-align: center;
        text-decoration: none;
        transition: background-color 0.2s ease;
        box-sizing: border-box;
    }

    .btn:hover {
        background-color: #4a59df;
    }

    @media (max-width: 640px) {
        .auth-card {
            margin: 32px auto;
            padding: 20px;
            border-radius: 16px;
        }
    }
</style>

<div class="auth-wrapper">
    <a href="<?= URL_BASE ?>/projetos" class="auth-brand" title="DevStudio">
        <span class="logo"></span>
        <span class="auth-brand-text title-link">DevStudio</span>
    </a>

    <div class="auth-card">
        <h1 class="auth-title">Esqueceu a senha?</h1>
        <p class="auth-subtitle">Esta função ainda pode ser implementada com envio de e-mail. Por enquanto, basta voltar ao login e usar a conta cadastrada.</p>
        <a href="<?= URL_BASE ?>/login" class="btn">Voltar para o login</a>
    </div>
</div>
</body>
</html>