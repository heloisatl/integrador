<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Estilo;
use PDO;
use PDOStatement;

class EstiloRepository {
    private PDO $conn;

    public function __construct() {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function insert(Estilo $estilo): int|false {
        $sql = "INSERT INTO estilo (
            nome_arquivo, texto_cabecalho, titulo_aba, cor_fundo, cor_fonte,
            tamanho_fonte, tipo_layout, alinhamento_horizontal, alinhamento_vertical,
            margem_pagina, preenchimento_pagina, cabecalho_fixo, url_fonte,
            peso_fonte, altura_linha, raio_borda, tipo_sombra, imagem_fundo,
            cor_gradiente_inicial, cor_gradiente_final, opacidade_fundo, sem_navegacao,
            cor_links, estilo_links, links, texto_pagina, nome_imagem, url_imagem,
            largura_imagem, alt_imagem, alt_text_imagem, incluir_lista, incluir_tabela,
            incluir_rodape, incluir_card, incluir_formulario, texto_botao, link_botao,
            cor_botao, cor_botao_hover, ordem_elementos, css_customizado, html_customizado
        ) VALUES (
            :nome_arquivo, :texto_cabecalho, :titulo_aba, :cor_fundo, :cor_fonte,
            :tamanho_fonte, :tipo_layout, :alinhamento_horizontal, :alinhamento_vertical,
            :margem_pagina, :preenchimento_pagina, :cabecalho_fixo, :url_fonte,
            :peso_fonte, :altura_linha, :raio_borda, :tipo_sombra, :imagem_fundo,
            :cor_gradiente_inicial, :cor_gradiente_final, :opacidade_fundo, :sem_navegacao,
            :cor_links, :estilo_links, :links, :texto_pagina, :nome_imagem, :url_imagem,
            :largura_imagem, :alt_imagem, :alt_text_imagem, :incluir_lista, :incluir_tabela,
            :incluir_rodape, :incluir_card, :incluir_formulario, :texto_botao, :link_botao,
            :cor_botao, :cor_botao_hover, :ordem_elementos, :css_customizado, :html_customizado
        )";

        $stm = $this->conn->prepare($sql);
        $this->bindParameters($stm, $estilo);

        if ($stm->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    public function getEstiloById(int $id): ?Estilo {
        $sql = "SELECT * FROM estilo WHERE id_estilo = :id";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);
        $stm->execute();

        $dados = $stm->fetch(PDO::FETCH_ASSOC);
        if (!$dados) {
            return null;
        }

        return Estilo::arrayParaObjeto($dados);
    }

    public function getAll(): array {
        $sql = "SELECT * FROM estilo ORDER BY id_estilo ASC";
        $stm = $this->conn->prepare($sql);
        $stm->execute();

        $registros = $stm->fetchAll(PDO::FETCH_ASSOC);
        $estilos = [];

        foreach ($registros as $dados) {
            $estilos[] = Estilo::arrayParaObjeto($dados);
        }

        return $estilos;
    }

    public function update(Estilo $estilo): bool {
        if ($estilo->getIdEstilo() === null) {
            return false;
        }

        $sql = "UPDATE estilo SET
            nome_arquivo = :nome_arquivo,
            texto_cabecalho = :texto_cabecalho,
            titulo_aba = :titulo_aba,
            cor_fundo = :cor_fundo,
            cor_fonte = :cor_fonte,
            tamanho_fonte = :tamanho_fonte,
            tipo_layout = :tipo_layout,
            alinhamento_horizontal = :alinhamento_horizontal,
            alinhamento_vertical = :alinhamento_vertical,
            margem_pagina = :margem_pagina,
            preenchimento_pagina = :preenchimento_pagina,
            cabecalho_fixo = :cabecalho_fixo,
            url_fonte = :url_fonte,
            peso_fonte = :peso_fonte,
            altura_linha = :altura_linha,
            raio_borda = :raio_borda,
            tipo_sombra = :tipo_sombra,
            imagem_fundo = :imagem_fundo,
            cor_gradiente_inicial = :cor_gradiente_inicial,
            cor_gradiente_final = :cor_gradiente_final,
            opacidade_fundo = :opacidade_fundo,
            sem_navegacao = :sem_navegacao,
            cor_links = :cor_links,
            estilo_links = :estilo_links,
            links = :links,
            texto_pagina = :texto_pagina,
            nome_imagem = :nome_imagem,
            url_imagem = :url_imagem,
            largura_imagem = :largura_imagem,
            alt_imagem = :alt_imagem,
            alt_text_imagem = :alt_text_imagem,
            incluir_lista = :incluir_lista,
            incluir_tabela = :incluir_tabela,
            incluir_rodape = :incluir_rodape,
            incluir_card = :incluir_card,
            incluir_formulario = :incluir_formulario,
            texto_botao = :texto_botao,
            link_botao = :link_botao,
            cor_botao = :cor_botao,
            cor_botao_hover = :cor_botao_hover,
            ordem_elementos = :ordem_elementos,
            css_customizado = :css_customizado,
            html_customizado = :html_customizado
            WHERE id_estilo = :id_estilo";

        $stm = $this->conn->prepare($sql);
        $this->bindParameters($stm, $estilo);
        $stm->bindValue(':id_estilo', $estilo->getIdEstilo(), PDO::PARAM_INT);

        return $stm->execute();
    }

    public function delete(int $id): bool {
        /**
         * Desvinculação defensiva em cascata suave:
         * A tabela projeto referencia estilo(id_estilo) com ON DELETE NO ACTION.
         * Desvinculamos fk_estilo = NULL para permitir exclusão sem violação de constraint 1451.
         */
        $sqlCleanFks = "UPDATE projeto SET fk_estilo = NULL WHERE fk_estilo = :id";
        $stmClean = $this->conn->prepare($sqlCleanFks);
        $stmClean->bindValue(':id', $id, PDO::PARAM_INT);
        $stmClean->execute();

        $sql = "DELETE FROM estilo WHERE id_estilo = :id";
        $stm = $this->conn->prepare($sql);
        $stm->bindValue(':id', $id, PDO::PARAM_INT);

        return $stm->execute();
    }

    private function bindParameters(PDOStatement $stm, Estilo $estilo): void {
        $stm->bindValue(':nome_arquivo', $estilo->getNomeArquivo());
        $stm->bindValue(':texto_cabecalho', $estilo->getTextoCabecalho());
        $stm->bindValue(':titulo_aba', $estilo->getTituloAba());
        $stm->bindValue(':cor_fundo', $estilo->getCorFundo());
        $stm->bindValue(':cor_fonte', $estilo->getCorFonte());
        $stm->bindValue(':tamanho_fonte', $estilo->getTamanhoFonte());
        $stm->bindValue(':tipo_layout', $estilo->getTipoLayout());
        $stm->bindValue(':alinhamento_horizontal', $estilo->getAlinhamentoHorizontal());
        $stm->bindValue(':alinhamento_vertical', $estilo->getAlinhamentoVertical());
        $stm->bindValue(':margem_pagina', $estilo->getMargemPagina(), PDO::PARAM_INT);
        $stm->bindValue(':preenchimento_pagina', $estilo->getPreenchimentoPagina(), PDO::PARAM_INT);
        $stm->bindValue(':cabecalho_fixo', $estilo->isCabecalhoFixo() ? 1 : 0, PDO::PARAM_INT);
        $stm->bindValue(':url_fonte', $estilo->getUrlFonte());
        $stm->bindValue(':peso_fonte', $estilo->getPesoFonte());
        $stm->bindValue(':altura_linha', $estilo->getAlturaLinha());
        $stm->bindValue(':raio_borda', $estilo->getRaioBorda(), PDO::PARAM_INT);
        $stm->bindValue(':tipo_sombra', $estilo->getTipoSombra());
        $stm->bindValue(':imagem_fundo', $estilo->getImagemFundo());
        $stm->bindValue(':cor_gradiente_inicial', $estilo->getCorGradienteInicial());
        $stm->bindValue(':cor_gradiente_final', $estilo->getCorGradienteFinal());
        $stm->bindValue(':opacidade_fundo', $estilo->getOpacidadeFundo(), PDO::PARAM_INT);
        $stm->bindValue(':sem_navegacao', $estilo->isSemNavegacao() ? 1 : 0, PDO::PARAM_INT);
        $stm->bindValue(':cor_links', $estilo->getCorLinks());
        $stm->bindValue(':estilo_links', $estilo->getEstiloLinks(), PDO::PARAM_INT);
        $stm->bindValue(':links', $estilo->getLinks());
        $stm->bindValue(':texto_pagina', $estilo->getTextoPagina());
        $stm->bindValue(':nome_imagem', $estilo->getNomeImagem());
        $stm->bindValue(':url_imagem', $estilo->getUrlImagem());
        $stm->bindValue(':largura_imagem', $estilo->getLarguraImagem(), $estilo->getLarguraImagem() !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stm->bindValue(':alt_imagem', $estilo->getAltImagem());
        $stm->bindValue(':alt_text_imagem', $estilo->getAltTextImagem());
        $stm->bindValue(':incluir_lista', $estilo->isIncluirLista() ? 1 : 0, PDO::PARAM_INT);
        $stm->bindValue(':incluir_tabela', $estilo->isIncluirTabela() ? 1 : 0, PDO::PARAM_INT);
        $stm->bindValue(':incluir_rodape', $estilo->isIncluirRodape() ? 1 : 0, PDO::PARAM_INT);
        $stm->bindValue(':incluir_card', $estilo->isIncluirCard() ? 1 : 0, PDO::PARAM_INT);
        $stm->bindValue(':incluir_formulario', $estilo->isIncluirFormulario() ? 1 : 0, PDO::PARAM_INT);
        $stm->bindValue(':texto_botao', $estilo->getTextoBotao());
        $stm->bindValue(':link_botao', $estilo->getLinkBotao());
        $stm->bindValue(':cor_botao', $estilo->getCorBotao());
        $stm->bindValue(':cor_botao_hover', $estilo->getCorBotaoHover());
        $stm->bindValue(':ordem_elementos', $estilo->getOrdemElementos());
        $stm->bindValue(':css_customizado', $estilo->getCssCustomizado());
        $stm->bindValue(':html_customizado', $estilo->getHtmlCustomizado());
    }
}
