<div class="mvc-etapa">
    <div class="mvc-aviso-info"><span>As credenciais foram pré-carregadas das <strong>Configurações Globais</strong>. Informe o nome do projeto e selecione o banco de dados.</span></div>

    <div class="mvc-grade-formulario">
        
        <input type="hidden" name="usuario" id="usuario" value="">
        
        <div class="mvc-campo">
            <label for="nomeProjeto">Nome do Projeto</label>
            <input type="text" name="nomeProjeto" oninput="salvarConfiguracoesSession()" id="nomeProjeto" placeholder="Insira aqui o nome do seu projeto">
        </div>

        <div class="mvc-campo">
            <label for="banco">Banco de Dados</label>
            <div class="mvc-linha-banco">
                <select name="banco"  id="banco"><option value="">Nenhum banco encontrado</option></select>
                <div class="mvc-botoes-banco">
                    <button type="button" onclick="carregarTabelas();" class="mvc-etapa-botao">Próxima Etapa →</button>
                    <button type="button" onclick="window.location.href='<?= URL_BASE ?>/projetos/phpmeuamigo'" class="mvc-etapa-botao">Criar novo banco</button>
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
