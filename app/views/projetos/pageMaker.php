<div id="pageMaker">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-pm.css">
    <script src="<?= URL_BASE ?>/assets/js/funcoes-pm.js" defer></script>
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
            <h3>Layout e espaçamento</h3>
            <div class="pm-grade-formulario">
                <div class="pm-campo">
                    <label>DIVISÃO DAS COLUNAS</label>
                    <select id="tipoLayout">
                        <option value="1">Uma coluna</option>
                        <option value="2">Duas colunas (50/50)</option>
                        <option value="3">Três colunas (33/33/33)</option>
                    </select>
                </div>
                <div class="pm-campo">
                    <label>ALINHAMENTO HORIZONTAL</label>
                    <select id="alinhamentoHorizontal">
                        <option value="flex-start">Esquerda</option>
                        <option value="center">Centro</option>
                        <option value="flex-end">Direita</option>
                        <option value="space-between">Espaço entre itens</option>
                    </select>
                </div>
                <div class="pm-campo">
                    <label>ALINHAMENTO VERTICAL</label>
                    <select id="alinhamentoVertical">
                        <option value="flex-start">Topo</option>
                        <option value="center">Centro</option>
                        <option value="flex-end">Base</option>
                    </select>
                </div>
                <div class="pm-campo">
                    <label>MARGEM DA PÁGINA: <output id="valorMargem">24px</output></label>
                    <input type="range" id="margemPagina" min="0" max="80" value="24">
                </div>
                <div class="pm-campo">
                    <label>PREENCHIMENTO: <output id="valorPreenchimento">24px</output></label>
                    <input type="range" id="preenchimentoPagina" min="0" max="80" value="24">
                </div>
                <label class="pm-formulario-opcoes pm-campo-completo">
                    <input type="checkbox" id="cabecalhoFixo"> Fixar cabeçalho durante a rolagem
                </label>
            </div>
        </div>

        <div class="pm-cartao">
            <h3>Design avançado</h3>
            <div class="pm-grade-formulario">
                <div class="pm-campo pm-campo-completo">
                    <label>URL DA FONTE GOOGLE FONTS</label>
                    <input type="url" id="urlFonte" placeholder="https://fonts.googleapis.com/css2?family=Roboto">
                </div>
                <div class="pm-campo">
                    <label>PESO DA FONTE</label>
                    <select id="pesoFonte">
                        <option value="400">Normal</option>
                        <option value="500">Médio</option>
                        <option value="700">Negrito</option>
                    </select>
                </div>
                <div class="pm-campo">
                    <label>ALTURA DA LINHA: <output id="valorAlturaLinha">1.5</output></label>
                    <input type="range" id="alturaLinha" min="1" max="2.4" step="0.1" value="1.5">
                </div>
                <div class="pm-campo">
                    <label>RAIO DAS BORDAS: <output id="valorRaioBorda">8px</output></label>
                    <input type="range" id="raioBorda" min="0" max="32" value="8">
                </div>
                <div class="pm-campo">
                    <label>SOMBRA</label>
                    <select id="tipoSombra">
                        <option value="none">Sem sombra</option>
                        <option value="leve">Leve</option>
                        <option value="forte">Forte</option>
                    </select>
                </div>
                <div class="pm-campo pm-campo-completo">
                    <label>IMAGEM DE FUNDO</label>
                    <input type="url" id="imagemFundo" placeholder="https://exemplo.com/fundo.jpg">
                </div>
                <div class="pm-campo">
                    <label>GRADIENTE INICIAL</label>
                    <input type="color" id="corGradienteInicial" value="#ffffff">
                </div>
                <div class="pm-campo">
                    <label>GRADIENTE FINAL</label>
                    <input type="color" id="corGradienteFinal" value="#e8e8ff">
                </div>
                <div class="pm-campo">
                    <label>OPACIDADE DO FUNDO: <output id="valorOpacidade">100%</output></label>
                    <input type="range" id="opacidadeFundo" min="0" max="100" value="100">
                </div>
            </div>
        </div>


        <div class="pm-cartao">
            <h3>Links de navegação</h3>
            <label class="pm-formulario-opcoes"><input type="checkbox" id="semNavegacao"> Página sem navegação</label>
            <div class="pm-grade-formulario pm-links-list">
                <div class="pm-campo">
                    <label>COR DOS LINKS</label>
                    <input type="color" name="corLinks" id="corLinks" value="#0000ff">
                </div>
                <div class="pm-campo">
                    <label>ESTILO</label>
                    <label><input type="radio" name="estiloLinks" value="1" checked> Sublinhado</label>
                    <label><input type="radio" name="estiloLinks" value="0"> Sem sublinhado</label>
                </div>
                <div class="pm-grupo-link">
                    <input type="text" name="links" id="link" value="" placeholder="Nome do link">
                    <input type="url" name="href" id="href" value="" placeholder="https://exemplo.com">
                    <span></span>
                </div>

                <button type="button" class="pm-botao" id="adicionarLink">+ Adicionar link</button>
            </div>
        </div>

        <div class="pm-cartao">
            <h3>Conteúdo principal</h3>
            <div class="pm-grade-formulario pm-image-controls">
                <div class="pm-campo pm-campo-completo">
                    <label>TEXTO DA PÁGINA</label>
                    <textarea name="textoPagina" id="textoPagina" placeholder="Digite o conteúdo da página"></textarea>
                </div>
                <div class="pm-campo">
                    <label>NOME DA IMAGEM</label>
                    <input type="text" name="nomeImagem" id="nomeImagem" value="" placeholder="Digite o nome da imagem">
                </div>
                <div class="pm-campo">
                    <label>URL DA IMAGEM</label>
                    <input type="url" name="urlImagem" id="urlImagem" value="" placeholder="https://exemplo.com/imagem.jpg">
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
            <label class="pm-formulario-opcoes"><input type="checkbox" id="incluirLista"> Incluir lista de itens (ul)</label>
            <label class="pm-formulario-opcoes"><input type="checkbox" id="incluirTabela"> Incluir tabela HTML simples</label>
            <label class="pm-formulario-opcoes"><input type="checkbox" id="incluirRodape"> Incluir rodapé com texto</label>
            <label class="pm-formulario-opcoes"><input type="checkbox" id="incluirCard"> Incluir card de conteúdo</label>
            <label class="pm-formulario-opcoes"><input type="checkbox" id="incluirFormulario"> Incluir formulário de contato</label>
        </div>

        <div class="pm-cartao">
            <h3>Botão de destaque</h3>
            <div class="pm-grade-formulario">
                <div class="pm-campo">
                    <label>TEXTO DO BOTÃO</label>
                    <input type="text" id="textoBotao" value="Saiba mais">
                </div>
                <div class="pm-campo">
                    <label>LINK DO BOTÃO</label>
                    <input type="url" id="linkBotao" value="https://exemplo.com">
                </div>
                <div class="pm-campo">
                    <label>COR DO BOTÃO</label>
                    <input type="color" id="corBotao" value="#5b6af0">
                </div>
                <div class="pm-campo">
                    <label>COR NO HOVER</label>
                    <input type="color" id="corBotaoHover" value="#4a59df">
                </div>
            </div>
        </div>

        <div class="pm-cartao">
            <h3>Prévia e Exportar</h3>
            <label>ORDEM DOS ELEMENTOS</label>
            <select id="ordemElementos">
                <option value="texto-imagem-lista-tabela">Texto, imagem, lista, tabela</option>
                <option value="imagem-texto-lista-tabela">Imagem, texto, lista, tabela</option>
                <option value="texto-lista-imagem-tabela">Texto, lista, imagem, tabela</option>
            </select>
            <div class="pm-controles-tela" aria-label="Tamanho da prévia">
                <button type="button" class="pm-botao pm-tela-ativa" data-largura="100%">Desktop</button>
                <button type="button" class="pm-botao" data-largura="768px">Tablet</button>
                <button type="button" class="pm-botao" data-largura="320px">Celular</button>
            </div>
            <button type="button" class="pm-botao" id="visualizarPrevia">Visualizar prévia</button>
            <button type="button" class="pm-botao pm-botao-secundario" id="gerarCodigo">Gerar HTML + CSS</button>
            <div class="pm-saida">
                <label for="codeHTML">HTML gerado</label>
                <textarea id="codeHTML" readonly></textarea>
                <label for="codeCSS">CSS gerado</label>
                <textarea id="codeCSS" readonly></textarea>
                <iframe id="pagina" title="Prévia da página gerada"></iframe>
            </div>
        </div>
    </div>

</div>
</div>



<?php require_once __DIR__ . '/../include/footer.php'; ?>
</main>
</div>
</div>
</body>

</html>