<?php require_once __DIR__ . '/../include/head.php'; ?>

<style>
    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        font-family: 'Syne', sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--text);
    }

    .form-container {
        background-color: var(--panel);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 32px;
        max-width: 600px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 24px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    label {
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        text-transform: uppercase;
        color: var(--muted);
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background-color: var(--surface);
        color: var(--text);
        font-size: 14px;
        font-family: 'DM Sans', sans-serif;
        transition: all 0.2s ease;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    select:focus {
        outline: none;
        border-color: var(--accent);
        background-color: var(--panel);
        box-shadow: 0 0 0 3px rgba(91, 106, 240, 0.1);
    }

    input[type="text"]::placeholder,
    input[type="email"]::placeholder,
    input[type="password"]::placeholder {
        color: var(--muted);
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
    }

    .btn-primary {
        background-color: var(--accent);
        color: #ffffff;
        flex: 1;
    }

    .btn-primary:hover {
        background-color: var(--accent-h);
    }

    .btn-secondary {
        background-color: var(--surface);
        color: var(--text);
        border: 1px solid var(--border);
        flex: 1;
    }

    .btn-secondary:hover {
        background-color: var(--panel);
        border-color: var(--accent);
    }

    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .alert-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
</style>

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
    <form method="POST" action="<?= URL_BASE ?>/usuarios/atualizar">
        <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
        
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" placeholder="Digite o nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" placeholder="seu@email.com" value="<?= htmlspecialchars($usuario['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha (deixe em branco para manter a atual)</label>
            <input type="password" id="senha" name="senha" placeholder="Digite uma nova senha ou deixe em branco">
        </div>

        <div class="form-group">
            <label for="usuario_banco">Usuário do Banco de Dados</label>
            <input type="text" id="usuario_banco" name="usuario_banco" placeholder="Usuário para acesso ao BD" value="<?= htmlspecialchars($usuario['usuario_banco'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="servidor">Servidor</label>
            <input type="text" id="servidor" name="servidor" placeholder="localhost" value="<?= htmlspecialchars($usuario['servidor'] ?? '') ?>">
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
                ✅ Atualizar Usuário
            </button>
            <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
                ❌ Cancelar
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

</main>
</div>

</body>
</html>
