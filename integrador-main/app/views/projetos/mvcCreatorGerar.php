<div id="mvcCreator">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-mvc.css">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    

    <div class="mvc-etapa-wrapper">
        <div class="mvc-etapa-cabecalho">
            <span class="mvc-etapa-selo">5</span>
            <h2>Gerar Sistema</h2>
            
        </div>

        <div class="mvc-etapa">
    <p class="mvc-subtitulo">Clique no botão abaixo para gerar o código MVC e baixar o arquivo .ZIP completo:</p>
    <div class="mvc-acoes">
        <button type="button" id="btn-gerar-final" onclick="executarGeracaoMvc();" class="mvc-etapa-botao" style="font-size: 16px; padding: 12px 24px;">🚀 Gerar Todo o Sistema (.ZIP)</button>
        <a href="?step=estrutura" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>
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