<div id="mvcCreator">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-mvc.css">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />


    <div class="mvc-etapa-wrapper">
        <div class="mvc-etapa-cabecalho">
            <span class="mvc-etapa-selo">4</span>
            <h2>Estrutura de Arquivos</h2>
            <span class="mvc-etapa-selo-novo">VISUALIZAÇÃO</span>
        </div>

            <div class="mvc-etapa">
        <p class="mvc-subtitulo">Árvore de diretórios gerada automaticamente:</p>

        <div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 14px; margin: 15px 0; overflow-x: auto; white-space: nowrap;">
            📂 app/<br>
            &nbsp;&nbsp;├── 📂 controllers/<br>
            &nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 📄 [NomeTabela]Controller.php<br>
            &nbsp;&nbsp;├── 📂 models/<br>
            &nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 📄 [NomeTabela].php<br>
            &nbsp;&nbsp;├── 📂 repositories/<br>
            &nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 📄 [NomeTabela]Repository.php<br>
            &nbsp;&nbsp;└── 📂 views/<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── 📂 [nometabela]s/<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📄 index.php<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📄 create.php<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── 📄 edit.php<br>
        </div>

        <div class="mvc-acoes">
            <a href="?step=gerar" class="mvc-etapa-botao">Avançar para Geração →</a>
            <a href="?step=opcoes" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>
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