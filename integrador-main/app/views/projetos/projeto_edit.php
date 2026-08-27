<?php require_once __DIR__ . '/../include/head.php'; ?>
<link rel="stylesheet" href="<?= defined('URL_BASE') ? URL_BASE : '' ?>/assets/css/form-styles.css">
<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Editar Projeto</h1>
</div>
<form action="/projetos/cadastrar/opcoes" method="POST">
    <div class="form-container">
        <div id="mvc-s1">
        <div class="card">
        <div class="card-title"><span class="step-badge">1</span> Editar Projeto</div>
        
        <div class="field-row">
            <div class="field">
            <label>Nome do Projeto <span class="tip">Sem espaços</span></label>
            <input value="<?= $nome ?? '' ?>" type="text" name="nome" id="nome" placeholder="sistema_oficina">
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
</main>
</div>

    <script src="/assets/js/mvcLoad.js"></script>
</body>
</html>

