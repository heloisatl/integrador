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
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.08);
        color: white;
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
        transition: background-color 0.2s ease;
    }

    .btn:hover {
        background-color: #4a59df;
    }

    .link-row {
        display: flex;
        justify-content: space-between;
        margin-top: 16px;
        font-size: 14px;
    }

    .link-row a {
        color: #c7d2fe;
        text-decoration: none;
    }

    .alert {
        padding: 12px;
        border-radius: 10px;
        background: rgba(248, 113, 113, 0.16);
        color: #fecaca;
        border: 1px solid rgba(248, 113, 113, 0.24);
        margin-bottom: 16px;
    }

    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .password-wrapper input {
        width: 100%;
        padding-right: 44px;
    }

    .btn-toggle-password {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        transition: color 0.2s ease;
    }

    .btn-toggle-password:hover {
        color: #f8fafc;
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
        <h1 class="auth-title">Criar conta</h1>
        <p class="auth-subtitle">Cadastre-se para acessar o painel.</p>

        <?php if (!empty($erro)) : ?>
            <div class="alert"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= URL_BASE ?>/cadastro/salvar">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <div class="password-wrapper">
                    <input type="password" id="senha" name="senha" required>
                    <button type="button" id="toggleSenha" class="btn-toggle-password" title="Mostrar/ocultar senha">
                        <i class="bi bi-eye" id="iconeOlho"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn">Cadastrar</button>
        </form>

        <script>
            document.getElementById('toggleSenha')?.addEventListener('click', function() {
                const inputSenha = document.getElementById('senha');
                const icone = document.getElementById('iconeOlho');
                
                if (inputSenha && icone) {
                    if (inputSenha.type === 'password') {
                        inputSenha.type = 'text';
                        icone.classList.remove('bi-eye');
                        icone.classList.add('bi-eye-slash');
                    } else {
                        inputSenha.type = 'password';
                        icone.classList.remove('bi-eye-slash');
                        icone.classList.add('bi-eye');
                    }
                }
            });
        </script>

        <div class="link-row">
            <a href="<?= URL_BASE ?>/login">Já tenho conta</a>
            <a href="<?= URL_BASE ?>/explorar">Continuar sem login</a>
        </div>
    </div>
</div>
</body>
</html>