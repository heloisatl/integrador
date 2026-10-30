<div class="mvc-etapa">
    <p class="mvc-subtitulo">Selecione as camadas e artefatos que deseja gerar:</p>
    <form class="mvc-formulario-opcoes">
        <label><input value="model" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Model (Models PSR-4)</label>
        <label><input value="controller" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Controller (Controllers RESTful)</label>
        <label><input value="repository" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Repositório (DAO / PDO ConnectionFactory)</label>
        <label><input value="service" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Service (Regras de negócio)</label>
        <label><input value="views" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Views (Listagem, Cadastro e Edição)</label>
        <label><input value="config" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Configuração (config.php com a conexão MySQL)</label>
        <label><input value="index" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Ponto de entrada (public/index.php, Router e Autoload)</label>
        <label><input value="database" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Script do banco (database.sql)</label>
        <label><input id="opt-sem-banco-local" oninput="salvarSemBancoLocal()" type="checkbox" > Não tenho banco de dados local ativo (gera sempre config.php e database.sql)</label>
        <label style="margin-top: 8px; padding-top: 10px; border-top: 1px solid var(--border, #333);">
            <input id="opt-comentarios" oninput="salvarComentariosDidaticos()" type="checkbox" checked>
            <strong>Comentários didáticos automáticos em português (PT-BR)</strong>
        </label>
    </form>
    <div class="mvc-acoes">
        <a href="?step=estrutura" class="mvc-etapa-botao">Ver Estrutura de Arquivos →</a>
        <a href="?step=tabelas" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>
    </div>
</div>
