<?php require_once __DIR__ . '/../include/head.php'; ?>
<link rel="stylesheet" href="<?= defined('URL_BASE') ? URL_BASE : '' ?>/assets/css/form-styles.css">
<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Criar Novo Projeto</h1>
</div>
<form action="/projetos/cadastrar/opcoes" method="POST">
    <div class="form-container">
        <div id="mvc-s1">
        <div class="card">
        <div class="card-title"><span class="step-badge">1</span> Configurar Projeto</div>
        
        <div class="field-row">
            <div class="field">
            <label>Nome do Projeto <span class="tip">Sem espaços</span></label>
            <input value="<?= $nome ?? '' ?>" type="text" name="nome" id="nome" placeholder="sistema_oficina">
            </div>
            <div class="field">
            <label>Servidor</label>
            <input value="<?= gethostbyaddr($_SERVER['REMOTE_ADDR']) ?>" type="text" name="server" id="server" placeholder="seu host">
            </div>
        </div>
        <div class="field-row">
            <div class="field">
            <label>Usuário</label>
            <input value="<?= $user ?? '' ?>" type="text" name="user" id="user" placeholder="root">
            </div>
            <div class="field">
            <label>Senha</label>
            <input value="<?= $pass ?? '' ?>" type="password" name="pass" id="pass" placeholder="••••••" onblur="carregarBanco()">
            </div>
        </div>
        <div class="field-row">
            <div class="field">
            <label>Banco de Dados</label>
            <select name="mvc-banco" id="mvc-banco"><option value="">— preencha a senha para carregar —</option></select>
            </div>
            <div class="field" style="justify-content:flex-end">
            <label>&nbsp;</label>
            <button class="btn btn-ghost btn-sm" type="button" onclick="carregarBanco()" style="align-self:flex-start">Recarregar bancos</button>
            </div>
        </div>
        <div id="mvc-s1-msg"></div>
        <div class="btn-row">
            <button class="btn btn-primary">Avançar</button>
        </div>
        </div>
    </div>
    </div>
</form>

    <!-- <form action="/projetos/editar" method="POST">
        <input type="hidden" value="1" name="id_projeto" id="id_projeto">
        <button>Editar projeto de id numero 1(e um botao so pra mostrar a funcao de editar)</button>
    </form> -->
</main>
</div>

    <script src="/assets/js/mvcLoad.js"></script>
</body>
</html>

