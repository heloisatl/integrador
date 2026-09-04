<?php

namespace app\tools\gerador;

class GerenciadorGerador {
    private GeradorModel $geradorModel;
    private GeradorRepositorio $geradorRepositorio;
    private GeradorController $geradorController;
    private GeradorView $geradorView;

    public function __construct() {
        $this->geradorModel = new GeradorModel();
        $this->geradorRepositorio = new GeradorRepositorio();
        $this->geradorController = new GeradorController();
        $this->geradorView = new GeradorView();
    }

    /**
     * Dispara a geração completa da estrutura MVC para uma tabela/entidade.
     *
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @return array
     */
    public function gerarTudo(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): array {
        $resultado = [];

        $resultado['model'] = $this->geradorModel->salvarModel($nomeTabela, $atributos);
        $resultado['repository'] = $this->geradorRepositorio->salvarRepositorio($nomeTabela, $atributos, $chavePrimaria);
        $resultado['controller'] = $this->geradorController->salvarController($nomeTabela, $atributos, $chavePrimaria);
        $resultado['views'] = $this->geradorView->salvarViews($nomeTabela, $atributos, $chavePrimaria);

        return $resultado;
    }
}
