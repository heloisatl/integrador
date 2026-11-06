<?php
$isVisitante = empty($_SESSION['usuario_logado']);
$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
?>



<style>
    .btn-toggle-password {
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 18px;
        padding: 4px;
        transition: color 0.2s ease;
    }
</style>
<div class="mvc-etapa">
    <?php if ($isVisitante): ?>
        <div class="mvc-aviso-info">
            <span><strong>Modo Visitante:</strong> Os bancos de dados abaixo são lidos diretamente da sua instância MySQL local (<code><?= htmlspecialchars($hostname) ?>:3306</code>). Nenhum dado será salvo nos servidores do DevStudio.</span>
        </div>
    <?php else: ?>
        <div class="mvc-aviso-info">
            <span>As credenciais foram pré-carregadas das <strong>Configurações Globais</strong>. Informe o nome do projeto e selecione o banco de dados.</span>
        </div>
    <?php endif; ?>

    <div class="mvc-grade-formulario">
        
        
        
        <div class="mvc-campo">
            <label for="nomeProjeto">Nome do Projeto</label>
            <input type="text" name="nomeProjeto" oninput="salvarConfiguracoesSession(false)" id="nomeProjeto" placeholder="Insira aqui o nome do seu projeto">
        </div>

          <?php if($isVisitante): ?>
    
            
            <div class="mvc-campo">
                <label for="usuario">Usuário (`usuario_banco`)</label>
                <input type="text" oninput="salvarConfiguracoesSession(false)" name="usuario" id="usuario" placeholder="root">
            </div>
    

            <div class="mvc-campo" >
                <label for="senha">Senha (`senha_banco`)</label>
                <div style="display:flex;flex-direction:row;">
                    <input type="password" oninput="salvarConfiguracoesSession(false)"  onblur="carregarBanco()" id="senha" name="senha" placeholder="••••••••">
                    <button type="button" id="toggleSenha" class="btn-toggle-password" title="Mostrar/ocultar senha">
                        <i class="bi bi-eye" id="iconeOlho"></i>
                    </button>
                </div>
            </div>
    

            <div class="mvc-campo">
                <label for="servidor">Host (`host`)</label>
                <input type="text" oninput="salvarConfiguracoesSession(false)" id="servidor" name="servidor" value="<?= $hostname  ?>" placeholder="localhost">
            </div>
    

            <div class="mvc-campo">
                <label for="porta">Porta (`porta`)</label>
                <input type="text" oninput="salvarConfiguracoesSession(false)" id="porta" name="porta" value="3306" placeholder="3306">
            </div>

            <?php endif; ?>

        <div class="mvc-campo">
            <label for="banco">Banco de Dados</label>
            <div class="mvc-linha-banco">
                <select name="banco" id="banco"><option value="">Nenhum banco encontrado</option></select>
                <div class="mvc-botoes-banco">
                    <button type="button" onclick="carregarTabelas();" class="mvc-etapa-botao">Próxima Etapa →</button>
                    <?php if ($isVisitante): ?>
                        <button type="button" onclick="carregarBanco();" class="mvc-etapa-botao mvc-etapa-botao-secundario" title="Recarregar esquemas do MySQL local">↻ Atualizar Bancos</button>
                    <?php else: ?>
                        <button type="button" onclick="window.location.href='<?= URL_BASE ?>/projetos/phpmeuamigo'" class="mvc-etapa-botao">Criar novo banco</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mvc-campo" style="grid-column: 1 / -1; margin-top: 5px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                <input type="checkbox" id="cfg-comentarios" onchange="salvarConfigComentarios(this.checked)" checked style="width: 18px; height: 18px;">
                <span>Incluir <strong>comentários didáticos automáticos em português (PT-BR)</strong> no código gerado</span>
            </label>
        </div>

    </div>
</div>
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