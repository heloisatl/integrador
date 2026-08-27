<?php require_once __DIR__ . '/../include/head.php'; ?>
<link rel="stylesheet" href="<?= defined('URL_BASE') ? URL_BASE : '' ?>/assets/css/form-styles.css">
<?php require_once __DIR__ . '/../include/navigation.php'; ?>

<div class="page-header">
    <h1 class="page-title">Criar Novo Projeto</h1>
</div>
<form action="/projetos/criar" method="POST">
    <div class="form-container">
        <input value="<?= $nome ?? "" ?>" type="hidden" name="nome">
        <input value="<?= $server ?? "" ?>"type="hidden" name="server">
        <input value="<?= $user ?? "" ?>" type="hidden" name="user">
        <input value="<?= $pass ?? "" ?>" type="hidden" name="pass">
        <input value="<?= $banco ?? "" ?>" type="hidden" name="mvc-banco">
        <div id="mvc-s3" style="display: block;">
            <div class="card">
            <div class="card-title"><span class="step-badge"></span> Opções de Geração</div>
            <div class="g2" style="margin-bottom:16px">
                <div>
                <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">O que será gerado</div>
                <div style="display:flex;flex-direction:column;gap:7px">
                    
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                        <input type="checkbox" name="opt-views" id="opt-views" checked value="1" style="width:auto"> Views (formulário + lista)
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                        <input type="checkbox" name="opt-comentarios" id="opt-comentarios" <?= '' ?>checked value="1" style="width:auto"> Comentários didáticos automáticos (PT-BR)
                    </label>
                    
                </div>
                </div>
                
            </div>
            <div class="btn-row">
                <button class="btn btn-primary" >Gerar Projeto</button>
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

