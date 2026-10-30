<?php
include_once(__DIR__ . "/../include/head.php");
include_once(__DIR__ . "/../include/navigation.php");

$nomeExibicao = $nomeExibicao ?? 'PERFIL';
$nomeUsuario = $nomeUsuario ?? '';
$emailUsuario = $emailUsuario ?? '';
$avatarUrl = $avatarUrl ?? null;
$mensagemPerfil = $mensagemPerfil ?? '';
$tipoMensagemPerfil = $tipoMensagemPerfil ?? 'sucesso';
$csrfPerfil = $csrfPerfil ?? '';
?>

<link rel="stylesheet" href="<?= URL_BASE ?>/assets/css/pagina-mvc.css">
<link rel="stylesheet" href="<?= URL_BASE ?>/assets/css/perfil.css">

<div class="perfil-container">
        <header class="perfil-header">
            <p class="etiqueta-inicial">Conta e segurança</p>
            <h1 class="perfil-nome-titulo"><?= htmlspecialchars($nomeExibicao, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="perfil-subtitulo">Gerencie seus dados pessoais, sua foto e a segurança da conta.</p>
        </header>

        <?php if ($mensagemPerfil !== ''): ?>
            <div class="perfil-feedback perfil-feedback-<?= htmlspecialchars($tipoMensagemPerfil, ENT_QUOTES, 'UTF-8') ?>" role="status">
                <i class="bi <?= $tipoMensagemPerfil === 'sucesso' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>" aria-hidden="true"></i>
                <?= htmlspecialchars($mensagemPerfil, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="perfil-formulario" action="<?= URL_BASE ?>/perfil/atualizar" method="post" enctype="multipart/form-data" id="perfilForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfPerfil, ENT_QUOTES, 'UTF-8') ?>">

            <section class="perfil-card">
                <div class="perfil-secao-cabecalho">
                    <span class="perfil-secao-icone"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <div>
                        <h2>Dados pessoais</h2>
                        <p>Atualize como suas informações aparecem na plataforma.</p>
                    </div>
                </div>

                <div class="perfil-avatar-editor">
                    <div class="perfil-avatar-wrapper">
                        <div class="perfil-avatar">
                            <?php if ($avatarUrl): ?>
                                <img id="perfilAvatarPreview" src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil">
                            <?php else: ?>
                                <img id="perfilAvatarPreview" src="" alt="Prévia da foto de perfil" hidden>
                                <i class="bi bi-person-fill perfil-avatar-icon" id="perfilAvatarPlaceholder" aria-hidden="true"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="perfil-avatar-controles">
                        <strong>Foto de perfil</strong>
                        <span>JPG, PNG ou WEBP · máximo 2 MB</span>
                        <label class="perfil-btn-foto" for="perfilAvatarInput"><i class="bi bi-camera" aria-hidden="true"></i> Escolher foto</label>
                        <input class="perfil-input-arquivo" type="file" id="perfilAvatarInput" name="avatar" accept="image/jpeg,image/png,image/webp">
                        <?php if ($avatarUrl): ?>
                            <label class="perfil-remover-avatar"><input type="checkbox" name="remover_avatar" value="1" id="perfilRemoverAvatar"> Remover foto atual</label>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="perfil-grade-campos">
                    <div class="perfil-campo">
                        <label for="perfil-nome">Nome completo</label>
                        <input type="text" id="perfil-nome" name="nome" value="<?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?>" maxlength="60" autocomplete="name" required>
                    </div>
                    <div class="perfil-campo">
                        <label for="perfil-email">E-mail</label>
                        <input type="email" id="perfil-email" name="email" value="<?= htmlspecialchars($emailUsuario, ENT_QUOTES, 'UTF-8') ?>" maxlength="60" autocomplete="email" required>
                    </div>
                </div>
            </section>

            <section class="perfil-card perfil-card-seguranca">
                <div class="perfil-secao-cabecalho">
                    <span class="perfil-secao-icone"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                    <div>
                        <h2>Alterar senha</h2>
                        <p>Deixe os campos vazios se não deseja trocar sua senha.</p>
                    </div>
                </div>

                <div class="perfil-campo">
                    <label for="perfil-senha-atual">Senha atual</label>
                    <input type="password" id="perfil-senha-atual" name="senha_atual" autocomplete="current-password">
                </div>
                <div class="perfil-grade-campos">
                    <div class="perfil-campo">
                        <label for="perfil-senha-nova">Nova senha</label>
                        <input type="password" id="perfil-senha-nova" name="senha_nova" autocomplete="new-password" minlength="10" aria-describedby="perfilRegraSenha">
                    </div>
                    <div class="perfil-campo">
                        <label for="perfil-confirmar-senha">Confirmar nova senha</label>
                        <input type="password" id="perfil-confirmar-senha" name="confirmar_senha" autocomplete="new-password" minlength="10">
                    </div>
                </div>
                <p class="perfil-ajuda-senha" id="perfilRegraSenha">Use pelo menos 10 caracteres, com letras maiúsculas e minúsculas, número e símbolo.</p>
            </section>

            <div class="perfil-acoes">
                <button type="submit" class="perfil-btn-salvar"><i class="bi bi-check2-circle" aria-hidden="true"></i> Salvar alterações</button>
            </div>
        </form>
    </div>

<script>
    (() => {
        const form = document.getElementById('perfilForm');
        const inputFoto = document.getElementById('perfilAvatarInput');
        const preview = document.getElementById('perfilAvatarPreview');
        const placeholder = document.getElementById('perfilAvatarPlaceholder');
        const removerFoto = document.getElementById('perfilRemoverAvatar');
        const senhaAtual = document.getElementById('perfil-senha-atual');
        const senhaNova = document.getElementById('perfil-senha-nova');
        const confirmarSenha = document.getElementById('perfil-confirmar-senha');
        let urlPreview = null;

        inputFoto?.addEventListener('change', () => {
            const arquivo = inputFoto.files?.[0];
            if (!arquivo) return;
            if (arquivo.size > 2 * 1024 * 1024) {
                alert('A foto deve ter no máximo 2 MB.');
                inputFoto.value = '';
                return;
            }
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(arquivo.type)) {
                alert('Selecione uma imagem JPG, PNG ou WEBP.');
                inputFoto.value = '';
                return;
            }
            if (urlPreview) URL.revokeObjectURL(urlPreview);
            urlPreview = URL.createObjectURL(arquivo);
            preview.src = urlPreview;
            preview.hidden = false;
            if (placeholder) placeholder.hidden = true;
            if (removerFoto) removerFoto.checked = false;
        });

        removerFoto?.addEventListener('change', () => {
            if (removerFoto.checked && inputFoto) inputFoto.value = '';
            if (removerFoto.checked && preview) preview.hidden = true;
            if (!removerFoto.checked && preview?.dataset.avatarOriginal) preview.hidden = false;
            if (placeholder) placeholder.hidden = !removerFoto.checked;
        });

        if (preview?.getAttribute('src')) preview.dataset.avatarOriginal = '1';

        form?.addEventListener('submit', (evento) => {
            const vaiAlterarSenha = senhaAtual.value !== '' || senhaNova.value !== '' || confirmarSenha.value !== '';
            const vaiRemoverFoto = removerFoto?.checked === true;
            const acoesCriticas = [];
            if (vaiAlterarSenha) acoesCriticas.push('alterar sua senha');
            if (vaiRemoverFoto) acoesCriticas.push('remover sua foto de perfil');
            if (acoesCriticas.length && !window.confirm(`Você está prestes a ${acoesCriticas.join(' e ')}. Deseja continuar?`)) {
                evento.preventDefault();
            }
        });
    })();
</script>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
</main>
</div>
</body>
</html>
