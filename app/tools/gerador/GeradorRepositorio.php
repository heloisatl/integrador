<?php

namespace app\tools\gerador;
use app\models\Tabela;
use app\models\Atributo;

class GeradorRepositorio {
    /**
     * Gera o repositório/DAO respeitando ConnectionFactory e o namespace app\repositories.
     *
     * @param bool $comComentarios Insere comentários explicativos e didáticos em PT-BR para iniciantes.
     */
    public function gerarRepositorio(Tabela $tabela, array $atributos, string $chavePrimaria = 'id', bool $comComentarios = false): string {
        $nomeClasse = $tabela->getNome_tabelaUC();
        $nomeRepositorio = "{$nomeClasse}Repository";
        $campos = [];
        // Filtra atributos excluindo a chave primária para inserção
        foreach ($atributos as $key => $att) {
   
            if($att->getPk()){
                $chavePrimaria = $att->getNome_atributo();
                unset($atributos[$key]);
            }else{
                $campos[] = $att->getNome_atributo();
            }
            
        }
        $camposInserir = array_values(array_filter($campos));
        $sqlCols = implode(', ', $camposInserir);
        $placeholders = implode(', ', array_fill(0, count($camposInserir), '?'));

        $atribuicoesMetodos = "";
        $vetAtributos = [];
        $setCampos = [];
        foreach ($atributos as $campo) {
            $metodo = ucfirst($campo->getNome_atributo());
            $atribuicoesMetodos .= "        \${$campo->getNome_atributo()} = \$obj->get{$metodo}();\n";
            $vetAtributos[] = "\${$campo->getNome_atributo()}";
            $setCampos[] = "{$campo->getNome_atributo()} = ?";
        }
        $atributosParams = implode(', ', $vetAtributos);
        $sqlSet = implode(', ', $setCampos);

        $docClasse = "";
        $docConstruct = "";
        $docInserir = "";
        $docListar = "";
        $docBuscar = "";
        $docAlterar = "";
        $docExcluir = "";

        if ($comComentarios) {
            $docClasse = <<<DOC
/**
 * Camada de Acesso a Dados (DAO / Repository) - Entidade {$nomeClasse}
 *
 * Esta classe gerencia a persistência no MySQL para a tabela '{$tabela->getNome_tabela()}':
 * - Comunicação segura via extensão PDO (PHP Data Objects);
 * - Uso obrigatório de Prepared Statements contra ataques de SQL Injection;
 * - Implementação dos métodos fundamentais do CRUD (Create, Read, Update, Delete).
 */
DOC;
            $docConstruct = "    /**\n     * Obtém a instância única de conexão PDO via ConnectionFactory (Padrão Singleton).\n     */\n";
            $docInserir = "    /**\n     * CREATE: Insere um novo registro com segurança utilizando Prepared Statement.\n     * O método prepare() pré-compila a instrução e execute() envia os dados separadamente,\n     * prevenindo que valores do usuário sejam interpretados como código SQL malicioso.\n     */\n";
            $docListar = "    /**\n     * READ: Retorna todos os registros cadastrados na tabela como arrays associativos (PDO::FETCH_ASSOC).\n     */\n";
            $docBuscar = "    /**\n     * READ: Localiza um registro específico com base na Chave Primária ('{$chavePrimaria}').\n     * O identificador é parametrizado com segurança para evitar injeção de SQL.\n     */\n";
            $docAlterar = "    /**\n     * UPDATE: Atualiza os dados de um registro existente com base no ID.\n     * Monta a cláusula SET parametrizada e vincula o identificador ao final dos parâmetros.\n     */\n";
            $docExcluir = "    /**\n     * DELETE: Remove definitivamente um registro da tabela pelo seu identificador único.\n     */\n";
        }

        $blocoDocClasse = $docClasse ? "\n" . $docClasse : "";

        return <<<PHP
<?php

namespace app\\repositories;

use app\\database\\ConnectionFactory;
use app\\models\\{$nomeClasse};
use PDO;
{$blocoDocClasse}
class {$nomeRepositorio} {
    private PDO \$con;

{$docConstruct}    public function __construct() {
        \$this->con = ConnectionFactory::getConnection();
    }

{$docInserir}    public function inserir({$nomeClasse} \$obj): bool {
        \$sql = "INSERT INTO {$tabela->getNome_tabela()} ({$sqlCols}) VALUES ({$placeholders})";
        \$stmt = \$this->con->prepare(\$sql);
{$atribuicoesMetodos}
        return \$stmt->execute([{$atributosParams}]);
    }

{$docListar}    public function listarTodos(): array {
        \$sql = "SELECT * FROM {$tabela->getNome_tabela()}";
        \$query = \$this->con->query(\$sql);
        return \$query->fetchAll(PDO::FETCH_ASSOC);
    }

{$docBuscar}    public function buscarPorId(int \$id): ?array {
        \$sql = "SELECT * FROM {$tabela->getNome_tabela()} WHERE {$chavePrimaria} = ?";
        \$stmt = \$this->con->prepare(\$sql);
        \$stmt->execute([\$id]);
        \$resultado = \$stmt->fetch(PDO::FETCH_ASSOC);
        return \$resultado ?: null;
    }

{$docAlterar}    public function alterar({$nomeClasse} \$obj, int \$id): bool {
{$atribuicoesMetodos}
        \$sql = "UPDATE {$tabela->getNome_tabela()} SET {$sqlSet} WHERE {$chavePrimaria} = ?";
        \$params = [{$atributosParams}, \$id];
        \$stmt = \$this->con->prepare(\$sql);
        return \$stmt->execute(\$params);
    }

{$docExcluir}    public function excluir(int \$id): bool {
        \$sql = "DELETE FROM {$tabela->getNome_tabela()} WHERE {$chavePrimaria} = ?";
        \$stmt = \$this->con->prepare(\$sql);
        return \$stmt->execute([\$id]);
    }
}
PHP;
    }

    /**
     * Gera app/database/ConnectionFactory.php, usada por todos os Repositories gerados.
     * Lê as constantes DB_HOST, DB_NAME, DB_USER e DB_PASS definidas no config.php.
     *
     * @param bool $comComentarios
     */
    public static function gerarConnectionFactory(bool $comComentarios = false): string {
        $docFactory = "";
        if ($comComentarios) {
            $docFactory = <<<'DOC'

/**
 * Conexão Segura com Banco de Dados via PDO (Padrão Factory / Singleton)
 *
 * Centraliza a inicialização e o compartilhamento da instância PDO:
 * 1. Padrão Singleton: Reutiliza uma única conexão ativa durante toda a requisição;
 * 2. PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION: Dispara exceções para controle rigoroso de falhas;
 * 3. PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC: Retorna dados mapeados diretamente por nome de coluna;
 * 4. Charset utf8mb4: Compatibilidade universal com acentuação e caracteres especiais.
 */
DOC;
        }

        return <<<PHP
<?php

namespace app\\database;

use PDO;
{$docFactory}
class ConnectionFactory {
    private static ?PDO \$connection = null;

    public static function getConnection(): PDO {
        if (self::\$connection === null) {
            \$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            self::\$connection = new PDO(\$dsn, DB_USER, DB_PASS);
            self::\$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::\$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return self::\$connection;
    }
}

PHP;
    }

    public function salvarConnectionFactory(string $caminhoBase, bool $comComentarios = false): string {
        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }
        $arquivoDestino = "{$caminhoBase}/ConnectionFactory.php";
        file_put_contents($arquivoDestino, self::gerarConnectionFactory($comComentarios));
        return $arquivoDestino;
    }

    /**
     * Salva o repositório em app/repositories/
     */
    public function salvarRepositorio(Tabela $tabela, array $atributos, string $chavePrimaria = 'id', string $caminhoBase = __DIR__ . '/../../repositories', bool $comComentarios = false): string {
        $nomeClasse = ucfirst($tabela->getNome_tabelaUC());
        $conteudo = $this->gerarRepositorio($tabela, $atributos, $chavePrimaria, $comComentarios);
        $arquivoDestino = "{$caminhoBase}/{$nomeClasse}Repository.php";

        if (!is_dir($caminhoBase)) {
            mkdir($caminhoBase, 0777, true);
        }

        file_put_contents($arquivoDestino, $conteudo);
        return $arquivoDestino;
    }
}
