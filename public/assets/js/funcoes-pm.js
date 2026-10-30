(() => {
    'use strict';

    let navegacaoAtiva = true;
    let imagemSelecionada = '';

    const buscar = (id) => document.getElementById(id);
    const ler = (id, padrao = '') => buscar(id)?.value.trim() || padrao;
    const marcado = (id) => buscar(id)?.checked === true;

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

        return `${ler('urlFonte') ? `@import url('${ler('urlFonte')}');\n\n` : ''}:root {
    --cor-fundo: ${ler('corFundo', '#ffffff')};
    --cor-fonte: ${ler('corFonte', '#000000')};
    --cor-links: ${ler('corLinks', '#0000ff')};
    --cor-botao: ${ler('corBotao', '#5b6af0')};
    --cor-botao-hover: ${ler('corBotaoHover', '#4a59df')};
}

body {
    background: ${fundo};
    background-size: cover;
    color: var(--cor-fonte);
    font-family: Arial, sans-serif;
    font-size: ${tamanho};
    font-weight: ${ler('pesoFonte', '400')};
    line-height: ${ler('alturaLinha', '1.5')};
    margin: ${ler('margemPagina', '24')}px;
    padding: ${ler('preenchimentoPagina', '24')}px;
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

@media (max-width: 700px) {
    .grade-conteudo { flex-direction: column; }
    .grade-conteudo > * { flex-basis: auto; width: 100%; }
}`;
    }

    function gerarLinks() {
        if (!navegacaoAtiva) return '';
        return [...document.querySelectorAll('input[name="links"]')].map((link) => {
            const url = link.closest('.pm-grupo-link')?.querySelector('input[name="href"]')?.value.trim() || '';
            return link.value.trim() && url ? `<a href="${url}">${link.value.trim()}</a>` : '';
        }).filter(Boolean).join('\n');
    }

    function gerarComponentes() {
        const imagem = obterImagem();
        const imagemHtml = imagem ? `<img src="${imagem}" alt="${ler('altImagem', 'Imagem do projeto')}">` : '';
        const card = marcado('incluirCard') ? `<article class="cartao-conteudo">${imagemHtml}<h2>${ler('textoCabecalho', 'Meu Projeto')}</h2><p>${ler('textoPagina', 'Conteúdo do card')}</p><a class="botao-cta" href="${ler('linkBotao', '#')}">${ler('textoBotao', 'Saiba mais')}</a></article>` : '';
        const formulario = marcado('incluirFormulario') ? `<form class="formulario-contato"><input placeholder="Nome" required><input type="email" placeholder="Email" required><textarea placeholder="Mensagem" required></textarea><button class="botao-cta" type="submit">Enviar</button></form>` : '';
        const lista = marcado('incluirLista') ? '<ul><li>Item da lista</li></ul>' : '';
        const tabela = marcado('incluirTabela') ? '<table border="1"><tr><td>Dados</td></tr></table>' : '';
        const rodape = marcado('incluirRodape') ? '<footer>Gerado pelo DevStudio</footer>' : '';
        const ordem = ler('ordemElementos', 'texto-imagem-lista-tabela').split('-');
        const elementos = {
            texto: `<p class="bloco-texto">${ler('textoPagina', 'Conteúdo da página')}</p>`,
            imagem: imagemHtml ? `<div class="bloco-imagem">${imagemHtml}</div>` : '',
            lista,
            tabela
        };
        const conteudo = ordem.map((item) => elementos[item] || '').join('\n');
        return { conteudo: `${conteudo}${card}${formulario}`, rodape };
    }

    function gerarHtml() {
        const links = gerarLinks();
        const componentes = gerarComponentes();
        return `<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>${ler('tituloAba', 'Meu Projeto')}</title><link rel="stylesheet" href="estilo.css"></head>
<body>
    <header><h1>${ler('textoCabecalho', 'Meu Projeto')}</h1></header>
    ${links ? `<nav>${links}</nav>` : ''}
    <main class="grade-conteudo">${componentes.conteudo}</main>
    <a class="botao-cta" href="${ler('linkBotao', '#')}">${ler('textoBotao', 'Saiba mais')}</a>
    ${componentes.rodape}
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
        const documento = iframe.contentDocument || iframe.contentWindow.document;
        documento.open();
        documento.write(`<style>${buscar('codeCSS').value}</style>${buscar('codeHTML').value}`);
        documento.close();
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
        buscar('gerarCodigo')?.addEventListener('click', atualizarSaida);
        buscar('visualizarPrevia')?.addEventListener('click', atualizarSaida);
        document.querySelectorAll('input[type="range"]').forEach((controle) => controle.addEventListener('input', atualizarValores));
        document.querySelectorAll('[data-largura]').forEach((botao) => botao.addEventListener('click', () => {
            buscar('pagina').style.width = botao.dataset.largura;
            document.querySelectorAll('[data-largura]').forEach((item) => item.classList.remove('pm-tela-ativa'));
            botao.classList.add('pm-tela-ativa');
        }));
        buscar('fileImagem')?.addEventListener('change', (evento) => {
            if (evento.target.files.length) imagemSelecionada = URL.createObjectURL(evento.target.files[0]);
        });
        atualizarNavegacao();
        atualizarValores();
        adicionarLink();
    });
})();
