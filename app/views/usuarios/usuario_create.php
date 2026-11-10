<?php require_once __DIR__ . '/../include/head.php'; ?>

<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-person-plus"></i> Criar Novo Usuário
    </h1>
    <div class="page-actions">
        <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="form-container">
    <?php if (!empty($erro)) : ?>
        <div class="alert alert-danger" style="margin-bottom: 24px;">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="alert-body">
                <strong>Atenção</strong>
                <div><?= htmlspecialchars($erro) ?></div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= URL_BASE ?>/usuarios/salvar">
        <!-- Seção 1: Credenciais e Dados de Acesso -->
        <div class="form-section-header">
            <span class="form-section-icon"><i class="bi bi-person-badge"></i></span>
            <div>
                <h3>Dados Pessoais e Acesso</h3>
                <p>Informações de autenticação e nível de privilégio na plataforma.</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o nome completo" value="<?= htmlspecialchars($nome ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha *</label>
                <input type="password" id="senha" name="senha" placeholder="Digite uma senha segura" required>
            </div>

            <div class="form-group">
                <label for="tipo_perfil">Tipo de Perfil *</label>
                <select id="tipo_perfil" name="tipo_perfil" required>
                    <option value="">Selecione um perfil</option>
                    <option value="admin" <?= (($tipo_perfil ?? '') === 'admin') ? 'selected' : '' ?>>Administrador</option>
                    <option value="usuario" <?= (($tipo_perfil ?? '') === 'usuario') ? 'selected' : '' ?>>Usuário</option>
                </select>
            </div>
        </div>

        <!-- Seção 2: Conexão Local com Banco de Dados (MySQL) -->
        <div class="form-section-header" style="margin-top: 32px;">
            <span class="form-section-icon"><i class="bi bi-database"></i></span>
            <div>
                <h3>Ambiente de Banco de Dados Local</h3>
                <p>Configuração das credenciais padrão de acesso ao MySQL (opcional).</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="usuario_banco">Usuário do Banco de Dados</label>
                <input type="text" id="usuario_banco" name="usuario_banco" placeholder="ex: root" value="<?= htmlspecialchars($usuario_banco ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="servidor">Servidor</label>
                <input type="text" id="servidor" name="servidor" placeholder="localhost" value="<?= htmlspecialchars($servidor ?? 'localhost') ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Salvar Usuário
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
</main>
</div>

</body>

</html>
