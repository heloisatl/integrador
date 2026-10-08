<?php
namespace app\controllers;

use app\core\Controller;
use app\services\BancoService;
use app\services\TabelaService;
use app\services\AtributoService;
use app\services\SqlImporterService;
use app\database\ConnectionFactory;
use app\tools\SchemaInspector;

class PhpMeuAmigoController extends Controller{
    private BancoService $bancoService;
    private TabelaService $tabelaService;
    private AtributoService $atributoService;
    private SqlImporterService $sqlImporterService;

    public function __construct(){
        $this->bancoService       = new BancoService();
        $this->tabelaService      = new TabelaService();
        $this->atributoService    = new AtributoService();
        $this->sqlImporterService = new SqlImporterService();
    }

    public function index(): void{
        $this->autenticacaoRequired();
        $this->view('projetos/phpmeuamigo');
    }

    // --- BANCOS ---

    public function listarBancos(): void{
        $this->autenticacaoJsonRequired();

        try {
            $id_usuario = $_SESSION['usuario_logado']->getIdUsuario();
            $bancos = $this->bancoService->getBancoByUsuario($id_usuario);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'bancos' => $bancos ?? []]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao carregar bancos: ' . $e->getMessage()]);
        }
    }

    public function salvarBanco(): void{
        $this->autenticacaoJsonRequired();

        $id_usuario    = $_SESSION['usuario_logado']->getIdUsuario();
        $id_banco      = filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);
        $nome_banco    = trim($_POST['nome_banco'] ?? '');
        $usuario_banco = trim($_POST['usuario_banco'] ?? 'root');
        $senha_banco   = $_POST['senha_banco'] ?? '';
        $host          = trim($_POST['host'] ?? 'localhost');
        $porta         = trim($_POST['porta'] ?? '3306');

        // Nome do banco so aceita letras, numeros e underline
        if (empty($nome_banco) || !preg_match('/^[a-zA-Z0-9_]+$/', $nome_banco)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Nome do banco invalido. Use apenas letras, numeros e underlines.']);
            return;
        }

        try {
            if ($id_banco) {
                $banco = $this->bancoService->getBancoById($id_banco);
                if (!$banco || (int)$banco['fk_usuario'] !== (int)$id_usuario) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Banco nao encontrado ou sem permissao.']);
                    return;
                }
                $this->bancoService->updateConfig($id_banco, $nome_banco, $usuario_banco, $senha_banco, $host, $porta);
                $mensagem = 'Configuracoes atualizadas com sucesso!';
            } else {
                $this->bancoService->insert($id_usuario, $nome_banco, $usuario_banco, $senha_banco, $host, $porta);
                $banco_criado = $this->bancoService->getBancoEspecifico($nome_banco, $usuario_banco, $id_usuario);
                $id_banco = $banco_criado['id_banco'] ?? null;
                $mensagem = 'Banco criado com sucesso!';
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'id_banco' => $id_banco, 'mensagem' => $mensagem]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar banco: ' . $e->getMessage()]);
        }
    }

    public function excluirBanco(): void{
        $this->autenticacaoJsonRequired();

        $id_usuario = $_SESSION['usuario_logado']->getIdUsuario();
        $id_banco   = filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);

        if (!$id_banco) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID do banco nao informado.']);
            return;
        }

        try {
            // Garante que o banco pertence ao usuario logado antes de excluir
            $banco = $this->bancoService->getBancoById($id_banco);
            if (!$banco || (int)$banco['fk_usuario'] !== (int)$id_usuario) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Banco nao encontrado ou permissao negada.']);
                return;
            }

            // Tabelas e atributos sao excluidos em cascata no mvc_creator
            $this->bancoService->delete($id_banco);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Banco e sua estrutura foram excluidos do DevStudio!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir banco: ' . $e->getMessage()]);
        }
    }

    // --- TABELAS ---

    public function listarTabelas(): void{
        $this->autenticacaoJsonRequired();

        $id_banco = filter_input(INPUT_GET, 'id_banco', FILTER_VALIDATE_INT)
                 ?: filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);

        if (!$id_banco) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID do banco nao informado ou invalido.']);
            return;
        }

        try {
            // Garante que o banco pertence ao usuario logado
            $banco = $this->bancoService->getBancoById($id_banco);
            $id_usuario = $_SESSION['usuario_logado']->getIdUsuario();

            if (!$banco || (int)$banco['fk_usuario'] !== (int)$id_usuario) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado ou banco nao encontrado.']);
                return;
            }

            $tabelas = $this->tabelaService->getTabelasRawByFk_banco($id_banco);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'tabelas' => $tabelas ?? []]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar tabelas: ' . $e->getMessage()]);
        }
    }

    public function salvarTabela(): void{
        $this->autenticacaoJsonRequired();

        $id_tabela   = filter_input(INPUT_POST, 'id_tabela', FILTER_VALIDATE_INT);
        $id_banco    = filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);
        $nome_tabela = trim($_POST['nome_tabela'] ?? '');

        if (!$id_banco) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID do banco e obrigatorio. Crie ou selecione um banco primeiro.']);
            return;
        }

        if (empty($nome_tabela) || !preg_match('/^[a-zA-Z0-9_]+$/', $nome_tabela)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Nome da tabela deve conter apenas letras, numeros e underlines.']);
            return;
        }

        try {
            if ($id_tabela) {
                $this->tabelaService->updateNome($id_tabela, $nome_tabela);
                $mensagem = 'Nome da tabela atualizado!';
            } else {
                $this->tabelaService->insert($nome_tabela, $id_banco);
                $tabela_criada = $this->tabelaService->getTabelaEspecifica($nome_tabela, $id_banco);
                $id_tabela = $tabela_criada['id_tabela'] ?? null;

                // Cria automaticamente a PK padrao (id_nometabela) para facilitar a modelagem inicial
                if ($id_tabela) {
                    $this->atributoService->insert($id_tabela, null, 'id_' . $nome_tabela, 'int', 1, 1, 1, 0);
                }

                $mensagem = 'Tabela criada com sucesso!';
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'id_tabela' => $id_tabela, 'mensagem' => $mensagem]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar tabela: ' . $e->getMessage()]);
        }
    }

    public function excluirTabela(): void{
        $this->autenticacaoJsonRequired();

        $id_tabela = filter_input(INPUT_POST, 'id_tabela', FILTER_VALIDATE_INT);
        if (!$id_tabela) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID da tabela nao informado.']);
            return;
        }

        try {
            $this->tabelaService->delete($id_tabela);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Tabela excluida com sucesso!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir tabela: ' . $e->getMessage()]);
        }
    }

    // --- ATRIBUTOS ---

    public function listarAtributos(): void{
        $this->autenticacaoJsonRequired();

        $id_tabela = filter_input(INPUT_GET, 'id_tabela', FILTER_VALIDATE_INT)
                  ?: filter_input(INPUT_POST, 'id_tabela', FILTER_VALIDATE_INT);
        $id_banco  = filter_input(INPUT_GET, 'id_banco', FILTER_VALIDATE_INT)
                  ?: filter_input(INPUT_POST, 'id_banco', FILTER_VALIDATE_INT);

        if (!$id_tabela && !$id_banco) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID da tabela ou ID do banco e obrigatorio.']);
            return;
        }

        try {
            if ($id_tabela) {
                $atributos = $this->atributoService->getAtributosRawByFk_tabela($id_tabela);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['sucesso' => true, 'atributos' => $atributos ?? []]);
                return;
            }

            // Retorna os atributos de todas as tabelas do banco ativo para alimentar o select didático de FK
            $tabelas = $this->tabelaService->getTabelasRawByFk_banco($id_banco);
            $candidatos = [];
            foreach ($tabelas as $tab) {
                $attrsTab = $this->atributoService->getAtributosRawByFk_tabela((int)$tab['id_tabela']);
                foreach ($attrsTab as $a) {
                    $candidatos[] = [
                        'id_atributo'   => (int)$a['id_atributo'],
                        'nome_atributo' => $a['nome_atributo'],
                        'id_tabela'     => (int)$tab['id_tabela'],
                        'nome_tabela'   => $tab['nome_tabela'],
                        'PK'            => (int)($a['PK'] ?? 0)
                    ];
                }
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'atributos_banco' => $candidatos]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao listar atributos: ' . $e->getMessage()]);
        }
    }

    public function salvarAtributo(): void{
        $this->autenticacaoJsonRequired();

        $id_atributo   = filter_input(INPUT_POST, 'id_atributo', FILTER_VALIDATE_INT);
        $id_tabela     = filter_input(INPUT_POST, 'id_tabela', FILTER_VALIDATE_INT);
        $rawFk         = trim((string)($_POST['fk_atributo'] ?? ''));
        $fk_atributo   = ($rawFk !== '' && $rawFk !== '0') ? filter_var($rawFk, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) : null;
        $nome_atributo = trim($_POST['nome_atributo'] ?? '');
        $tipo          = trim($_POST['tipo'] ?? 'varchar(60)');
        if (is_string($tipo)) {
            $tipo = strtolower($tipo);
        }
        $PK = isset($_POST['PK']) && $_POST['PK'] == '1' ? 1 : 0;
        $NN = isset($_POST['NN']) && $_POST['NN'] == '1' ? 1 : 0;
        $AI = isset($_POST['AI']) && $_POST['AI'] == '1' ? 1 : 0;
        $UQ = isset($_POST['UQ']) && $_POST['UQ'] == '1' ? 1 : 0;

        if (!$id_tabela && !$id_atributo) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID da tabela e obrigatorio para novo atributo.']);
            return;
        }

        if (empty($nome_atributo) || !preg_match('/^[a-zA-Z0-9_]+$/', $nome_atributo)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Nome do atributo invalido. Use apenas letras, numeros e underlines.']);
            return;
        }

        // Se informou formato invalido na FK
        if ($rawFk !== '' && $rawFk !== '0' && $fk_atributo === null) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'O identificador da Foreign Key deve ser um numero inteiro valido.']);
            return;
        }

        // Se informou FK, valida existencia da PK de destino e isolamento do mesmo banco
        if ($fk_atributo !== null) {
            if ($id_atributo && (int)$fk_atributo === (int)$id_atributo) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Um atributo nao pode referenciar a si mesmo como Foreign Key.']);
                return;
            }

            $attrDestino = $this->atributoService->getAtributoById($fk_atributo);
            if (!$attrDestino) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['sucesso' => false, 'mensagem' => "A Primary Key / atributo referenciado (ID $fk_atributo) nao existe no sistema."]);
                return;
            }

            // Garante que ambos pertencem ao mesmo banco de dados
            $tabelaOrigemId = $id_tabela;
            if (!$tabelaOrigemId && $id_atributo) {
                $attrAtual = $this->atributoService->getAtributoById($id_atributo);
                $tabelaOrigemId = $attrAtual ? (int)$attrAtual['fk_tabela'] : null;
            }

            if ($tabelaOrigemId) {
                $tabOrigem  = $this->tabelaService->getTabelaById($tabelaOrigemId);
                $tabDestino = $this->tabelaService->getTabelaById((int)$attrDestino['fk_tabela']);
                if ($tabOrigem && $tabDestino && (int)$tabOrigem['fk_banco'] !== (int)$tabDestino['fk_banco']) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Nao e permitido vincular Foreign Keys entre bancos de dados diferentes.']);
                    return;
                }
            }
        }

        try {
            if ($id_atributo) {
                $this->atributoService->update($id_atributo, $fk_atributo, $nome_atributo, $tipo, $PK, $NN, $AI, $UQ);
                $mensagem = 'Atributo atualizado com sucesso!';
            } else {
                $inserted = $this->atributoService->insert($id_tabela, $fk_atributo, $nome_atributo, $tipo, $PK, $NN, $AI, $UQ);
                if (!$inserted) {
                    // Se houve colisão de nome padrão no banco, busca automaticamente o próximo número disponível
                    if (preg_match('/^novo_atributo(\d+)$/', $nome_atributo, $matches)) {
                        $num = (int)$matches[1];
                        for ($i = $num + 1; $i <= $num + 50; $i++) {
                            $tentativaNome = 'novo_atributo' . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
                            $inserted = $this->atributoService->insert($id_tabela, $fk_atributo, $tentativaNome, $tipo, $PK, $NN, $AI, $UQ);
                            if ($inserted) {
                                break;
                            }
                        }
                    }
                }

                if (!$inserted) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['sucesso' => false, 'mensagem' => "Ja existe um atributo com o nome '$nome_atributo' nesta tabela."]);
                    return;
                }
                $mensagem = 'Atributo criado com sucesso!';
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'mensagem' => $mensagem]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar atributo: ' . $e->getMessage()]);
        }
    }

    public function excluirAtributo(): void{
        $this->autenticacaoJsonRequired();

        $id_atributo = filter_input(INPUT_POST, 'id_atributo', FILTER_VALIDATE_INT);
        if (!$id_atributo) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID do atributo nao informado.']);
            return;
        }

        try {
            $this->atributoService->delete($id_atributo);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Atributo excluido com sucesso!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir atributo: ' . $e->getMessage()]);
        }
    }

    // --- IMPORTACAO DE BANCO LOCAL ---

    public function conectarMysqlLocal(): void{
        $this->autenticacaoJsonRequired();

        $host  = trim($_POST['host'] ?? 'localhost');
        $porta = trim($_POST['porta'] ?? '3306');
        $user  = trim($_POST['usuario'] ?? 'root');
        $pass  = $_POST['senha'] ?? '';

        try {
            $dsn = "mysql:host=$host;port=$porta";
            $pdo = ConnectionFactory::specialConn($dsn, $user, $pass);

            $stm = $pdo->query("SHOW DATABASES");
            $databases = $stm->fetchAll(\PDO::FETCH_COLUMN);

            // Filtra os schemas internos do MySQL
            $esquemas_sistema = ['information_schema', 'performance_schema', 'mysql', 'sys'];
            $bancos_filtrados = array_values(array_filter($databases, function($db) use ($esquemas_sistema) {
                return !in_array(strtolower($db), $esquemas_sistema);
            }));

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => true, 'bancos' => $bancos_filtrados]);
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao conectar ao MySQL local: ' . $e->getMessage()]);
        }
    }

    public function importarBancoLocal(): void{
        $this->autenticacaoJsonRequired();

        $id_usuario        = $_SESSION['usuario_logado']->getIdUsuario();
        $host              = trim($_POST['host'] ?? 'localhost');
        $porta             = trim($_POST['porta'] ?? '3306');
        $user              = trim($_POST['usuario'] ?? 'root');
        $pass              = $_POST['senha'] ?? '';
        $banco_selecionado = trim($_POST['banco_selecionado'] ?? '');

        if (empty($banco_selecionado)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione um banco de dados para importar.']);
            return;
        }

        try {
            $dsn    = "mysql:host=$host;port=$porta;dbname=$banco_selecionado";
            $schema = new SchemaInspector($dsn, $user, $pass);

            // Registra o banco no mvc_creator se ainda nao existir
            $banco_existente = $this->bancoService->getBancoEspecifico($banco_selecionado, $user, $id_usuario);
            if (!$banco_existente) {
                $this->bancoService->insert($id_usuario, $banco_selecionado, $user, $pass, $host, $porta);
                $banco_criado = $this->bancoService->getBancoEspecifico($banco_selecionado, $user, $id_usuario);
            } else {
                $banco_criado = $banco_existente;
            }

            if (!$banco_criado) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao registrar o banco no sistema.']);
                return;
            }

            $id_banco = (int)$banco_criado['id_banco'];
            $tabelas  = $schema->getTabelas();

            // =========================================================================
            // ETAPA 1: CRIAÇÃO E INSERÇÃO DAS TABELAS E SEUS ATRIBUTOS BASE
            // (Insere todos os atributos com fk_atributo = null para que todos os IDs existam antes das FKs)
            // =========================================================================
            $total_tabelas_processadas = 0;

            foreach ($tabelas as $tab) {
                $nome_tabela = $tab[0] ?? null;
                if (!$nome_tabela) continue;

                // 1.1 Se a tabela ja existe no DevStudio, recupera; se nao, cria
                $tabela_especifica = $this->tabelaService->getTabelaEspecifica($nome_tabela, $id_banco);
                if (!$tabela_especifica) {
                    $this->tabelaService->insert($nome_tabela, $id_banco);
                    $tabela_especifica = $this->tabelaService->getTabelaEspecifica($nome_tabela, $id_banco);
                }

                if (!$tabela_especifica) continue;

                $id_tabela = (int)$tabela_especifica['id_tabela'];

                // 1.2 Mapeia atributos ja existentes nesta tabela no banco do sistema (para pular os ja criados)
                $atributosExistentes = $this->atributoService->getAtributosRawByFk_tabela($id_tabela);
                $nomesExistentes     = array_column($atributosExistentes, 'id_atributo', 'nome_atributo');

                // 1.3 Puxa as colunas fisicas inspecionadas do MySQL
                $colunas = $schema->getAtributos($nome_tabela);

                foreach ($colunas as $col) {
                    $nomeCampo = $col['Field'];

                    // Pula o atributo se ele ja estiver cadastrado no sistema
                    if (isset($nomesExistentes[$nomeCampo])) {
                        continue;
                    }

                    $tipo = $col['Type'] ?? 'varchar(60)';
                    if (is_string($tipo)) {
                        $tipo = strtolower(trim($tipo));
                    }

                    $PK = (isset($col['Key'])   && $col['Key']  === 'PRI') ? 1 : 0;
                    $NN = (isset($col['Null'])  && $col['Null'] === 'NO')  ? 1 : 0;
                    $AI = (isset($col['Extra']) && str_contains(strtolower($col['Extra']), 'auto_increment')) ? 1 : 0;
                    $UQ = (isset($col['Key'])   && $col['Key']  === 'UNI') ? 1 : 0;

                    // Cria o atributo SEM fk_atributo por enquanto (garante insercao sem violacao de FK)
                    $this->atributoService->insert($id_tabela, null, $nomeCampo, $tipo, $PK, $NN, $AI, $UQ);
                }

                $total_tabelas_processadas++;
            }

            // =========================================================================
            // ETAPA 2: PROCESSAMENTO E MAPEAMENTO DE CHAVES ESTRANGEIRAS (FOREIGN KEYS)
            // (Executado apenas apos todas as tabelas e dados base estarem garantidos no banco)
            // =========================================================================
            $total_fks_resolvidas = 0;

            foreach ($tabelas as $tab) {
                $nome_tabela_origem = $tab[0] ?? null;
                if (!$nome_tabela_origem) continue;

                // 2.1 Busca todas as FKs da tabela de origem no banco inspecionado
                $fks = $schema->getReferenciaFk($nome_tabela_origem);
                if (empty($fks)) continue;

                // 2.2 Localiza a entidade de origem no sistema
                $tabelaOrigem = $this->tabelaService->getTabelaEspecifica($nome_tabela_origem, $id_banco);
                if (!$tabelaOrigem) continue;

                // Carrega atributos atuais da tabela de origem indexados pelo nome (em minusculo)
                $atributosOrigem = $this->atributoService->getAtributosRawByFk_tabela($tabelaOrigem['id_tabela']);
                $mapOrigem       = [];
                foreach ($atributosOrigem as $a) {
                    $mapOrigem[strtolower($a['nome_atributo'])] = $a;
                }

                // 2.3 Resolve e vincula cada chave estrangeira
                foreach ($fks as $fk) {
                    $colunaOrigem  = $fk['COLUMN_NAME'];            // Atributo local que recebe a chave (ex: id_cliente em pedido)
                    $tabelaDestino = $fk['REFERENCED_TABLE_NAME'];   // Entidade referenciada (ex: cliente)
                    $colunaDestino = $fk['REFERENCED_COLUMN_NAME'];  // Atributo referenciado na entidade destino (ex: id_cliente)

                    $keyOrigem = strtolower($colunaOrigem);
                    // Valida se o atributo de origem existe no sistema
                    if (!isset($mapOrigem[$keyOrigem])) {
                        continue;
                    }

                    $attrOrigem = $mapOrigem[$keyOrigem];

                    // 2.4 Identifica a qual entidade (tabela destino) esse atributo se refere
                    $tabelaDestinoModel = $this->tabelaService->getTabelaEspecifica($tabelaDestino, $id_banco);
                    if (!$tabelaDestinoModel) {
                        continue;
                    }

                    // 2.5 Localiza o atributo correspondente na entidade referenciada
                    $atributosDestino  = $this->atributoService->getAtributosRawByFk_tabela($tabelaDestinoModel['id_tabela']);
                    $idAtributoDestino = null;
                    foreach ($atributosDestino as $attrDest) {
                        if (strcasecmp($attrDest['nome_atributo'], $colunaDestino) === 0) {
                            $idAtributoDestino = (int)$attrDest['id_atributo'];
                            break;
                        }
                    }

                    if (!$idAtributoDestino) {
                        continue;
                    }

                    // 2.6 Validacao de estado: se a FK ja esta associada corretamente, pula
                    if (!empty($attrOrigem['fk_atributo']) && (int)$attrOrigem['fk_atributo'] === $idAtributoDestino) {
                        continue;
                    }

                    // 2.7 Guarda a chave/associacao no sistema de forma segura
                    $this->atributoService->updateFkAtributo($attrOrigem['id_atributo'], $idAtributoDestino);
                    $total_fks_resolvidas++;
                }
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso'  => true,
                'id_banco' => $id_banco,
                'mensagem' => "Banco '$banco_selecionado' importado com sucesso! $total_tabelas_processadas tabela(s) processadas e $total_fks_resolvidas chave(s) estrangeira(s) vinculadas."
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao importar banco local: ' . $e->getMessage()]);
        }
    }

    public function importarSql(): void{
        $this->autenticacaoJsonRequired();
        // Endpoint reservado para futura importacao via arquivo .sql
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => false, 'mensagem' => 'Use a importacao direta pelo MySQL local.']);
    }
}
