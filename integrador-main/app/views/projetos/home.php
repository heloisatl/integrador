    <div id="home">

        <?php
        include_once(__DIR__ . "/../include/head.php");
        include_once(__DIR__ . "/../include/navigation.php");

        $nomeDev = 'Desenvolvedor';
        if (isset($_SESSION['usuario_logado']) && method_exists($_SESSION['usuario_logado'], 'getNome')) {
            $nomeDev = $_SESSION['usuario_logado']->getNome();
        }
        ?>

        <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/home.css">

        <section class="pagina-inicial">
            <header class="cabecalho-inicial">
                <p class="etiqueta-inicial">Plataforma de desenvolvimento</p>
                <h2>DevStudio</h2>
                <p class="subtitulo-inicial">Plataforma integrada: gere sistemas PHP MVC e crie páginas HTML visuais.</p>
            </header>

            <section class="secao-metricas" aria-labelledby="titulo-metricas">
                <div class="cabecalho-secao">
                    <p class="etiqueta-inicial">Visão geral</p>
                    <h3 id="titulo-metricas">Tudo pronto para começar</h3>
                </div>

                <div class="grade-metricas">
                    <div class="cartao-metrica">
                        <strong>2</strong>
                        <span>Ferramentas integradas</span>
                    </div>
                    <div class="cartao-metrica">
                        <strong>5</strong>
                        <span>Camadas MVC geradas</span>
                    </div>
                    <div class="cartao-metrica">
                        <strong>100%</strong>
                        <span>Código pronto para uso</span>
                    </div>
                </div>
            </section>

            <section class="secao-ferramentas" aria-labelledby="titulo-ferramentas">
                <div class="cabecalho-secao">
                    <p class="etiqueta-inicial">Ferramentas</p>
                    <h3 id="titulo-ferramentas">Escolha por onde começar</h3>
                </div>

                <div class="grade-ferramentas">
                    <article class="cartao-ferramenta">
                        <span class="icone-ferramenta icone-mvc" aria-hidden="true"><i class="bi bi-braces"></i></span>
                        <h4>EasyMVC — Gerador PHP</h4>
                        <p>Conecte ao banco MySQL, detecte tabelas automaticamente e gere um sistema CRUD completo com Model, View, Controller e DAO.</p>
                        <a class="botao-ferramenta botao-mvc" href="<?= URL_BASE ?>/projetos/mvc-creator">Abrir <span aria-hidden="true">→</span></a>
                    </article>

                    <article class="cartao-ferramenta">
                        <span class="icone-ferramenta icone-pagemaker" aria-hidden="true"><i class="bi bi-file-earmark-code"></i></span>
                        <h4>PageMaker — Criador HTML</h4>
                        <p>Configure cabeçalho, links, conteúdo e imagens visualmente. Gere HTML e CSS prontos para download com prévia em tempo real.</p>
                        <a class="botao-ferramenta botao-pagemaker" href="<?= URL_BASE ?>/projetos/pagemaker">Abrir <span aria-hidden="true">→</span></a>
                    </article>

                    <article class="cartao-ferramenta">
                        <span class="icone-ferramenta icone-saida" aria-hidden="true"><i class="bi bi-box-arrow-up-right"></i></span>
                        <h4>Saída Unificada</h4>
                        <p>Todo o código gerado em ambas ferramentas aparece na aba &quot;Saída Gerada&quot;. Copie ou baixe individualmente cada arquivo.</p>
                        <a class="botao-ferramenta botao-saida" href="<?= URL_BASE ?>/projetos/saida">Ver Saída <span aria-hidden="true">→</span></a>
                    </article>
                </div>
            </section>
        </section>
        </main>
    </div>
    </body>

    </html>