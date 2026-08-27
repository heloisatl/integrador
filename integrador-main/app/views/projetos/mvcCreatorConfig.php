<div id="mvcCreator">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-mvc.css">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    

    <div class="mvc-etapa-wrapper">
        <div class="mvc-etapa-cabecalho">
            <span class="mvc-etapa-selo">1</span>
            <h2>Configurar Projeto</h2>
            
        </div>

            <div class="mvc-etapa">
        <div class="mvc-aviso-info"><span>As credenciais foram pré-carregadas das <strong>Configurações Globais</strong>. Informe o nome do projeto e selecione o banco de dados.</span></div>

        <div class="mvc-grade-formulario">

            <div class="mvc-campo">
                <label for="nomeProjeto">Nome do Projeto</label>
                <input type="text" name="nomeProjeto" id="nomeProjeto" placeholder="Insira aqui o nome do seu projeto">
            </div>

            <div class="mvc-campo">
                <label for="banco">Banco de Dados</label>
                <div class="mvc-linha-banco">
                    <select disabled name="banco" id="banco"></select>
                    <div class="mvc-botoes-banco">
                        <button type="button" onclick="carregarBanco();" disabled class="mvc-etapa-botao mvc-etapa-botao-secundario mvc-btn-icon" title="Atualizar"><span class="material-symbols-outlined">refresh</span></button>
                        <button type="button" onclick="" class="mvc-etapa-botao">Criar novo banco</button>
                    </div>
                </div>
            </div>

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