<div class="mvc-etapa">
    <p class="mvc-subtitulo">Selecione as camadas que deseja gerar:</p>
    <form class="mvc-formulario-opcoes">
        <label><input value="model" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Model (Models PSR-4)</label>
        <label><input value="controller" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Controller (Controllers RESTful)</label>
        <label><input value="repository" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Repositório (DAO / PDO ConnectionFactory)</label>
        <label><input value="views" oninput="salvarSessionCamadas()" class="mvc-option" type="checkbox" checked > Camada Views (Listagem, Cadastro e Edição)</label>
    </form>
    <div class="mvc-acoes">
        <a href="?step=estrutura" class="mvc-etapa-botao">Ver Estrutura de Arquivos →</a>
        <a href="?step=tabelas" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>
    </div>
</div>
