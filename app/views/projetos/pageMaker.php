<div id="pageMaker">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-pm.css">
    <div class="pm-etapa-wrapper">
        <div class="pm-etapa">
            <h3>Arquivo e cabeçalho</h3>
            <div class="pm-grade-formulario">
                <div class="pm-campo">
                    <label>NOME DO ARQUIVO HTML <span class="pm-etiqueta">Sem espaços</span></label>
                    <input type="text" id="nomeArquivo">
                </div>

                <div class="pm-campo">
                    <label>TEXTO DO CABEÇALHO</label>
                    <input type="text" name="textoCabecalho" id="textoCabecalho" value="Meu Projeto" placeholder="Meu Projeto">
                </div>

                <div class="pm-campo">
                    <label>TÍTULO DA ABA</label>
                    <input type="text" name="tituloAba" id="tituloAba" value="Meu Projeto" placeholder="Meu Projeto">
                </div>

                <div class="pm-campo">
                    <label>COR DE FUNDO</label>
                    <input type="color" name="corFundo" id="corFundo" value="#ffffff">
                </div>

                <div class="pm-campo">
                    <label>COR DA FONTE</label>
                    <input type="color" name="corFonte" id="corFonte" value="#000000">
                </div>

                <div class="pm-campo pm-campo-completo">
                    <label>TAMANHO DA FONTE</label>
                    <select id="tamanhoFonte">
                        <option value="small">Pequeno</option>
                        <option value="medium" selected>Médio</option>
                        <option value="large">Grande</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
            </div>
            <!-- If outro selected abrir campo para inserir tamanho customizado -->
        </div>


        <div class="pm-cartao">
            <h3>Links de navegação</h3>
            <label class="pm-formulario-opcoes"><input type="checkbox" value=""> Página sem navegação</label>
            <!-- IF página sem navegação class da div abaixo= hidden -->
            <div class="pm-grade-formulario pm-links-list">
                <div class="pm-campo">
                    <label>COR DOS LINKS</label>
                    <input type="color" name="corLinks" id="corLinks" value="#0000ff">
                </div>
                <div class="pm-campo">
                    <label>ESTILO</label>
                    <label><input type="radio" name="opcao" value="1" checked> Opção Um</label>
                    <label><input type="radio" name="opcao" value="2"> Opção Dois</label>
                </div>
                <div class="pm-campo">
                    <label>LINK</label>
                    <input type="text" name="link" id="link" value="" placeholder="https://exemplo.com">
                </div>

                <button class="pm-botao">+ Adicionar link</button>
                <!-- <button class="remove-btn">ESSE BOTAO É PRA REMOVER O ÚLTIMO LINK GERADO</button> -->
            </div>
        </div>

        <div class="pm-cartao">
            <h3>Conteúdo principal</h3>
            <div class="pm-grade-formulario pm-image-controls">
                <div class="pm-campo pm-campo-completo">
                    <label>TEXTO DA PÁGINA</label>
                    <input type="text" name="textoPagina" id="textoPagina" value="" placeholder="Digite o conteúdo da página">
                </div>
                <div class="pm-campo">
                    <label>NOME DA IMAGEM</label>
                    <input type="text" name="nomeImagem" id="nomeImagem" value="" placeholder="Digite o nome da imagem">
                </div>
                <div class="pm-campo">
                    <label>URL DA IMAGEM</label>
                    <input type="text" name="urlImagem" id="urlImagem" value="">
                </div>
                <div class="pm-campo">
                    <label>UPLOAD</label>
                    <input type="file" id="fileImagem">
                </div>
                <div class="pm-campo">
                    <label>LARGURA EM PIXELS</label>
                    <input type="number" name="larguraImagem" id="larguraImagem" value="" placeholder="Digite a largura em pixels">
                </div>
                <div class="pm-campo">
                    <label>ALT DA IMAGEM</label>
                    <input type="text" name="altImagem" id="altImagem" value="">
                </div>
                <div class="pm-campo">
                    <label>ALT TEXT (nome que aparecerá caso a imagem não carregue)</label>
                    <input type="text" name="altTextImagem" id="altTextImagem" value="">
                </div>
            </div>
        </div>

        <div class="pm-cartao">
            <h3>Elementos extras</h3>
            <label class="pm-formulario-opcoes"><input type="checkbox" value=""> Incluir lista de itens (ul)</label>
            <label class="pm-formulario-opcoes"><input type="checkbox" value=""> Incluir tabela HTML simples</label>
            <label class="pm-formulario-opcoes"><input type="checkbox" value=""> Incluir rodapé com texto</label>
        </div>

        <div class="pm-cartao">
            <h3>Prévia e Exportar</h3>
<<<<<<< Updated upstream
            <button class="pm-botao">Visualizar prévia</button>
            <button class="pm-botao pm-botao-secundario">Gerar HTML + CSS</button>
=======
            <label>ORDEM DOS ELEMENTOS</label>
            <select id="ordemElementos">
                <option value="texto-imagem-lista-tabela">Texto, imagem, lista, tabela</option>
                <option value="imagem-texto-lista-tabela">Imagem, texto, lista, tabela</option>
                <option value="texto-lista-imagem-tabela">Texto, lista, imagem, tabela</option>
            </select>
            <div class="pm-controles-tela" aria-label="Tamanho da prévia">
                <button type="button" class="pm-botao pm-tela-ativa" data-largura="100%" aria-pressed="true">Desktop</button>
                <button type="button" class="pm-botao" data-largura="768px" aria-pressed="false">Tablet</button>
                <button type="button" class="pm-botao" data-largura="320px" aria-pressed="false">Celular</button>
            </div>
            <div class="pm-grade-formulario">
                <div class="pm-campo">
                    <label for="maxWidthDesktop">LARGURA MÁXIMA NO DESKTOP (px)</label>
                    <input type="number" id="maxWidthDesktop" min="320" max="2400" step="10" value="1200">
                </div>
                <div class="pm-campo">
                    <label for="maxWidthTablet">LARGURA MÁXIMA NO TABLET (px)</label>
                    <input type="number" id="maxWidthTablet" min="320" max="1024" step="10" value="768">
                </div>
                <div class="pm-campo">
                    <label for="maxWidthCelular">LARGURA MÁXIMA NO CELULAR (px)</label>
                    <input type="number" id="maxWidthCelular" min="240" max="600" step="10" value="480">
                </div>
            </div>
            <button type="button" class="pm-botao" id="visualizarPrevia">Visualizar prévia</button>
            <div class="pm-controles-download" aria-label="Exportar a página">
                <button type="button" class="pm-botao pm-botao-secundario" id="gerarZip">Gerar ZIP (.zip)</button>
            </div>
            <p id="pmStatus" class="pm-status-historico" role="status" aria-live="polite"></p>
            <div class="pm-saida">
                <label for="codeHTML">HTML gerado</label>
                <textarea id="codeHTML" readonly></textarea>
                <label for="codeCSS">CSS gerado</label>
                <textarea id="codeCSS" readonly></textarea>
                <iframe id="pagina" title="Prévia da página gerada"></iframe>
            </div>
>>>>>>> Stashed changes
        </div>
    </div>

</div>
</div>



</main>
</div>
</div>
</body>

</html>