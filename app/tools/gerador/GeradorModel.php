<?php

namespace app\tools\gerador;

use app\models\Atributo;
use app\models\Tabela;

class GeradorModel {
    /**
     * Gera o código-fonte da classe Model respeitando a arquitetura do projeto (namespace app\models).
     *
     * @param Tabela $tabela Nome da tabela/entidade
     * @param array $atributos Lista de nomes das colunas/atributos
     * @return string Conteúdo PHP da classe gerada
     */
    public function gerarModel(Tabela $tabela, array $atributos): string {
        $nomeClasse = $tabela->getNome_tabelaUC();
        $nomeAtributos = "";
        $gettersSetters = "";

        foreach ($atributos as $atributo) {
            foreach($atributo as $att){

                $nomeAtributos .= "    private \${$att->getNome_atributo()};\n";
                $metodo = ucfirst($att->getNome_atributo());
    
                $gettersSetters .= "    public function get{$metodo}() {\n";
                $gettersSetters .= "        return \$this->{$att->getNome_atributo()};\n";
                $gettersSetters .= "    }\n\n";
    
                $gettersSetters .= "    public function set{$metodo}(\${$att->getNome_atributo()}) {\n";
                $gettersSetters .= "        \$this->{$att->getNome_atributo()} = \${$att->getNome_atributo()};\n";
                $gettersSetters .= "    }\n\n";
            }
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
     * @param Tabela $tabela
     * @param array $atributos
     * @param string $caminhoBase
     * @return string Caminho do arquivo salvo
     */
    public function salvarModel(Tabela $tabela, array $atributos, string $caminhoBase = __DIR__ . '/../../models'): string {
        $nomeClasse = ucfirst($tabela->getNome_tabelaUC());
        $conteudo = $this->gerarModel($tabela, $atributos);
        $arquivoDestino = "{$caminhoBase}/{$nomeClasse}.php";

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($arquivoDestino, $conteudo);
        return $arquivoDestino;
    }
}
