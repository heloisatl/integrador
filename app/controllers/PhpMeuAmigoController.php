<?php

namespace app\controllers;

use app\core\Controller;
use app\services\BancoService;
use app\services\TabelaService;
use app\services\AtributoService;
use app\services\SqlImporterService;
use app\database\ConnectionFactory;
use app\tools\SchemaInspector;

/**
 * Class PhpMeuAmigoController
 * 
 * Responsável por gerenciar a interface e os endpoints da API do módulo PHPMeuAmigo.
 * O módulo atua como um protótipo educacional integrado ao DevStudio para modelagem
 * e gerenciamento visual de Bancos de Dados, Tabelas e Atributos.
 * 
 * @package app\controllers
 */
class PhpMeuAmigoController extends Controller
{
    private BancoService $bancoService;
    private TabelaService $tabelaService;
    private AtributoService $atributoService;
    private SqlImporterService $sqlImporterService;

    public function __construct()
    {
        $this->bancoService = new BancoService();
        $this->tabelaService = new TabelaService();
        $this->atributoService = new AtributoService();
        $this->sqlImporterService = new SqlImporterService();
    }

    /**
     * Renderiza a página principal da interface visual do PHPMeuAmigo.
     * Exige que o usuário esteja autenticado na sessão.
     * 
     * @return void
     */
    public function index(): void
    {
        $this->autenticacaoRequired();
        $this->view('projetos/phpmeuamigo');
    }

    /**
     * API Endpoint: Retorna a lista de bancos de dados pertencentes ao usuário logado em formato JSON.
     * 
     * @return void
     */
    public function listarBancos(): void
    {
        $this->autenticacaoJsonRequired();

        try {
            $idUsuario = $_SESSION['usuario_logado']->getIdUsuario();
            
            // Busca todos os bancos cadastrados para o usuário logado
            $bancos = $this->bancoService->getBancoByUsuario($idUsuario);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'bancos' => $bancos ?? []
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao carregar os bancos de dados: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Endpoint: Retorna as tabelas pertencentes a um banco de dados específico.
     * 
     * @return void
     */
    public function listarTabelas(): void
    {
        $this->autenticacaoJsonRequired();

        // Obtém e sanitiza o ID do banco enviado via GET ou POST
        $idBanco = filter_input(INPUT_GET, 'id_banco', FILTER_VALIDATE_INT) 
                ?: filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);

        if (!$idBanco) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Identificador do banco de dados não informado ou inválido.'
            ]);
            return;
        }

        try {
            // Garante que o banco pertence ao usuário logado por segurança
            $banco = $this->bancoService->getBancoById($idBanco);
            $idUsuarioLogado = $_SESSION['usuario_logado']->getIdUsuario();

            if (!$banco || (int)$banco['fk_usuario'] !== (int)$idUsuarioLogado) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Acesso negado ou banco de dados não encontrado.'
                ]);
                return;
            }

            // Busca as tabelas no formato de array associativo
            $tabelas = $this->tabelaService->getTabelasRawByFk_banco($idBanco);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'tabelas' => $tabelas ?? []
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao buscar tabelas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Endpoint: Retorna os atributos (campos) de uma tabela específica.
     * 
     * @return void
     */
    public function listarAtributos(): void
    {
        $this->autenticacaoJsonRequired();

        $idTabela = filter_input(INPUT_GET, 'id_tabela', FILTER_VALIDATE_INT)
                 ?: filter_input(INPUT_POST, 'id_tabela', FILTER_VALIDATE_INT);

        if (!$idTabela) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID da tabela é obrigatório.']);
            return;
        }

        try {
            $atributos = $this->atributoService->getAtributosRawByFk_tabela($idTabela);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'atributos' => $atributos ?? []]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar atributos: ' . $e->getMessage()]);
        }
    }

    /**
     * API Endpoint: Cria ou atualiza as configurações de um banco de dados no mvc_creator.
     * 
     * @return void
     */
    public function salvarBanco(): void
    {
        $this->autenticacaoJsonRequired();

        $idUsuario = $_SESSION['usuario_logado']->getIdUsuario();
        $idBanco = filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);
        $nomeBanco = trim($_POST['nome_banco'] ?? '');
        $usuarioBanco = trim($_POST['usuario_banco'] ?? 'root');
        $senhaBanco = $_POST['senha_banco'] ?? '';
        $host = trim($_POST['host'] ?? 'localhost');
        $porta = trim($_POST['porta'] ?? '3306');

        // Sanitização e Validação do nome do banco
        if (empty($nomeBanco) || !preg_match('/^[a-zA-Z0-9_]+$/', $nomeBanco)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'O nome do banco de dados é inválido ou contém caracteres especiais não permitidos.']);
            return;
        }

        try {
            if ($idBanco) {
                // Atualização de configurações de banco existente
                $banco = $this->bancoService->getBancoById($idBanco);
                if (!$banco || (int)$banco['fk_usuario'] !== (int)$idUsuario) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Banco de dados não encontrado ou sem permissão.']);
                    return;
                }
                $this->bancoService->updateConfig($idBanco, $nomeBanco, $usuarioBanco, $senhaBanco, $host, $porta);
                $mensagem = 'Configurações do banco atualizadas com sucesso!';
            } else {
                // Inserção de novo banco no mvc_creator
                $this->bancoService->insert($idUsuario, $nomeBanco, $usuarioBanco, $senhaBanco, $host, $porta);
                $bancoCriado = $this->bancoService->getBancoEspecifico($nomeBanco, $usuarioBanco, $idUsuario);
                $idBanco = $bancoCriado['id_banco'] ?? null;
                $mensagem = 'Novo banco de dados criado com sucesso!';
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'id_banco' => $idBanco,
                'mensagem' => $mensagem
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar banco de dados: ' . $e->getMessage()]);
        }
    }

    /**
     * API Endpoint: Cria ou atualiza o nome de uma tabela no mvc_creator.
     * 
     * @return void
     */
    public function salvarTabela(): void
    {
        $this->autenticacaoJsonRequired();

        $idTabela = filter_input(INPUT_POST, 'id_tabela', FILTER_VALIDATE_INT);
        $idBanco = filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);
        $nomeTabela = trim($_POST['nome_tabela'] ?? '');

        if (!$idBanco) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID do banco de dados é obrigatório. Por favor, crie ou selecione um banco primeiro.']);
            return;
        }

        if (empty($nomeTabela) || !preg_match('/^[a-zA-Z0-9_]+$/', $nomeTabela)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'O nome da tabela deve conter apenas letras, números e underlines.']);
            return;
        }

        try {
            if ($idTabela) {
                // Atualização de nome da tabela
                $this->tabelaService->updateNome($idTabela, $nomeTabela);
                $mensagem = 'Nome da tabela atualizado com sucesso!';
            } else {
                // Inserção de nova tabela
                $res = $this->tabelaService->insert($nomeTabela, $idBanco);
                $tabelaCriada = $this->tabelaService->getTabelaEspecifica($nomeTabela, $idBanco);
                $idTabela = $tabelaCriada['id_tabela'] ?? null;
                
                // Criação automática do atributo Chave Primária padrão (id_nometabela) para experiência didática
                if ($idTabela) {
                    $nomePk = 'id_' . $nomeTabela;
                    $this->atributoService->insert($idTabela, null, $nomePk, 'INT', 1, 1, 1, 0);
                }

                $mensagem = 'Tabela criada com sucesso!';
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'id_tabela' => $idTabela,
                'mensagem' => $mensagem
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar tabela: ' . $e->getMessage()]);
        }
    }

    /**
     * API Endpoint: Exclui uma tabela e seus atributos associados.
     * 
     * @return void
     */
    public function excluirTabela(): void
    {
        $this->autenticacaoJsonRequired();

        $idTabela = filter_input(INPUT_POST, 'id_tabela', FILTER_VALIDATE_INT);
        if (!$idTabela) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID da tabela não informado.']);
            return;
        }

        try {
            $this->tabelaService->delete($idTabela);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Tabela excluída com sucesso!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir tabela: ' . $e->getMessage()]);
        }
    }

    /**
     * API Endpoint: Cria ou atualiza as propriedades de um atributo (campo) de uma tabela.
     * 
     * @return void
     */
    public function salvarAtributo(): void
    {
        $this->autenticacaoJsonRequired();

        $idAtributo = filter_input(INPUT_POST, 'id_atributo', FILTER_VALIDATE_INT);
        $idTabela = filter_input(INPUT_POST, 'id_tabela', FILTER_VALIDATE_INT);
        $fkAtributo = filter_input(INPUT_POST, 'fk_atributo', FILTER_VALIDATE_INT) ?: null;
        $nomeAtributo = trim($_POST['nome_atributo'] ?? '');
        $tipo = trim($_POST['tipo'] ?? 'VARCHAR(60)');
        $pk = isset($_POST['PK']) && $_POST['PK'] == '1' ? 1 : 0;
        $nn = isset($_POST['NN']) && $_POST['NN'] == '1' ? 1 : 0;
        $ai = isset($_POST['AI']) && $_POST['AI'] == '1' ? 1 : 0;
        $uq = isset($_POST['UQ']) && $_POST['UQ'] == '1' ? 1 : 0;

        if (!$idTabela && !$idAtributo) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID da tabela é obrigatório para novo atributo.']);
            return;
        }

        if (empty($nomeAtributo) || !preg_match('/^[a-zA-Z0-9_]+$/', $nomeAtributo)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Nome de atributo inválido. Use apenas letras, números e underlines.']);
            return;
        }

        try {
            if ($idAtributo) {
                // Atualiza atributo existente
                $this->atributoService->update($idAtributo, $fkAtributo, $nomeAtributo, $tipo, $pk, $nn, $ai, $uq);
                $mensagem = 'Atributo atualizado com sucesso!';
            } else {
                // Insere novo atributo
                $this->atributoService->insert($idTabela, $fkAtributo, $nomeAtributo, $tipo, $pk, $nn, $ai, $uq);
                $mensagem = 'Atributo criado com sucesso!';
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'mensagem' => $mensagem]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar atributo: ' . $e->getMessage()]);
        }
    }

    /**
     * API Endpoint: Exclui um atributo do modelo.
     * 
     * @return void
     */
    public function excluirAtributo(): void
    {
        $this->autenticacaoJsonRequired();

        $idAtributo = filter_input(INPUT_POST, 'id_atributo', FILTER_VALIDATE_INT);
        if (!$idAtributo) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID do atributo não informado.']);
            return;
        }

        try {
            $this->atributoService->delete($idAtributo);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Atributo excluído com sucesso!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir atributo: ' . $e->getMessage()]);
        }
    }

    /**
     * API Endpoint: Conecta ao servidor MySQL local com as credenciais fornecidas e lista os bancos disponíveis.
     * 
     * @return void
     */
    public function conectarMysqlLocal(): void
    {
        $this->autenticacaoJsonRequired();

        $host = trim($_POST['host'] ?? 'localhost');
        $porta = trim($_POST['porta'] ?? '3306');
        $user = trim($_POST['usuario'] ?? 'root');
        $pass = $_POST['senha'] ?? '';

        try {
            $dsn = "mysql:host=$host;port=$porta";
            $pdo = ConnectionFactory::specialConn($dsn, $user, $pass);

            $stm = $pdo->query("SHOW DATABASES");
            $databases = $stm->fetchAll(\PDO::FETCH_COLUMN);

            // Filtrar esquemas padrão de sistema do MySQL
            $esquemasSistema = ['information_schema', 'performance_schema', 'mysql', 'sys'];
            $bancosFiltrados = array_values(array_filter($databases, function ($db) use ($esquemasSistema) {
                return !in_array(strtolower($db), $esquemasSistema);
            }));

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'bancos' => $bancosFiltrados
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao conectar ao MySQL local: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Endpoint: Importa a estrutura de um banco de dados local selecionado usando SchemaInspector.
     * 
     * @return void
     */
    public function importarBancoLocal(): void
    {
        $this->autenticacaoJsonRequired();

        $idUsuario = $_SESSION['usuario_logado']->getIdUsuario();
        $host = trim($_POST['host'] ?? 'localhost');
        $porta = trim($_POST['porta'] ?? '3306');
        $user = trim($_POST['usuario'] ?? 'root');
        $pass = $_POST['senha'] ?? '';
        $bancoSelecionado = trim($_POST['banco_selecionado'] ?? '');

        if (empty($bancoSelecionado)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Por favor, selecione um banco de dados para importar.']);
            return;
        }

        try {
            $dsn = "mysql:host=$host;port=$porta;dbname=$bancoSelecionado";
            $schema = new SchemaInspector($dsn, $user, $pass);

            // 1. Cadastra/registra o banco no mvc_creator
            $this->bancoService->insert($idUsuario, $bancoSelecionado, $user, $pass, $host, $porta);
            $bancoCriado = $this->bancoService->getBancoEspecifico($bancoSelecionado, $user, $idUsuario);

            if (!$bancoCriado) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao registrar o banco de dados no sistema.']);
                return;
            }

            $idBanco = (int)$bancoCriado['id_banco'];
            $tabelas = $schema->getTabelas();
            $totalTabelas = 0;

            // 2. Itera sobre cada tabela e seus atributos
            foreach ($tabelas as $tab) {
                $nomeTabela = $tab[0] ?? null;
                if (!$nomeTabela) continue;

                $this->tabelaService->insert($nomeTabela, $idBanco);
                $tabelaEspecifica = $this->tabelaService->getTabelaEspecifica($nomeTabela, $idBanco);

                if ($tabelaEspecifica) {
                    $idTabela = (int)$tabelaEspecifica['id_tabela'];
                    $colunas = $schema->getAtributos($nomeTabela);

                    foreach ($colunas as $col) {
                        $pk = (isset($col['Key']) && $col['Key'] === 'PRI') ? 1 : 0;
                        $nn = (isset($col['Null']) && $col['Null'] === 'NO') ? 1 : 0;
                        $ai = (isset($col['Extra']) && str_contains(strtolower($col['Extra']), 'auto_increment')) ? 1 : 0;
                        $uq = (isset($col['Key']) && $col['Key'] === 'UNI') ? 1 : 0;

                        $this->atributoService->insert($idTabela, null, $col['Field'], $col['Type'], $pk, $nn, $ai, $uq);
                    }
                    $totalTabelas++;
                }
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'id_banco' => $idBanco,
                'mensagem' => "Banco '$bancoSelecionado' importado com sucesso! $totalTabelas tabela(s) importadas."
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Erro ao importar estrutura do banco local: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Endpoint: Exclui um banco de dados do modelo mvc_creator.
     * 
     * @return void
     */
    public function excluirBanco(): void
    {
        $this->autenticacaoJsonRequired();

        $idUsuario = $_SESSION['usuario_logado']->getIdUsuario();
        $idBanco = filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);

        if (!$idBanco) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Identificador do banco de dados não informado.']);
            return;
        }

        try {
            // Verifica se o banco pertence ao usuário logado
            $banco = $this->bancoService->getBancoById($idBanco);
            if (!$banco || (int)$banco['fk_usuario'] !== (int)$idUsuario) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Banco de dados não encontrado ou permissão negada.']);
                return;
            }

            // Exclui o banco de dados (as tabelas e atributos são excluídos em cascata no mvc_creator)
            $this->bancoService->delete($idBanco);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Banco de dados e sua estrutura foram excluídos do DevStudio!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir banco de dados: ' . $e->getMessage()]);
        }
    }
}
