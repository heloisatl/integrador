<div class="mvc-etapa">
    <div class="mvc-aviso-info"><span>As credenciais foram pré-carregadas das <strong>Configurações Globais</strong>. Informe o nome do projeto e selecione o banco de dados.</span></div>

    <div class="mvc-grade-formulario">
        
        <input type="hidden" name="usuario" id="usuario" value="">
        
        <div class="mvc-campo">
            <label for="nomeProjeto">Nome do Projeto</label>
            <input type="text" name="nomeProjeto" id="nomeProjeto" placeholder="Insira aqui o nome do seu projeto">
        </div>

        <div class="mvc-campo">
            <label for="banco">Banco de Dados</label>
            <div class="mvc-linha-banco">
                <select name="banco" onchange="salvarConfiguracoesSession();" id="banco"><option value="">Nenhum banco encontrado</option></select>
                <div class="mvc-botoes-banco">
                    <button type="button" onclick="carregarBanco();"  class="mvc-etapa-botao mvc-etapa-botao-secundario mvc-btn-icon" title="Atualizar"><span class="material-symbols-outlined">refresh</span></button>
                    <button type="button" onclick="" class="mvc-etapa-botao">Criar novo banco</button>
                </div>
            </div>
        </div>

    </div>
</div>
