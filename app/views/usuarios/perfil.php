<?php
include_once(__DIR__ . "/../include/head.php");
include_once(__DIR__ . "/../include/navigation.php");

$nomeExibicao = $nomeExibicao ?? 'FULANO';
$nomeUsuario  = $nomeUsuario ?? 'Fulano da Silva';
$emailUsuario = $emailUsuario ?? 'fulano@devstudio.com';
?>

<link rel="stylesheet" href="<?= URL_BASE ?>/assets/css/pagina-mvc.css">
<link rel="stylesheet" href="<?= URL_BASE ?>/assets/css/perfil.css">

<div class="perfil-container">

    <!-- Cabeçalho com Nome de Destaque -->
    <header class="perfil-header">
        <h1 class="perfil-nome-titulo"><?= htmlspecialchars($nomeExibicao) ?></h1>
    </header>

    <!-- Avatar Circle com Badge do Lápis -->
    <div class="perfil-avatar-wrapper">
        <div class="perfil-avatar">
            <i class="bi bi-person-fill perfil-avatar-icon" aria-hidden="true"></i>
        </div>
        <button type="button" class="perfil-avatar-badge" title="Alterar foto de perfil" aria-label="Alterar foto de perfil">
            <i class="bi bi-pencil-fill" aria-hidden="true"></i>
        </button>
    </div>

    <!-- Card de Formulário dos Dados -->
    <div class="perfil-card">
        <form class="perfil-form" onsubmit="event.preventDefault();">
            <div class="perfil-campo">
                <label for="perfil-email">Email</label>
                <input type="email" id="perfil-email" name="email" value="<?= htmlspecialchars($emailUsuario) ?>" placeholder="seu.email@exemplo.com">
            </div>

            <div class="perfil-campo">
                <label for="perfil-nome">Nome</label>
                <input type="text" id="perfil-nome" name="nome" value="<?= htmlspecialchars($nomeUsuario) ?>" placeholder="Seu nome completo">
            </div>

            <div class="perfil-campo">
                <label for="perfil-senha">Senha</label>
                <div class="perfil-senha-wrapper">
                    <input type="password" id="perfil-senha" name="senha" value="12345678" placeholder="Digite uma nova senha">
                    <button type="button" class="perfil-btn-toggle-senha" id="perfilToggleSenha" title="Mostrar/ocultar senha" aria-label="Mostrar ou ocultar senha">
                        <i class="bi bi-eye" id="perfilIconeOlho"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Botão Salvar Alterações -->
    <div class="perfil-acoes">
        <button type="button" class="perfil-btn-salvar">
            Salvar alterações
        </button>
    </div>

</div>

<script>
    document.getElementById('perfilToggleSenha')?.addEventListener('click', function() {
        const inputSenha = document.getElementById('perfil-senha');
        const icone = document.getElementById('perfilIconeOlho');
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

<?php require_once __DIR__ . '/../include/footer.php'; ?>
</main>
</div>
</body>
</html>
