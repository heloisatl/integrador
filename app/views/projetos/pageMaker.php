<div id="pageMaker">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-pm.css">
    <div class="">
        <div class="mvc-etapa">
            <h3>Arquivo e cabeçalho</h3>
            <label>NOME DO ARQUIVO HTML <span class="">Sem espaços</span></label>
            <input type="text" id="nomeArquivo">
            <div class="">

                <label>TEXTO DO CABEÇALHO</label>
                <input type="text" name="textoCabecalho" id="textoCabecalho" value="Meu Projeto" placeholder="Meu Projeto">
            </div>
            <label>TÍTULO DA ABA</label>
            <input type="text" name="tituloAba" id="tituloAba" value="Meu Projeto" placeholder="Meu Projeto">
            <label>COR DE FUNDO</label>
            <input type="color" name="corFundo" id="corFundo" value="#ffffff">
            <label>COR DA FONTE</label>
            <input type="color" name="corFonte" id="corFonte" value="#000000">
            <label>TAMANHO DA FONTE</label>
            <select></select>
            <option value="small">Pequeno</option>
            <option value="medium" selected>Médio</option>
            <option value="large">Grande</option>
            <option value="outro">Outro</option>
            </select>
            <!-- If outro selected abrir campo para inserir tamanho customizado -->
        </div>


        <div class="pm-cartao">
            <h3>Links de navegação</h3>
            <input type="checkbox" value=""> Página sem navegação
            <!-- IF página sem navegação class da div abaixo= hidden -->
            <div class="">
                <label>COR DOS LINKS</label>
                <input type="color" name="corLinks" id="corLinks" value="#0000ff">
                <label>ESTILO</label>
                <label>
                    <input type="radio" name="opcao" value="1" checked> Opção Um
                </label>
                <label>
                    <input type="radio" name="opcao" value="2"> Opção Dois
                </label>
                <label>LINK</label>
                <input type="text" name="link" id="link" value="" placeholder="https://exemplo.com">


                <button class="">+ Adicionar link</button>
                <!-- <button class="remove-btn">ESSE BOTAO É PRA REMOVER O ÚLTIMO LINK GERADO</button> -->
            </div>

            <div class="pm-cartao">
                <h3>Conteúdo principal</h3>
                <label>TEXTO DA PÁGINA</label>
                <input type="text" name="textoPagina" id="textoPagina" value="" placeholder="Digite o conteúdo da página">
                <label>NOME DA IMAGEM</label>
                <input type="text" name="nomeImagem" id="nomeImagem" value="" placeholder="Digite o nome da imagem">
                <label>URL DA IMAGEM</label>
                <input type="text" name="urlImagem" id="urlImagem" value="">
                <input type="file">
                <label>LARGURA EM PIXELS</label>
                <input type="number" name="larguraImagem" id="larguraImagem" value="" placeholder="Digite a largura em pixels">
                <label>ALT DA IMAGEM</label>
                <input type="text" name="altImagem" id="altImagem" value="">
                <label>ALT TEXT (nome que aparecerá caso a imagem não carregue)</label>
                <input type="text" name="altTextImagem" id="altTextImagem" value="">
            </div>

            <div class="pm-cartao">
                <h3>Elementos extras</h3>
                <input type="checkbox" value=""> Incluir lista de itens (ul)

                <input type="checkbox" value=""> Incluir tabela HTML simples

                <input type="checkbox" value=""> Incluir rodapé com texto

            </div>


            <div class="pm-cartao">
                <h3>Prévia e Exportar</h3>
                <button class="">Visualizar prévia</button>
                <button class="">Gerar HTML + CSS</button>
            </div>
        </div>

    </div>
</div>



</main>
</div>
</div>
</body>

</html>