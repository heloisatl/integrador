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
    .field{
        display:flex;
        flex-direction:column;
        gap:5px;
        margin-bottom:12px
    }
    .field label{
        font-size:11px;
        font-weight:600;
        color:var(--muted);
        text-transform:uppercase;
        letter-spacing:.07em;
        display:flex;
        align-items:center;
        gap:6px
    }
    .field label .tip{
        font-size:11px;
        background:var(--border2);
        color:var(--muted);
        border-radius:4px;
        padding:1px 6px;
        font-weight:400;
        text-transform:none;
        letter-spacing:0;
        cursor:help
    }
    .field input,.field select,.field textarea{
        background:var(--bg);
        border:1px solid var(--border2);
        border-radius:8px;
        padding:9px 12px;
        color:var(--text);
        font-size:13px;
        outline:none;
        transition:border .18s;
        width:100%
    }
    .field input:focus,.field select:focus,.field textarea:focus{
        border-color:var(--accent);
        box-shadow:0 0 0 3px var(--accent-glow)
    }
    .field select option{
        background:var(--bg)
    }
    .field textarea{
        min-height:80px;
        resize:vertical;
        line-height:1.5
    }
    .field-row{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:12px
    }
    .field-row3{
        display:grid;
        grid-template-columns:1fr 1fr 1fr;
        gap:12px
    }
</style>

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

