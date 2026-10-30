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
                        <div class="perfil-avatar-botoes">
                            <label class="perfil-btn-foto" for="perfilAvatarInput"><i class="bi bi-camera" aria-hidden="true"></i> Escolher foto</label>
                            <input class="perfil-input-arquivo" type="file" id="perfilAvatarInput" name="avatar" accept="image/jpeg,image/png,image/webp">
                            <input type="hidden" name="remover_avatar" id="perfilRemoverAvatar" value="0">
                            <button type="button" class="perfil-btn-remover" id="perfilBtnRemover" <?= !$avatarUrl ? 'style="display: none;"' : '' ?>>
                                <i class="bi bi-trash" id="perfilBtnRemoverIcone" aria-hidden="true"></i>
                                <span id="perfilBtnRemoverTexto">Remover foto</span>
                            </button>
                        </div>
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
                <a href="<?= URL_BASE ?>/projetos" class="perfil-btn-voltar"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao início</a>
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
        const inputRemover = document.getElementById('perfilRemoverAvatar');
        const btnRemover = document.getElementById('perfilBtnRemover');
        const btnRemoverIcone = document.getElementById('perfilBtnRemoverIcone');
        const btnRemoverTexto = document.getElementById('perfilBtnRemoverTexto');
        const senhaAtual = document.getElementById('perfil-senha-atual');
        const senhaNova = document.getElementById('perfil-senha-nova');
        const confirmarSenha = document.getElementById('perfil-confirmar-senha');
        let urlPreview = null;
        const temAvatarOriginal = preview && preview.getAttribute('src') !== '';

        if (temAvatarOriginal) {
            preview.dataset.avatarOriginalSrc = preview.getAttribute('src');
        }

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
            if (inputRemover) inputRemover.value = '0';
            if (btnRemover) {
                btnRemover.style.display = 'inline-flex';
                btnRemover.classList.remove('ativo');
                if (btnRemoverIcone) btnRemoverIcone.className = 'bi bi-trash';
                if (btnRemoverTexto) btnRemoverTexto.textContent = 'Remover foto selecionada';
            }
        });

        btnRemover?.addEventListener('click', () => {
            // Se havia um arquivo recém-selecionado mas não salvo, cancela a seleção
            if (inputFoto?.files?.length > 0) {
                inputFoto.value = '';
                if (urlPreview) {
                    URL.revokeObjectURL(urlPreview);
                    urlPreview = null;
                }
                if (temAvatarOriginal) {
                    preview.src = preview.dataset.avatarOriginalSrc;
                    preview.hidden = false;
                    if (placeholder) placeholder.hidden = true;
                    inputRemover.value = '0';
                    btnRemover.classList.remove('ativo');
                    btnRemoverIcone.className = 'bi bi-trash';
                    btnRemoverTexto.textContent = 'Remover foto';
                } else {
                    preview.hidden = true;
                    if (placeholder) placeholder.hidden = false;
                    btnRemover.style.display = 'none';
                }
                return;
            }

            // Alternar estado de remoção do avatar já existente
            if (inputRemover.value === '1') {
                inputRemover.value = '0';
                preview.hidden = false;
                if (placeholder) placeholder.hidden = true;
                btnRemover.classList.remove('ativo');
                btnRemoverIcone.className = 'bi bi-trash';
                btnRemoverTexto.textContent = 'Remover foto';
            } else {
                inputRemover.value = '1';
                preview.hidden = true;
                if (placeholder) placeholder.hidden = false;
                btnRemover.classList.add('ativo');
                btnRemoverIcone.className = 'bi bi-arrow-counterclockwise';
                btnRemoverTexto.textContent = 'Desfazer remoção';
            }
        });

        form?.addEventListener('submit', (evento) => {
            const vaiAlterarSenha = senhaAtual.value !== '' || senhaNova.value !== '' || confirmarSenha.value !== '';
            const vaiRemoverFoto = inputRemover?.value === '1';
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
