<?php

namespace app\tools\gerador;

class GeradorModel {
    /**
     * Gera o código-fonte da classe Model respeitando a arquitetura do projeto (namespace app\models).
     *
     * @param string $nomeTabela Nome da tabela/entidade
     * @param array $atributos Lista de nomes das colunas/atributos
     * @return string Conteúdo PHP da classe gerada
     */
    public function gerarModel(string $nomeTabela, array $atributos): string {
        $nomeClasse = ucfirst($nomeTabela);
        $nomeAtributos = "";
        $gettersSetters = "";

        foreach ($atributos as $atributo) {
            $nomeAtributos .= "    private \${$atributo};\n";
            $metodo = ucfirst($atributo);

            $gettersSetters .= "    public function get{$metodo}() {\n";
            $gettersSetters .= "        return \$this->{$atributo};\n";
            $gettersSetters .= "    }\n\n";

            $gettersSetters .= "    public function set{$metodo}(\${$atributo}) {\n";
            $gettersSetters .= "        \$this->{$atributo} = \${$atributo};\n";
            $gettersSetters .= "    }\n\n";
        }

        return <<<PHP
<?php

namespace app\models;

class {$nomeClasse} {
{$nomeAtributos}
{$gettersSetters}}
PHP;
    }

    /**
     * Salva a classe Model gerada na pasta app/models/
     *
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $caminhoBase
     * @return string Caminho do arquivo salvo
     */
    public function salvarModel(string $nomeTabela, array $atributos, string $caminhoBase = __DIR__ . '/../../models'): string {
        $nomeClasse = ucfirst($nomeTabela);
        $conteudo = $this->gerarModel($nomeTabela, $atributos);
        $arquivoDestino = "{$caminhoBase}/{$nomeClasse}.php";

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($arquivoDestino, $conteudo);
        return $arquivoDestino;
    }
}
