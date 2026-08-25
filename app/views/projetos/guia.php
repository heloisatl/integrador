<div id="guia">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/guia.css">

    <section class="guia-pagina">
        <header class="guia-cabecalho">
            <p class="guia-etiqueta">Guia Rápido</p>
            <h2> tudo o que o DevStudio oferece</h2>
            <p>Use as ferramentas do DevStudio para configurar seu projeto, gerar sistemas PHP, criar páginas visuais e organizar os arquivos produzidos.</p>
        </header>

        <section class="guia-bloco">
            <div class="guia-titulo-bloco"><span class="guia-numero">01</span><div><p class="guia-etiqueta">Primeiros passos</p><h3>Como começar</h3></div></div>
            <div class="guia-passos">
                <article class="guia-passo"><span>1</span><div><h4>Acesse o DevStudio</h4><p>Abra o sistema pela URL da pasta <code>public</code>. A página inicial apresenta os atalhos e o resumo das ferramentas.</p></div></article>
                <article class="guia-passo"><span>2</span><div><h4>Configure seu ambiente</h4><p>Use <strong>Config. Globais</strong> para informar os dados do MySQL e organizar as configurações usadas pelo gerador.</p></div></article>
                <article class="guia-passo"><span>3</span><div><h4>Escolha seu objetivo</h4><p>Gere um sistema PHP pelo <strong>MVC Creator</strong> ou monte uma interface HTML pelo <strong>Page Maker</strong>.</p></div></article>
                <article class="guia-passo"><span>4</span><div><h4>Confira e aproveite</h4><p>Use a prévia, acompanhe o histórico e consulte a saída para copiar ou baixar o código gerado.</p></div></article>
            </div>
        </section>

        <section class="guia-bloco">
            <div class="guia-titulo-bloco"><span class="guia-numero">02</span><div><p class="guia-etiqueta">Funcionalidades</p><h3>Conheça cada área</h3></div></div>
            <div class="guia-camadas">
                <article class="guia-camada"><span class="guia-icone">⌂</span><div><h4>Início</h4><p>Mostra uma visão geral do DevStudio e oferece acesso rápido ao MVC Creator, Page Maker e Configurações Globais.</p><a class="guia-link" href="<?= URL_BASE ?>/projetos">Ir para Início →</a></div></article>
                <article class="guia-camada"><span class="guia-icone">⚙</span><div><h4>Config. Globais</h4><p>Área para definir host, usuário, senha e nome do banco MySQL que serão usados na conexão do projeto.</p><a class="guia-link" href="<?= URL_BASE ?>/projetos/config-global">Abrir configurações →</a></div></article>
                <article class="guia-camada"><span class="guia-icone">MVC</span><div><h4>MVC Creator</h4><p>Gera Controllers, Models, Repositories/DAO e Views CRUD a partir das tabelas do seu banco de dados.</p><a class="guia-link" href="<?= URL_BASE ?>/projetos/mvc-creator">Abrir MVC Creator →</a></div></article>
                <article class="guia-camada"><span class="guia-icone">HTML</span><div><h4>Page Maker</h4><p>Cria páginas HTML e CSS com cabeçalho, navegação, imagens, colunas, cards, formulários, botões e prévia responsiva.</p><a class="guia-link" href="<?= URL_BASE ?>/projetos/pagemaker">Abrir Page Maker →</a></div></article>
                <article class="guia-camada"><span class="guia-icone">OUT</span><div><h4>Saída</h4><p>Centraliza os códigos produzidos pelas ferramentas para visualizar, copiar e baixar cada arquivo gerado.</p><a class="guia-link" href="<?= URL_BASE ?>/projetos/saida">Ver saída →</a></div></article>
                <article class="guia-camada"><span class="guia-icone">HIS</span><div><h4>Histórico</h4><p>Ajuda a acompanhar os projetos e atividades recentes, facilitando a retomada do trabalho.</p><a class="guia-link" href="<?= URL_BASE ?>/projetos/historico">Ver histórico →</a></div></article>
                <article class="guia-camada"><span class="guia-icone">PER</span><div><h4>Perfil e tema</h4><p>No menu Perfil, alterne entre tema claro e escuro. Quando estiver autenticado, também poderá sair da conta.</p></div></article>
            </div>
        </section>

        <section class="guia-bloco">
            <div class="guia-titulo-bloco"><span class="guia-numero">03</span><div><p class="guia-etiqueta">Gerador PHP</p><h3>Como usar o MVC Creator</h3></div></div>
            <ol class="guia-lista-detalhada">
                <li><strong>Configure:</strong> abra as configurações globais e confira os dados do MySQL.</li>
                <li><strong>Conecte:</strong> no MVC Creator, informe o nome do projeto e carregue os bancos disponíveis.</li>
                <li><strong>Detecte tabelas:</strong> escolha o banco e consulte as tabelas encontradas automaticamente.</li>
                <li><strong>Selecione as opções:</strong> o gerador prepara Model, Controller, Repository/DAO e Views de listagem, cadastro e edição.</li>
                <li><strong>Veja a estrutura:</strong> confira as pastas e os arquivos antes de executar a geração.</li>
                <li><strong>Gere e baixe:</strong> o sistema cria o código, monta o arquivo <code>.zip</code> e disponibiliza o download.</li>
            </ol>
            <div class="guia-aviso"><strong>MVC + DAO:</strong> o Model representa os dados; o DAO, chamado de Repository neste projeto, concentra o SQL; o Controller coordena as ações; e a View exibe a interface.</div>
        </section>

        <section class="guia-bloco">
            <div class="guia-titulo-bloco"><span class="guia-numero">04</span><div><p class="guia-etiqueta">Criador visual</p><h3>Como usar o Page Maker</h3></div></div>
            <div class="guia-lista-recursos">
                <p><strong>Conteúdo básico:</strong> defina nome do arquivo, título, cabeçalho, texto, imagens por URL ou upload e links de navegação.</p>
                <p><strong>Layout:</strong> escolha uma, duas ou três colunas, alinhamento, margem, preenchimento e cabeçalho fixo.</p>
                <p><strong>Design:</strong> configure cores, Google Fonts, peso da fonte, altura da linha, gradientes, imagem de fundo, sombras e cantos arredondados.</p>
                <p><strong>Componentes:</strong> inclua listas, tabelas, rodapé, cards, formulário de contato e botões CTA personalizados.</p>
                <p><strong>Prévia e código:</strong> visualize em desktop, tablet ou celular e gere o HTML e CSS finais.</p>
            </div>
            <a class="guia-botao" href="<?= URL_BASE ?>/projetos/pagemaker">Experimentar Page Maker</a>
        </section>

        <section class="guia-bloco">
            <div class="guia-titulo-bloco"><span class="guia-numero">05</span><div><p class="guia-etiqueta">Depois de gerar</p><h3>O que fazer com o resultado</h3></div></div>
            <div class="guia-passos">
                <article class="guia-passo"><span>1</span><div><h4>Revise na Saída</h4><p>Confira os arquivos separados por tipo e verifique se o conteúdo gerado corresponde ao que foi configurado.</p></div></article>
                <article class="guia-passo"><span>2</span><div><h4>Copie ou baixe</h4><p>Use os controles de cada bloco para copiar o código ou salvar os arquivos no computador.</p></div></article>
                <article class="guia-passo"><span>3</span><div><h4>Coloque no servidor</h4><p>Use o código em XAMPP, WAMP, Docker ou outro ambiente PHP/HTML.</p></div></article>
                <article class="guia-passo"><span>4</span><div><h4>Retome depois</h4><p>Consulte o Histórico para encontrar atividades e continuar seu trabalho com mais organização.</p></div></article>
            </div>
        </section>
    </section>
</main>
</div>
</body>
</html>
