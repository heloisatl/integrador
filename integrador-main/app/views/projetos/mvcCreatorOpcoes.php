<div id="mvcCreator">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-mvc.css">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />



    <div class="mvc-etapa-wrapper">
        <div class="mvc-etapa-cabecalho">
            <span class="mvc-etapa-selo">3</span>
            <h2>Opções de Geração</h2>
            
        </div>

        <div class="mvc-etapa">
    <p class="mvc-subtitulo">Selecione as camadas que deseja gerar:</p>
    <form class="mvc-formulario-opcoes">
        <label><input type="checkbox" checked disabled> Camada Model (Models PSR-4)</label>
        <label><input type="checkbox" checked disabled> Camada Controller (Controllers RESTful)</label>
        <label><input type="checkbox" checked disabled> Camada Repositório (DAO / PDO ConnectionFactory)</label>
        <label><input type="checkbox" checked disabled> Camada Views (Listagem, Cadastro e Edição)</label>
    </form>
    <div class="mvc-acoes">
        <a href="?step=estrutura" class="mvc-etapa-botao">Ver Estrutura de Arquivos →</a>
        <a href="?step=tabelas" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>
    </div>
</div>
    </div>

    </main>
    <script src="<?= URL_BASE ?>/assets/js/mvcLoad.js"></script>
    <script src="<?= URL_BASE ?>/assets/js/desabilitado.js"></script>
</div>
</div>
</body>

</html>