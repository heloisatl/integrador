(() => {
    'use strict';

    let navegacaoAtiva = true;
    let imagemSelecionada = '';
    let alteracoesPendentes = false;
    let navegacaoConfirmada = false;

    const buscar = (id) => document.getElementById(id);
    const ler = (id, padrao = '') => buscar(id)?.value.trim() || padrao;
    const marcado = (id) => buscar(id)?.checked === true;
    const lerLarguraMaxima = (id, padrao, minimo, maximo) => {
        const valor = Number.parseInt(ler(id, String(padrao)), 10);
        return Number.isFinite(valor) ? Math.min(maximo, Math.max(minimo, valor)) : padrao;
    };
    const escaparHtml = (valor) => String(valor).replace(/[&<>"']/g, (caractere) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[caractere]);

    function obterNomeBaseArquivo() {
        const nome = ler('nomeArquivo', 'pagina')
            .replace(/\.(html?|css)$/i, '')
            .replace(/[^a-zA-Z0-9_-]+/g, '_')
            .replace(/^_+|_+$/g, '');
        return nome || 'pagina';
    }

    function criarCampoLink() {
        const grupo = document.createElement('div');
        grupo.className = 'pm-grupo-link';
        grupo.innerHTML = `
            <input type="text" name="links" placeholder="Nome do link">
            <input type="url" name="href" placeholder="https://exemplo.com">
            <button type="button" class="pm-botao pm-botao-pequeno">-</button>`;
        grupo.querySelector('button').addEventListener('click', () => grupo.remove());
        return grupo;
    }

    function adicionarLink() {
        const lista = document.querySelector('.pm-links-list');
        const botao = buscar('adicionarLink');
        if (lista && botao) lista.insertBefore(criarCampoLink(), botao);
    }

    function atualizarNavegacao() {
        const lista = document.querySelector('.pm-links-list');
        const controle = buscar('semNavegacao');
        if (!lista || !controle) return;
        navegacaoAtiva = !controle.checked;
        lista.classList.toggle('pm-oculto', !navegacaoAtiva);
    }

    function obterImagem() {
        const arquivo = buscar('fileImagem');
        return ler('urlImagem') || (arquivo?.files.length ? imagemSelecionada : '');
    }

    function converterCor(cor, opacidade) {
        const valores = cor.replace('#', '').match(/.{2}/g).map((valor) => parseInt(valor, 16));
        return `rgba(${valores.join(', ')}, ${opacidade})`;
    }

    function gerarCss() {
        const tamanhos = { small: '14px', medium: '18px', large: '24px', outro: '18px' };
        const tamanho = tamanhos[ler('tamanhoFonte', 'medium')] || tamanhos.medium;
        const largura = ler('larguraImagem', 'auto');
        const sombra = { none: 'none', leve: '0 4px 14px rgba(0,0,0,.15)', forte: '0 10px 28px rgba(0,0,0,.3)' }[ler('tipoSombra', 'none')];
        const opacidade = Number(ler('opacidadeFundo', '100')) / 100;
        const fundoInicial = converterCor(ler('corGradienteInicial', '#ffffff'), opacidade);
        const fundoFinal = converterCor(ler('corGradienteFinal', '#e8e8ff'), opacidade);
        const imagemFundo = ler('imagemFundo');
        const fundo = imagemFundo
            ? `linear-gradient(${fundoInicial}, ${fundoFinal}), url('${imagemFundo}')`
            : `linear-gradient(${fundoInicial}, ${fundoFinal})`;
        const estilo = document.querySelector('input[name="estiloLinks"]:checked')?.value === '0' ? 'none' : 'underline';
        const maxWidthDesktop = lerLarguraMaxima('maxWidthDesktop', 1200, 320, 2400);
        const maxWidthTablet = lerLarguraMaxima('maxWidthTablet', 768, 320, 1024);
        const maxWidthCelular = lerLarguraMaxima('maxWidthCelular', 480, 240, 600);

        return `${ler('urlFonte') ? `@import url('${ler('urlFonte')}');\n\n` : ''}:root {
    --cor-fundo: ${ler('corFundo', '#ffffff')};
    --cor-fonte: ${ler('corFonte', '#000000')};
    --cor-links: ${ler('corLinks', '#0000ff')};
    --cor-botao: ${ler('corBotao', '#5b6af0')};
    --cor-botao-hover: ${ler('corBotaoHover', '#4a59df')};
}

body {
    background: ${fundo};
    background-color: var(--cor-fundo);
    background-size: cover;
    color: var(--cor-fonte);
    font-family: Arial, sans-serif;
    font-size: ${tamanho};
    font-weight: ${ler('pesoFonte', '400')};
    line-height: ${ler('alturaLinha', '1.5')};
    margin: ${ler('margemPagina', '24')}px;
    padding: ${ler('preenchimentoPagina', '24')}px;
    box-sizing: border-box;
}

.pagina-container {
    box-sizing: border-box;
    margin-inline: auto;
    max-width: ${maxWidthDesktop}px;
    width: 100%;
}

header {
    border-radius: ${ler('raioBorda', '8')}px;
    box-shadow: ${sombra};
    padding: ${ler('preenchimentoPagina', '24')}px;
    ${marcado('cabecalhoFixo') ? 'position: sticky; top: 0; z-index: 2;' : ''}
}

.grade-conteudo {
    align-items: ${ler('alinhamentoVertical', 'flex-start')};
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: ${ler('preenchimentoPagina', '24')}px;
    justify-content: ${ler('alinhamentoHorizontal', 'flex-start')};
}

.grade-conteudo > * {
    flex: 1 1 calc(${100 / Number(ler('tipoLayout', '1'))}% - 24px);
    min-width: 0;
}

nav a {
    color: var(--cor-links);
    margin-right: 16px;
    text-decoration: ${estilo};
}

img, .cartao-conteudo, table, .botao-cta {
    border-radius: ${ler('raioBorda', '8')}px;
    box-shadow: ${sombra};
}

img { max-width: 100%; width: ${largura === 'auto' ? 'auto' : largura + 'px'}; }
.botao-cta { background: var(--cor-botao); color: #fff; display: inline-block; padding: 10px 16px; text-decoration: none; }
.botao-cta:hover { background: var(--cor-botao-hover); }
.formulario-contato { display: grid; gap: 12px; max-width: 520px; }
.formulario-contato input, .formulario-contato textarea { border: 1px solid var(--cor-links); border-radius: ${ler('raioBorda', '8')}px; padding: 10px; }

@media (max-width: 1024px) {
    .pagina-container { max-width: ${maxWidthTablet}px; }
}

@media (max-width: 600px) {
    .pagina-container { max-width: ${maxWidthCelular}px; }
}

@media (max-width: 700px) {
    .grade-conteudo { flex-direction: column; }
    .grade-conteudo > * { flex-basis: auto; width: 100%; }
}`;
    }

    function gerarLinks() {
        if (!navegacaoAtiva) return '';
        return [...document.querySelectorAll('input[name="links"]')].map((link) => {
            const url = link.closest('.pm-grupo-link')?.querySelector('input[name="href"]')?.value.trim() || '';
            return link.value.trim() && url
                ? `<!-- Link clicável: altere o texto entre as tags e o endereço no atributo href. -->
<a href="${escaparHtml(url)}">${escaparHtml(link.value.trim())}</a>`
                : '';
        }).filter(Boolean).join('\n');
    }

    function gerarComponentes() {
        const imagem = obterImagem();
        const descricaoImagem = escaparHtml(ler('altImagem', ler('altTextImagem', ler('nomeImagem', 'Imagem do projeto'))));
        const tituloImagem = escaparHtml(ler('altTextImagem', ler('nomeImagem', '')));
        const imagemHtml = imagem
            ? `<!-- Imagem configurada; alt descreve seu conteúdo e title define o texto auxiliar. -->
<img src="${escaparHtml(imagem)}" alt="${descricaoImagem}"${tituloImagem ? ` title="${tituloImagem}"` : ''}>`
            : '';
        const card = marcado('incluirCard')
            ? `<!-- Card personalizável que reúne imagem, título, texto e botão. -->
<article class="cartao-conteudo">
    ${imagemHtml}
    <!-- Título principal do card. -->
    <h2>${escaparHtml(ler('textoCabecalho', 'Meu Projeto'))}</h2>
    <!-- Texto descritivo do card. -->
    <p>${escaparHtml(ler('textoPagina', 'Conteúdo do card'))}</p>
    <!-- Botão do card: altere o texto e o destino no href. -->
    <a class="botao-cta" href="${escaparHtml(ler('linkBotao', '#'))}">${escaparHtml(ler('textoBotao', 'Saiba mais'))}</a>
</article>`
            : '';
        const formulario = marcado('incluirFormulario')
            ? `<!-- Formulário de contato com campos obrigatórios para nome, e-mail e mensagem. -->
<form class="formulario-contato">
    <!-- Campo para o nome da pessoa. -->
    <input placeholder="Nome" required>
    <!-- Campo validado para o endereço de e-mail. -->
    <input type="email" placeholder="Email" required>
    <!-- Campo para escrever a mensagem. -->
    <textarea placeholder="Mensagem" required></textarea>
    <!-- Botão que envia o formulário. -->
    <button class="botao-cta" type="submit">Enviar</button>
</form>`
            : '';
        const lista = marcado('incluirLista')
            ? '<!-- Lista de itens; cada li representa um item que pode ser editado ou duplicado. -->\n<ul>\n    <li>Item da lista</li>\n</ul>'
            : '';
        const tabela = marcado('incluirTabela')
            ? '<!-- Tabela HTML: cada tr cria uma linha e cada td cria uma célula de dados. -->\n<table border="1">\n    <tr><td>Dados</td></tr>\n</table>'
            : '';
        const rodape = marcado('incluirRodape')
            ? '<!-- Rodapé da página; edite o texto entre as tags footer. -->\n<footer>Gerado pelo DevStudio</footer>'
            : '';
        const ordem = ler('ordemElementos', 'texto-imagem-lista-tabela').split('-');
        const elementos = {
            texto: `<!-- Texto principal da página. -->
<p class="bloco-texto">${escaparHtml(ler('textoPagina', 'Conteúdo da página'))}</p>`,
            imagem: imagemHtml ? `<!-- Bloco que posiciona a imagem inserida por URL ou arquivo. -->
<div class="bloco-imagem">${imagemHtml}</div>` : '',
            lista,
            tabela
        };
        const conteudo = ordem.map((item) => elementos[item] || '').join('\n');
        return { conteudo: `${conteudo}${card}${formulario}`, rodape };
    }

    function gerarHtml() {
        const links = gerarLinks();
        const componentes = gerarComponentes();
        const nomeBase = escaparHtml(obterNomeBaseArquivo());
        return `<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Título exibido na aba do navegador; altere este texto para renomear a página. -->
    <title>${escaparHtml(ler('tituloAba', 'Meu Projeto'))}</title>
    <!-- O CSS controla: cores de fundo e texto; fonte, tamanho, peso e altura de linha; margens e preenchimento. -->
    <!-- Também controla: gradiente, imagem e opacidade do fundo; colunas e alinhamentos; bordas, sombras e cabeçalho fixo. -->
    <!-- Links, imagens, botões e formulário também recebem seus estilos no arquivo CSS abaixo. -->
    <link rel="stylesheet" href="${nomeBase}.css">
</head>
<body>
    <!-- O CSS controla as cores, fontes, fundo, espaçamentos e alinhamentos da página. -->
    <!-- O conteúdo abaixo fica centralizado e respeita os limites de largura de desktop, tablet e celular no CSS. -->
    <div class="pagina-container">
    <!-- Cabeçalho com o texto configurado; o CSS também pode fixá-lo durante a rolagem. -->
    <header>
        <!-- Título principal exibido no cabeçalho. -->
        <h1>${escaparHtml(ler('textoCabecalho', 'Meu Projeto'))}</h1>
    </header>
    ${links ? `<!-- Navegação: cada link configurado abaixo é um elemento clicável com seu próprio destino. -->
    <!-- A cor e o sublinhado dos links são definidos nas opções de navegação do CSS. -->
    <nav>${links}</nav>` : ''}
    <!-- Conteúdo principal; a ordem dos elementos e o número de colunas vêm das opções de layout. -->
    <!-- A largura das imagens e o estilo visual dos componentes são definidos no CSS. -->
    <main class="grade-conteudo">${componentes.conteudo}</main>
    <!-- Botão de destaque: o texto e o endereço são definidos no PageMaker; as cores normal e hover ficam no CSS. -->
    <a class="botao-cta" href="${escaparHtml(ler('linkBotao', '#'))}">${escaparHtml(ler('textoBotao', 'Saiba mais'))}</a>
    ${componentes.rodape}
    </div>
</body>
</html>`;
    }

    function atualizarSaida() {
        buscar('codeCSS').value = gerarCss();
        buscar('codeHTML').value = gerarHtml();
        renderizarPrevia();
    }

    function renderizarPrevia() {
        const iframe = buscar('pagina');
        if (!iframe) return;
        const css = buscar('codeCSS').value.replace(/<\/style/gi, '<\\/style');
        const html = buscar('codeHTML').value
            .replace(/<link rel="stylesheet" href="[^"]+">/, '')
            .replace('</head>', `<style>${css}</style></head>`);
        iframe.srcdoc = html;
    }

    async function gerarZip() {
        atualizarSaida();
        const raiz = buscar('pageMaker');
        const botao = buscar('gerarZip');
        const status = buscar('pmStatus');
        if (!raiz || !botao) return;

        const dados = new FormData();
        dados.append('nome', obterNomeBaseArquivo());
        dados.append('html', buscar('codeHTML').value);
        dados.append('css', buscar('codeCSS').value);
        botao.disabled = true;
        if (status) status.textContent = 'Gerando o ZIP...';

        try {
            const resposta = await fetch(`${raiz.dataset.urlBase}/projetos/pagemaker/baixar-zip`, {
                method: 'POST',
                body: dados
            });
            if (!resposta.ok) {
                const erro = await resposta.json().catch(() => null);
                throw new Error(erro?.mensagem || 'Não foi possível gerar o arquivo ZIP.');
            }

            const arquivoZip = await resposta.blob();
            const url = URL.createObjectURL(arquivoZip);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${obterNomeBaseArquivo()}.zip`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
            if (status) status.textContent = 'ZIP gerado com os arquivos HTML e CSS.';
            alteracoesPendentes = false;
        } catch (erro) {
            if (status) status.textContent = erro.message;
            console.error('Erro ao gerar ZIP do PageMaker:', erro);
        } finally {
            botao.disabled = false;
        }
    }

    function marcarAlteracoesPendentes() {
        alteracoesPendentes = true;
    }

    function atualizarValores() {
        const saidas = {
            margemPagina: ['valorMargem', 'px'],
            preenchimentoPagina: ['valorPreenchimento', 'px'],
            alturaLinha: ['valorAlturaLinha', ''],
            raioBorda: ['valorRaioBorda', 'px'],
            opacidadeFundo: ['valorOpacidade', '%']
        };
        document.querySelectorAll('input[type="range"]').forEach((controle) => {
            const configuracao = saidas[controle.id];
            const saida = configuracao ? buscar(configuracao[0]) : null;
            if (saida) saida.textContent = controle.value + configuracao[1];
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        buscar('adicionarLink')?.addEventListener('click', adicionarLink);
        buscar('semNavegacao')?.addEventListener('change', atualizarNavegacao);
        buscar('visualizarPrevia')?.addEventListener('click', atualizarSaida);
        buscar('gerarZip')?.addEventListener('click', gerarZip);
        document.querySelectorAll('input[type="range"]').forEach((controle) => controle.addEventListener('input', atualizarValores));
        document.querySelectorAll('[data-largura]').forEach((botao) => botao.addEventListener('click', () => {
            const iframe = buscar('pagina');
            if (iframe) {
                iframe.style.width = botao.dataset.largura;
                iframe.style.maxWidth = '100%';
            }
            document.querySelectorAll('[data-largura]').forEach((item) => {
                item.classList.remove('pm-tela-ativa');
                item.setAttribute('aria-pressed', 'false');
            });
            botao.classList.add('pm-tela-ativa');
            botao.setAttribute('aria-pressed', 'true');
            atualizarSaida();
        }));
        buscar('fileImagem')?.addEventListener('change', (evento) => {
            const arquivo = evento.target.files[0];
            if (!arquivo) {
                imagemSelecionada = '';
                return;
            }

            const leitor = new FileReader();
            leitor.addEventListener('load', () => {
                imagemSelecionada = typeof leitor.result === 'string' ? leitor.result : '';
            });
            leitor.readAsDataURL(arquivo);
        });
        const raiz = buscar('pageMaker');
        raiz?.addEventListener('input', marcarAlteracoesPendentes);
        raiz?.addEventListener('change', marcarAlteracoesPendentes);
        raiz?.addEventListener('click', (evento) => {
            if (evento.target.closest('#adicionarLink, .pm-grupo-link button')) {
                marcarAlteracoesPendentes();
            }
        });
        document.addEventListener('click', (evento) => {
            if (!alteracoesPendentes) return;
            const link = evento.target.closest('a[href]');
            if (!link || link.hasAttribute('download') || link.target === '_blank') return;

            const destino = new URL(link.href, window.location.href);
            if (destino.href === window.location.href) return;
            if (!window.confirm('Você tem alterações não exportadas no PageMaker. Se sair agora, elas serão perdidas. Deseja continuar?')) {
                evento.preventDefault();
                return;
            }
            navegacaoConfirmada = true;
        }, true);
        window.addEventListener('beforeunload', (evento) => {
            if (!alteracoesPendentes || navegacaoConfirmada) return;
            evento.preventDefault();
            evento.returnValue = '';
        });
        atualizarNavegacao();
        atualizarValores();
        adicionarLink();
    });
})();
