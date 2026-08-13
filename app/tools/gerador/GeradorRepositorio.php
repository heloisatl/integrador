<?php

namespace app\tools\gerador;

class GeradorRepositorio {
    /**
     * Gera o repositório/DAO respeitando ConnectionFactory e o namespace app\repositories.
     *
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @return string
     */
    public function gerarRepositorio(string $nomeTabela, array $atributos, string $chavePrimaria = 'id'): string {
        $nomeClasse = ucfirst($nomeTabela);
        $nomeRepositorio = "{$nomeClasse}Repository";

        // Filtra atributos excluindo a chave primária para inserção
        $camposInserir = array_values(array_filter($atributos, fn($a) => $a !== $chavePrimaria));
        $sqlCols = implode(', ', $camposInserir);
        $placeholders = implode(', ', array_fill(0, count($camposInserir), '?'));

        $atribuicoesMetodos = "";
        $vetAtributos = [];
        foreach ($camposInserir as $campo) {
            $metodo = ucfirst($campo);
            $atribuicoesMetodos .= "        \${$campo} = \$obj->get{$metodo}();\n";
            $vetAtributos[] = "\${$campo}";
        }
        $atributosParams = implode(', ', $vetAtributos);

        $setCampos = [];
        foreach ($camposInserir as $campo) {
            $setCampos[] = "{$campo} = ?";
        }
        $sqlSet = implode(', ', $setCampos);

        return <<<PHP
<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\\{$nomeClasse};
use PDO;

class {$nomeRepositorio} {
    private PDO \$con;

    public function __construct() {
        \$this->con = ConnectionFactory::getConnection();
    }

    public function inserir({$nomeClasse} \$obj): bool {
        \$sql = "INSERT INTO {$nomeTabela} ({$sqlCols}) VALUES ({$placeholders})";
        \$stmt = \$this->con->prepare(\$sql);
{$atribuicoesMetodos}
        return \$stmt->execute([{$atributosParams}]);
    }

    public function listarTodos(): array {
        \$sql = "SELECT * FROM {$nomeTabela}";
        \$query = \$this->con->query(\$sql);
        return \$query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int \$id): ?array {
        \$sql = "SELECT * FROM {$nomeTabela} WHERE {$chavePrimaria} = ?";
        \$stmt = \$this->con->prepare(\$sql);
        \$stmt->execute([\$id]);
        \$resultado = \$stmt->fetch(PDO::FETCH_ASSOC);
        return \$resultado ?: null;
    }

    public function alterar({$nomeClasse} \$obj, int \$id): bool {
{$atribuicoesMetodos}
        \$sql = "UPDATE {$nomeTabela} SET {$sqlSet} WHERE {$chavePrimaria} = ?";
        \$params = [{$atributosParams}, \$id];
        \$stmt = \$this->con->prepare(\$sql);
        return \$stmt->execute(\$params);
    }

    public function excluir(int \$id): bool {
        \$sql = "DELETE FROM {$nomeTabela} WHERE {$chavePrimaria} = ?";
        \$stmt = \$this->con->prepare(\$sql);
        return \$stmt->execute([\$id]);
    }
}
PHP;
    }

    /**
     * Salva o repositório em app/repositories/
     *
     * @param string $nomeTabela
     * @param array $atributos
     * @param string $chavePrimaria
     * @param string $caminhoBase
     * @return string
     */
    public function salvarRepositorio(string $nomeTabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../repositories'): string {
        $nomeClasse = ucfirst($nomeTabela);
        $conteudo = $this->gerarRepositorio($nomeTabela, $atributos, $chavePrimaria);
        $arquivoDestino = "{$caminhoBase}/{$nomeClasse}Repository.php";

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($arquivoDestino, $conteudo);
        return $arquivoDestino;
    }
}
