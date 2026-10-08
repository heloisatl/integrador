<?php

namespace app\models;

class Estilo {
    private ?int $idEstilo;
    private string $nomeArquivo;
    private string $textoCabecalho;
    private string $tituloAba;
    private string $corFundo;
    private string $corFonte;
    private string $tamanhoFonte;
    private string $tipoLayout;
    private string $alinhamentoHorizontal;
    private string $alinhamentoVertical;
    private int $margemPagina;
    private int $preenchimentoPagina;
    private bool $cabecalhoFixo;
    private ?string $urlFonte;
    private string $pesoFonte;
    private float $alturaLinha;
    private int $raioBorda;
    private string $tipoSombra;
    private ?string $imagemFundo;
    private string $corGradienteInicial;
    private string $corGradienteFinal;
    private int $opacidadeFundo;
    private bool $semNavegacao;
    private string $corLinks;
    private int $estiloLinks;
    private ?string $links;
    private ?string $textoPagina;
    private ?string $nomeImagem;
    private ?string $urlImagem;
    private ?int $larguraImagem;
    private ?string $altImagem;
    private ?string $altTextImagem;
    private bool $incluirLista;
    private bool $incluirTabela;
    private bool $incluirRodape;
    private bool $incluirCard;
    private bool $incluirFormulario;
    private string $textoBotao;
    private string $linkBotao;
    private string $corBotao;
    private string $corBotaoHover;
    private string $ordemElementos;
    private ?string $cssCustomizado;
    private ?string $htmlCustomizado;

    public function __construct(
        ?int $idEstilo = null,
        string $nomeArquivo = 'index.html',
        string $textoCabecalho = 'Meu Projeto',
        string $tituloAba = 'Meu Projeto',
        string $corFundo = '#ffffff',
        string $corFonte = '#000000',
        string $tamanhoFonte = 'medium',
        string $tipoLayout = '1',
        string $alinhamentoHorizontal = 'flex-start',
        string $alinhamentoVertical = 'flex-start',
        int $margemPagina = 24,
        int $preenchimentoPagina = 24,
        bool $cabecalhoFixo = false,
        ?string $urlFonte = null,
        string $pesoFonte = '400',
        float $alturaLinha = 1.5,
        int $raioBorda = 8,
        string $tipoSombra = 'none',
        ?string $imagemFundo = null,
        string $corGradienteInicial = '#ffffff',
        string $corGradienteFinal = '#e8e8ff',
        int $opacidadeFundo = 100,
        bool $semNavegacao = false,
        string $corLinks = '#0000ff',
        int $estiloLinks = 1,
        ?string $links = null,
        ?string $textoPagina = null,
        ?string $nomeImagem = null,
        ?string $urlImagem = null,
        ?int $larguraImagem = null,
        ?string $altImagem = null,
        ?string $altTextImagem = null,
        bool $incluirLista = false,
        bool $incluirTabela = false,
        bool $incluirRodape = false,
        bool $incluirCard = false,
        bool $incluirFormulario = false,
        string $textoBotao = 'Saiba mais',
        string $linkBotao = 'https://exemplo.com',
        string $corBotao = '#5b6af0',
        string $corBotaoHover = '#4a59df',
        string $ordemElementos = 'texto-imagem-lista-tabela',
        ?string $cssCustomizado = null,
        ?string $htmlCustomizado = null
    ) {
        $this->idEstilo = $idEstilo;
        $this->nomeArquivo = $nomeArquivo;
        $this->textoCabecalho = $textoCabecalho;
        $this->tituloAba = $tituloAba;
        $this->corFundo = $corFundo;
        $this->corFonte = $corFonte;
        $this->tamanhoFonte = $tamanhoFonte;
        $this->tipoLayout = $tipoLayout;
        $this->alinhamentoHorizontal = $alinhamentoHorizontal;
        $this->alinhamentoVertical = $alinhamentoVertical;
        $this->margemPagina = $margemPagina;
        $this->preenchimentoPagina = $preenchimentoPagina;
        $this->cabecalhoFixo = $cabecalhoFixo;
        $this->urlFonte = $urlFonte;
        $this->pesoFonte = $pesoFonte;
        $this->alturaLinha = $alturaLinha;
        $this->raioBorda = $raioBorda;
        $this->tipoSombra = $tipoSombra;
        $this->imagemFundo = $imagemFundo;
        $this->corGradienteInicial = $corGradienteInicial;
        $this->corGradienteFinal = $corGradienteFinal;
        $this->opacidadeFundo = $opacidadeFundo;
        $this->semNavegacao = $semNavegacao;
        $this->corLinks = $corLinks;
        $this->estiloLinks = $estiloLinks;
        $this->links = $links;
        $this->textoPagina = $textoPagina;
        $this->nomeImagem = $nomeImagem;
        $this->urlImagem = $urlImagem;
        $this->larguraImagem = $larguraImagem;
        $this->altImagem = $altImagem;
        $this->altTextImagem = $altTextImagem;
        $this->incluirLista = $incluirLista;
        $this->incluirTabela = $incluirTabela;
        $this->incluirRodape = $incluirRodape;
        $this->incluirCard = $incluirCard;
        $this->incluirFormulario = $incluirFormulario;
        $this->textoBotao = $textoBotao;
        $this->linkBotao = $linkBotao;
        $this->corBotao = $corBotao;
        $this->corBotaoHover = $corBotaoHover;
        $this->ordemElementos = $ordemElementos;
        $this->cssCustomizado = $cssCustomizado;
        $this->htmlCustomizado = $htmlCustomizado;
    }

    // Getters
    public function getIdEstilo(): ?int { return $this->idEstilo; }
    public function getNomeArquivo(): string { return $this->nomeArquivo; }
    public function getTextoCabecalho(): string { return $this->textoCabecalho; }
    public function getTituloAba(): string { return $this->tituloAba; }
    public function getCorFundo(): string { return $this->corFundo; }
    public function getCorFonte(): string { return $this->corFonte; }
    public function getTamanhoFonte(): string { return $this->tamanhoFonte; }
    public function getTipoLayout(): string { return $this->tipoLayout; }
    public function getAlinhamentoHorizontal(): string { return $this->alinhamentoHorizontal; }
    public function getAlinhamentoVertical(): string { return $this->alinhamentoVertical; }
    public function getMargemPagina(): int { return $this->margemPagina; }
    public function getPreenchimentoPagina(): int { return $this->preenchimentoPagina; }
    public function isCabecalhoFixo(): bool { return $this->cabecalhoFixo; }
    public function getUrlFonte(): ?string { return $this->urlFonte; }
    public function getPesoFonte(): string { return $this->pesoFonte; }
    public function getAlturaLinha(): float { return $this->alturaLinha; }
    public function getRaioBorda(): int { return $this->raioBorda; }
    public function getTipoSombra(): string { return $this->tipoSombra; }
    public function getImagemFundo(): ?string { return $this->imagemFundo; }
    public function getCorGradienteInicial(): string { return $this->corGradienteInicial; }
    public function getCorGradienteFinal(): string { return $this->corGradienteFinal; }
    public function getOpacidadeFundo(): int { return $this->opacidadeFundo; }
    public function isSemNavegacao(): bool { return $this->semNavegacao; }
    public function getCorLinks(): string { return $this->corLinks; }
    public function getEstiloLinks(): int { return $this->estiloLinks; }
    public function getLinks(): ?string { return $this->links; }
    public function getTextoPagina(): ?string { return $this->textoPagina; }
    public function getNomeImagem(): ?string { return $this->nomeImagem; }
    public function getUrlImagem(): ?string { return $this->urlImagem; }
    public function getLarguraImagem(): ?int { return $this->larguraImagem; }
    public function getAltImagem(): ?string { return $this->altImagem; }
    public function getAltTextImagem(): ?string { return $this->altTextImagem; }
    public function isIncluirLista(): bool { return $this->incluirLista; }
    public function isIncluirTabela(): bool { return $this->incluirTabela; }
    public function isIncluirRodape(): bool { return $this->incluirRodape; }
    public function isIncluirCard(): bool { return $this->incluirCard; }
    public function isIncluirFormulario(): bool { return $this->incluirFormulario; }
    public function getTextoBotao(): string { return $this->textoBotao; }
    public function getLinkBotao(): string { return $this->linkBotao; }
    public function getCorBotao(): string { return $this->corBotao; }
    public function getCorBotaoHover(): string { return $this->corBotaoHover; }
    public function getOrdemElementos(): string { return $this->ordemElementos; }
    public function getCssCustomizado(): ?string { return $this->cssCustomizado; }
    public function getHtmlCustomizado(): ?string { return $this->htmlCustomizado; }

    // Setters
    public function setNomeArquivo(string $nomeArquivo): void { $this->nomeArquivo = $nomeArquivo; }
    public function setTextoCabecalho(string $textoCabecalho): void { $this->textoCabecalho = $textoCabecalho; }
    public function setTituloAba(string $tituloAba): void { $this->tituloAba = $tituloAba; }
    public function setCorFundo(string $corFundo): void { $this->corFundo = $corFundo; }
    public function setCorFonte(string $corFonte): void { $this->corFonte = $corFonte; }
    public function setTamanhoFonte(string $tamanhoFonte): void { $this->tamanhoFonte = $tamanhoFonte; }
    public function setTipoLayout(string $tipoLayout): void { $this->tipoLayout = $tipoLayout; }
    public function setAlinhamentoHorizontal(string $alinhamentoHorizontal): void { $this->alinhamentoHorizontal = $alinhamentoHorizontal; }
    public function setAlinhamentoVertical(string $alinhamentoVertical): void { $this->alinhamentoVertical = $alinhamentoVertical; }
    public function setMargemPagina(int $margemPagina): void { $this->margemPagina = $margemPagina; }
    public function setPreenchimentoPagina(int $preenchimentoPagina): void { $this->preenchimentoPagina = $preenchimentoPagina; }
    public function setCabecalhoFixo(bool $cabecalhoFixo): void { $this->cabecalhoFixo = $cabecalhoFixo; }
    public function setUrlFonte(?string $urlFonte): void { $this->urlFonte = $urlFonte; }
    public function setPesoFonte(string $pesoFonte): void { $this->pesoFonte = $pesoFonte; }
    public function setAlturaLinha(float $alturaLinha): void { $this->alturaLinha = $alturaLinha; }
    public function setRaioBorda(int $raioBorda): void { $this->raioBorda = $raioBorda; }
    public function setTipoSombra(string $tipoSombra): void { $this->tipoSombra = $tipoSombra; }
    public function setImagemFundo(?string $imagemFundo): void { $this->imagemFundo = $imagemFundo; }
    public function setCorGradienteInicial(string $corGradienteInicial): void { $this->corGradienteInicial = $corGradienteInicial; }
    public function setCorGradienteFinal(string $corGradienteFinal): void { $this->corGradienteFinal = $corGradienteFinal; }
    public function setOpacidadeFundo(int $opacidadeFundo): void { $this->opacidadeFundo = $opacidadeFundo; }
    public function setSemNavegacao(bool $semNavegacao): void { $this->semNavegacao = $semNavegacao; }
    public function setCorLinks(string $corLinks): void { $this->corLinks = $corLinks; }
    public function setEstiloLinks(int $estiloLinks): void { $this->estiloLinks = $estiloLinks; }
    public function setLinks(?string $links): void { $this->links = $links; }
    public function setTextoPagina(?string $textoPagina): void { $this->textoPagina = $textoPagina; }
    public function setNomeImagem(?string $nomeImagem): void { $this->nomeImagem = $nomeImagem; }
    public function setUrlImagem(?string $urlImagem): void { $this->urlImagem = $urlImagem; }
    public function setLarguraImagem(?int $larguraImagem): void { $this->larguraImagem = $larguraImagem; }
    public function setAltImagem(?string $altImagem): void { $this->altImagem = $altImagem; }
    public function setAltTextImagem(?string $altTextImagem): void { $this->altTextImagem = $altTextImagem; }
    public function setIncluirLista(bool $incluirLista): void { $this->incluirLista = $incluirLista; }
    public function setIncluirTabela(bool $incluirTabela): void { $this->incluirTabela = $incluirTabela; }
    public function setIncluirRodape(bool $incluirRodape): void { $this->incluirRodape = $incluirRodape; }
    public function setIncluirCard(bool $incluirCard): void { $this->incluirCard = $incluirCard; }
    public function setIncluirFormulario(bool $incluirFormulario): void { $this->incluirFormulario = $incluirFormulario; }
    public function setTextoBotao(string $textoBotao): void { $this->textoBotao = $textoBotao; }
    public function setLinkBotao(string $linkBotao): void { $this->linkBotao = $linkBotao; }
    public function setCorBotao(string $corBotao): void { $this->corBotao = $corBotao; }
    public function setCorBotaoHover(string $corBotaoHover): void { $this->corBotaoHover = $corBotaoHover; }
    public function setOrdemElementos(string $ordemElementos): void { $this->ordemElementos = $ordemElementos; }
    public function setCssCustomizado(?string $cssCustomizado): void { $this->cssCustomizado = $cssCustomizado; }
    public function setHtmlCustomizado(?string $htmlCustomizado): void { $this->htmlCustomizado = $htmlCustomizado; }

    public static function arrayParaObjeto(array $dados): self {
        return new self(
            isset($dados['id_estilo']) ? (int) $dados['id_estilo'] : null,
            (string) ($dados['nome_arquivo'] ?? 'index.html'),
            (string) ($dados['texto_cabecalho'] ?? $dados['cabecalho'] ?? 'Meu Projeto'),
            (string) ($dados['titulo_aba'] ?? 'Meu Projeto'),
            (string) ($dados['cor_fundo'] ?? $dados['cor_primaria'] ?? '#ffffff'),
            (string) ($dados['cor_fonte'] ?? $dados['cor_secundaria'] ?? '#000000'),
            (string) ($dados['tamanho_fonte'] ?? 'medium'),
            (string) ($dados['tipo_layout'] ?? '1'),
            (string) ($dados['alinhamento_horizontal'] ?? 'flex-start'),
            (string) ($dados['alinhamento_vertical'] ?? 'flex-start'),
            (int) ($dados['margem_pagina'] ?? 24),
            (int) ($dados['preenchimento_pagina'] ?? 24),
            !empty($dados['cabecalho_fixo']),
            $dados['url_fonte'] ?? null,
            (string) ($dados['peso_fonte'] ?? '400'),
            (float) ($dados['altura_linha'] ?? 1.5),
            (int) ($dados['raio_borda'] ?? 8),
            (string) ($dados['tipo_sombra'] ?? 'none'),
            $dados['imagem_fundo'] ?? null,
            (string) ($dados['cor_gradiente_inicial'] ?? '#ffffff'),
            (string) ($dados['cor_gradiente_final'] ?? '#e8e8ff'),
            (int) ($dados['opacidade_fundo'] ?? 100),
            !empty($dados['sem_navegacao']),
            (string) ($dados['cor_links'] ?? '#0000ff'),
            (int) ($dados['estilo_links'] ?? 1),
            $dados['links'] ?? null,
            $dados['texto_pagina'] ?? $dados['conteudo_principal'] ?? null,
            $dados['nome_imagem'] ?? null,
            $dados['url_imagem'] ?? null,
            isset($dados['largura_imagem']) && $dados['largura_imagem'] !== '' ? (int) $dados['largura_imagem'] : null,
            $dados['alt_imagem'] ?? null,
            $dados['alt_text_imagem'] ?? null,
            !empty($dados['incluir_lista']),
            !empty($dados['incluir_tabela']),
            !empty($dados['incluir_rodape']),
            !empty($dados['incluir_card']),
            !empty($dados['incluir_formulario']),
            (string) ($dados['texto_botao'] ?? 'Saiba mais'),
            (string) ($dados['link_botao'] ?? 'https://exemplo.com'),
            (string) ($dados['cor_botao'] ?? '#5b6af0'),
            (string) ($dados['cor_botao_hover'] ?? '#4a59df'),
            (string) ($dados['ordem_elementos'] ?? 'texto-imagem-lista-tabela'),
            $dados['css_customizado'] ?? null,
            $dados['html_customizado'] ?? null
        );
    }
}
