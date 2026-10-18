<?php
namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Projeto;
use app\models\Tabela;
use app\models\Atributo;
use app\services\ProjetoService;
use app\services\BancoService;
use app\services\TabelaService;
use app\services\AtributoService;

use app\tools\SchemaInspector;
use app\services\UsuarioService;

class ProjetoController extends Controller{
    private ProjetoService $projetoService;

    public function __construct(){
        $this->projetoService = new ProjetoService();
    }


    public function getDatabases(){
        if (empty($_SESSION['usuario_logado'])) {
            echo '<option value="">Sessão expirada. Faça login novamente.</option>';
            return;
        }
        $sel = trim($_POST['selecionado'] ?? null);
        echo (new BancoService())->getBancoByUsuario($_SESSION['usuario_logado']->getIdUsuario(),"DB_OPTIONS",$sel);
    }

    public function getTabelas(): void {
        $this->autenticacaoJsonRequired();

        $user = trim($_POST['usuario'] ?? '');
        $pass = trim($_POST['senha'] ?? '');
        $server = trim($_POST['servidor'] ?? 'localhost');
        $banco = trim($_POST['banco'] ?? '');


        try {
            $tabelaService = new TabelaService();
            $tabelas = $tabelaService->getTabelasByFk_banco($banco,'prox_etap');
            echo json_encode(['sucesso' => true, 'tabelas' => $tabelas]);
        } catch (\Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function TesteLeituraDeBanco(){
        $bancoService = new BancoService();
        $tabelaService = new TabelaService();
        $atributoService = new AtributoService();

        $bancoSelecionado = $bancoService->getBancoById(2);
        $tabelas = $tabelaService->getTabelasByFk_banco($bancoSelecionado['id_banco']);
        $atributos = [];
        foreach ($tabelas as $tabela) {
            $atributos[] = $atributoService->getAtributosByFk_tabela($tabela->getId_tabela());
            
        }
        
        
        
    }

    public function testeInserirBanco(){
        $host = '127.0.0.1:3306';
        $hostEporta = explode(':',$host);
        $nome_banco = 'db_projeto_integrador';
        $usuario_banco = 'root';
        $senha_banco = 'bancodedados';
        $fk_usuario = $_SESSION['usuario_logado']->getIdUsuario();
        
        // SchemaInspector com o Banco Selecionado
        $schema = new SchemaInspector("mysql:host=$host;dbname=$nome_banco",$usuario_banco,$senha_banco);

        // Insercao do Banco
        $bancoService = new BancoService();
        $bancoService->insert($fk_usuario,$nome_banco,$usuario_banco,$senha_banco,$hostEporta[0],$hostEporta[1]);
        $bancoEspecifico = $bancoService->getBancoEspecifico($nome_banco,$usuario_banco,$fk_usuario);
        // Insercao da Tabela e atributos
        $tabelaService = new TabelaService();
        $tabelas = $schema->getTabelas();
        
        
        $atributoService = new AtributoService();
        foreach($tabelas as $key => $value){
            print "Inserindo a tabela ".$value[0]." do banco ".$nome_banco."<br>";
            $tabelaService->insert($value[0],$bancoEspecifico['id_banco']);
            $tabelaEspecifica = $tabelaService->getTabelaEspecifica($value[0],$bancoEspecifico['id_banco']);
            
            foreach($schema->getAtributos($value[0]) as $att){
                $pk = $att['Key']=="PRI" ? 1 : 0;
                $nn = $att['Null']=="NO" ? 1 : 0;
                print $tabelaEspecifica['id_tabela']." - ".$att['Field']." - ".$att['Type']." - ".$pk." - ".$nn."<br>";
                $atributoService->insert($tabelaEspecifica['id_tabela'],null,$att['Field'],$att['Type'],$pk,$nn,0,0);
            }
        }
            print "O Banco ".$nome_banco. " foi inserido com sucesso!";
    }

    public function gerarMvc(): void {
        $this->autenticacaoJsonRequired();

        $bancoService = new BancoService();
        $tabelaService = new TabelaService();
        $atributoService = new AtributoService();
        $nomeProjeto = trim($_POST['nomeProjeto'] ?? '') ?: 'meu_projeto';
        $user = trim($_POST['usuario'] ?? '');
        $pass = trim($_POST['senha'] ?? '');
        $server = trim($_POST['servidor'] ?? 'localhost');
        $banco = trim($_POST['banco'] ?? '');

        if (preg_match('/\s/', $nomeProjeto)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'O nome do projeto não deve conter espaços.']);
            return;
        }

        if (empty($banco)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione um banco de dados antes de gerar o sistema.']);
            return;
        }

        
        
        $bancoSelecionado = $bancoService->getBancoById($banco);
        if (!$bancoSelecionado) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Banco de dados não encontrado.']);
            return;
        }

        if (empty($_SESSION['usuario_logado'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Sessão expirada. Faça login novamente.']);
            return;
        }

        $projeto = new Projeto(
            $_SESSION['usuario_logado']->getIdUsuario(),
            (int) $bancoSelecionado['id_banco'],
            null,
            $nomeProjeto,
            date('Y-m-d H:i:s'),
            ['comentarios' => 0, 'views' => 0],
            null
        );

        $resultadoInsert = $this->projetoService->insert($projeto);
        if (!$resultadoInsert) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível salvar o projeto no banco de dados.']);
            return;
        }

        $tabelas = $tabelaService->getTabelasByFk_banco($bancoSelecionado['id_banco']);
        $pastaOutput = __DIR__ . '/../projetos/temp/' . $nomeProjeto;
        if(!is_dir($pastaOutput)){
            mkdir($pastaOutput, 0755, true);
        }
        $pastaApp = $pastaOutput . '/app';
        if(!is_dir($pastaApp)){
            mkdir($pastaApp, 0755, true);
        }
        $pastaPublic = $pastaOutput . '/public';
        if(!is_dir($pastaPublic)){
            mkdir($pastaPublic, 0755, true);
        }
        $nomeZip = $nomeProjeto .'-' . $resultadoInsert .'.zip';
        $pastaDownloads = __DIR__ . '/../projetos/downloads/';
        if(!is_dir($pastaDownloads)){
            mkdir($pastaDownloads, 0755, true);
        }
        $arquivoZip = $pastaDownloads . $nomeZip;
        try {
            

            $gerenciador = new \app\tools\gerador\GerenciadorGerador();
            $geradorIndex = new \app\tools\gerador\geradorIndex();
            
            foreach ($tabelas as $tabela) {
                $atributos = $atributoService->getAtributosByFk_tabela($tabela->getId_tabela());
                $chavePrimaria = 'id';
                foreach($atributos as $key => $att){
                   
                    if($att->getPk()){
                        $chavePrimaria = $att->getNome_atributo();
                        break ; // Sai dos dois loops
                    }
                    
                }
                // Desativado a geração direta no app/ do projeto ativo:
                // $gerenciador->gerarTudo($tabela, $atributos, $chavePrimaria);

                // Copiar também para a pasta de exportação ZIP
                $geradorModel = new \app\tools\gerador\GeradorModel();
                $geradorRepo = new \app\tools\gerador\GeradorRepositorio();
                $geradorCtrl = new \app\tools\gerador\GeradorController();
                $geradorView = new \app\tools\gerador\GeradorView();
                $geradorCore = new \app\tools\gerador\GeradorCore();
                $geradorConfig = new \app\tools\gerador\GeradorConfig();

                $geradorModel->salvarModel($tabela, $atributos, $pastaApp . '/models');
                $geradorRepo->salvarRepositorio($tabela, $atributos, $chavePrimaria, $pastaApp . '/repositories');
                $geradorCtrl->salvarController($tabela, $atributos, $chavePrimaria, $pastaApp . '/controllers');
                $geradorView->salvarViews($tabela, $atributos, $chavePrimaria, $pastaApp . '/views');
                $geradorCore->salvarCore($pastaApp.'/core');

            }
            
            $geradorIndex->salvarIndex($tabelas, $pastaPublic);

            // Criar arquivo .zip para download
            $geradorZip = new \app\tools\gerador\GeradorZip();
            $geradorZip->compactarPasta($pastaOutput, $arquivoZip);

            // Apaga a pasta temporária de compilação após gerar o ZIP
            $this->excluirDiretorioRecursivo($pastaOutput);

                $downloadUrl = URL_BASE . '/projetos/downloadZip?file=' . urlencode($nomeZip);
            $this->registrarProjetoNoHistorico($nomeProjeto, 'MVC Creator', [
                'nome' => $nomeProjeto,
                'arquivo' => $nomeZip,
                'downloadUrl' => $downloadUrl,
                'banco' => $bancoSelecionado['nome_banco'] ?? $bancoSelecionado['nome'] ?? $banco,
                'banco_id' => $bancoSelecionado['id_banco'] ?? $banco,
                'gerado_em' => date('c'),
                'tipo' => 'MVC Creator',
            ]);

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Sistema MVC gerado com sucesso!',
                'downloadUrl' => $downloadUrl
            ]);
        } catch (\Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    private function excluirDiretorioRecursivo(string $dir): void {
        if (!file_exists($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->excluirDiretorioRecursivo($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function downloadZip(): void {
        $nomeZip = basename($_GET['file'] ?? '');
        $caminhoZip = __DIR__ . '/../projetos/downloads/' . $nomeZip;   
        $geradorZip = new \app\tools\gerador\GeradorZip();
        $geradorZip->enviarDownload($caminhoZip, $nomeZip);
    }

    private function getHistoricoUsuarioId(): int {
        $usuario = $_SESSION['usuario_logado'] ?? null;
        if (!$usuario || !method_exists($usuario, 'getIdUsuario')) {
            throw new \RuntimeException('É necessário estar autenticado para acessar o histórico.');
        }

        $usuarioId = (int) $usuario->getIdUsuario();
        if ($usuarioId < 1) {
            throw new \RuntimeException('A conta autenticada não possui um identificador válido.');
        }

        return $usuarioId;
    }

    private function getHistoricoStoragePath(?int $usuarioId = null): string {
        $usuarioId = $usuarioId ?? $this->getHistoricoUsuarioId();
        return __DIR__ . '/../projetos/historicos/usuario_' . $usuarioId . '.json';
    }

    private function migrarHistoricoCompartilhadoAdmin(string $caminhoHistoricoUsuario): void {
        $usuario = $_SESSION['usuario_logado'] ?? null;
        $caminhoLegado = __DIR__ . '/../projetos/historico.json';
        $caminhoMigrado = __DIR__ . '/../projetos/historico_compartilhado_migrado.json';

        if (!$usuario || !method_exists($usuario, 'getTipoPerfil')
            || strtolower($usuario->getTipoPerfil()) !== 'admin'
            || !is_file($caminhoLegado)
            || is_file($caminhoMigrado)) {
            return;
        }

        $historicoAdmin = $this->lerJsonArrayValido($caminhoHistoricoUsuario);
        $historicoLegado = $this->lerJsonArrayValido($caminhoLegado);
        $idsExistentes = array_column($historicoAdmin, 'id');

        foreach ($historicoLegado as $item) {
            if (!in_array($item['id'] ?? '', $idsExistentes, true)) {
                $historicoAdmin[] = $item;
            }
        }

        $historicoAdmin = $this->ordenarPorData($historicoAdmin);
        $historicoAdmin = array_slice($historicoAdmin, 0, 10);
        $this->salvarJsonArrayValido($caminhoHistoricoUsuario, $historicoAdmin);

        if (!@rename($caminhoLegado, $caminhoMigrado)) {
            @copy($caminhoLegado, $caminhoMigrado);
        }
    }

    private function inicializarArquivoJson(string $caminho): void {
        if (file_exists($caminho)) {
            return;
        }

        $diretorio = dirname($caminho);
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0775, true);
        }

        file_put_contents($caminho, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function normalizarItemHistorico(array $item): array {
        $id = trim((string)($item['id'] ?? ''));
        if ($id === '') {
            $id = 'proj_' . substr(hash('sha256', uniqid((string) microtime(true), true)), 0, 12);
        }

        $nome = trim((string)($item['nome'] ?? 'Projeto sem nome'));
        if ($nome === '') {
            $nome = 'Projeto sem nome';
        }

        $modulo = trim((string)($item['modulo'] ?? ''));
        if (!in_array($modulo, ['PageMaker', 'MVC Creator'], true)) {
            $modulo = 'MVC Creator';
        }

        $criadoEm = $this->converterStringEmData($item['criado_em'] ?? $item['created_at'] ?? null);
        if ($criadoEm === null) {
            $criadoEm = date('c');
        }

        $conteudo = is_array($item['conteudo'] ?? null) ? $item['conteudo'] : [];
        if ($modulo === 'PageMaker' && empty($conteudo['css']) && is_string($conteudo['html'] ?? null)) {
            if (preg_match('/<style\\b[^>]*>(.*?)<\\/style>/is', $conteudo['html'], $correspondencia)) {
                $conteudo['css'] = trim($correspondencia[1]);
            }
        }

        return [
            'id' => $id,
            'nome' => $nome,
            'modulo' => $modulo,
            'criado_em' => $criadoEm,
            'usuario_id' => (int) ($item['usuario_id'] ?? $this->getHistoricoUsuarioId()),
            'conteudo' => $conteudo,
        ];
    }

    private function converterStringEmData($valor): ?string {
        if (!is_string($valor) || trim($valor) === '') {
            return null;
        }

        try {
            $data = new \DateTimeImmutable($valor);
            return $data->format('c');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function lerJsonArrayValido(string $caminho): array {
        $this->inicializarArquivoJson($caminho);
        $usuarioId = $this->getHistoricoUsuarioId();

        $conteudo = file_get_contents($caminho);
        if ($conteudo === false || trim($conteudo) === '') {
            file_put_contents($caminho, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return [];
        }

        $json = json_decode($conteudo, true);
        if (!is_array($json)) {
            file_put_contents($caminho, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return [];
        }

        $itensValidos = [];
        foreach ($json as $item) {
            if (!is_array($item)) {
                continue;
            }
            $itemNormalizado = $this->normalizarItemHistorico($item);
            if ($itemNormalizado['usuario_id'] === $usuarioId) {
                $itensValidos[] = $itemNormalizado;
            }
        }

        return array_values($itensValidos);
    }

    private function salvarJsonArrayValido(string $caminho, array $itens): void {
        $this->inicializarArquivoJson($caminho);

        $dados = [];
        foreach ($itens as $item) {
            if (is_array($item)) {
                $itemNormalizado = $this->normalizarItemHistorico($item);
                if ($itemNormalizado['usuario_id'] === $this->getHistoricoUsuarioId()) {
                    $dados[] = $itemNormalizado;
                }
            }
        }

        $resultado = file_put_contents($caminho, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($resultado === false) {
            throw new \RuntimeException('Não foi possível salvar os dados do histórico em JSON.');
        }
    }

    private function ordenarPorData(array $itens): array {
        usort($itens, function (array $a, array $b): int {
            $dataA = $this->converterStringEmData($a['criado_em'] ?? null) ?? date('c');
            $dataB = $this->converterStringEmData($b['criado_em'] ?? null) ?? date('c');
            return strtotime($dataB) <=> strtotime($dataA);
        });

        return $itens;
    }

    private function sincronizarHistoricoEArquivos(): array {
        $caminhoHistoricoUsuario = $this->getHistoricoStoragePath();
        $this->migrarHistoricoCompartilhadoAdmin($caminhoHistoricoUsuario);
        $historico = $this->lerJsonArrayValido($caminhoHistoricoUsuario);

        $historico = $this->ordenarPorData($historico);
        $historico = array_slice($historico, 0, 10);
        $this->salvarJsonArrayValido($caminhoHistoricoUsuario, $historico);
        return $historico;
    }

    private function obterItemPorId(string $id): ?array {
        foreach ($this->sincronizarHistoricoEArquivos() as $item) {
            if (($item['id'] ?? null) === $id) {
                return $item;
            }
        }

        return null;
    }

    private function removerItemPorId(array &$lista, string $id): ?array {
        foreach ($lista as $indice => $item) {
            if (($item['id'] ?? null) === $id) {
                unset($lista[$indice]);
                return $this->normalizarItemHistorico($item);
            }
        }

        return null;
    }

    private function registrarProjetoNoHistorico(string $nome, string $modulo, array $conteudo = []): array {
        $nome = trim((string) $nome);
        $modulo = trim((string) $modulo);

        if ($nome === '') {
            return ['sucesso' => false, 'mensagem' => 'Informe o nome do projeto para registrar no histórico.'];
        }

        if (!in_array($modulo, ['PageMaker', 'MVC Creator'], true)) {
            return ['sucesso' => false, 'mensagem' => 'Módulo inválido para o histórico.'];
        }

        $historico = $this->sincronizarHistoricoEArquivos();

        $novoItem = [
            'id' => 'proj_' . substr(hash('sha256', uniqid((string) microtime(true), true)), 0, 12),
            'nome' => $nome,
            'modulo' => $modulo,
            'criado_em' => date('c'),
            'usuario_id' => $this->getHistoricoUsuarioId(),
            'conteudo' => $conteudo,
        ];
        $historico[] = $novoItem;
        $historico = $this->ordenarPorData($historico);
        $historico = array_slice($historico, 0, 10);
        $this->salvarJsonArrayValido($this->getHistoricoStoragePath(), $historico);

        return ['sucesso' => true, 'item' => $novoItem];
    }

    public function registrarProjetoHistoricoJson(): void {
        $this->autenticacaoJsonRequired();

        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados)) {
            $dados = $_POST;
        }

        $nome = trim((string)($dados['nome'] ?? ''));
        $modulo = trim((string)($dados['modulo'] ?? ''));
        $conteudo = $dados['conteudo'] ?? [];

        if (!is_array($conteudo)) {
            $conteudo = [];
        }

        header('Content-Type: application/json');
        echo json_encode($this->registrarProjetoNoHistorico($nome, $modulo, $conteudo));
        exit();
    }

    public function listarHistoricoJson(): void {
        $this->autenticacaoJsonRequired();
        $historico = $this->sincronizarHistoricoEArquivos();

        header('Content-Type: application/json');
        echo json_encode([
            'sucesso' => true,
            'historico' => $historico,
        ]);
        exit();
    }

    public function baixarProjetoHistorico(): void {
        $this->autenticacaoRequired();

        $id = trim((string)($_GET['id'] ?? $_POST['id'] ?? ''));
        if ($id === '') {
            http_response_code(400);
            echo 'Identificador do projeto não informado.';
            return;
        }

        $item = $this->obterItemPorId($id);
        if ($item === null) {
            http_response_code(404);
            echo 'Projeto não encontrado no histórico.';
            return;
        }

        $payload = is_array($item['conteudo'] ?? null) ? $item['conteudo'] : [];

        if (($item['modulo'] ?? '') === 'PageMaker' && ($_GET['arquivo'] ?? '') === 'css') {
            if (!isset($payload['css']) || !is_string($payload['css'])) {
                http_response_code(404);
                echo 'CSS não encontrado para este projeto.';
                return;
            }

            $nomeArquivo = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', trim((string)($item['nome'] ?? 'pagina'))) . '.css';
            header('Content-Type: text/css; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            echo $payload['css'];
            exit();
        }

        if (($item['modulo'] ?? '') === 'PageMaker' && isset($payload['html'])) {
            $nomeArquivo = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', trim((string)($item['nome'] ?? 'pagina'))) . '.html';
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            echo (string) $payload['html'];
            exit();
        }

        $nomeArquivo = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', trim((string)($item['nome'] ?? 'projeto'))) . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function apagarProjetoHistorico(): void {
        $this->autenticacaoRequired();

        $id = trim((string)($_GET['id'] ?? $_POST['id'] ?? ''));
        if ($id === '') {
            $this->redirect(URL_BASE . '/projetos/historico');
        }

        $historico = $this->sincronizarHistoricoEArquivos();
        $item = $this->removerItemPorId($historico, $id);
        if ($item === null) {
            $this->redirect(URL_BASE . '/projetos/historico');
        }

        $this->salvarJsonArrayValido($this->getHistoricoStoragePath(), $historico);
        $this->redirect(URL_BASE . '/projetos/historico');
    }

    public function limparHistorico(): void {
        $this->autenticacaoRequired();

        $this->sincronizarHistoricoEArquivos();
        $this->salvarJsonArrayValido($this->getHistoricoStoragePath(), []);
        $this->redirect(URL_BASE . '/projetos/historico');
    }

    public function index(): void {
        $this->autenticacaoRequired();
        $this->view('projetos/home');
    }

    public function guia(): void {
        $this->view('projetos/guia');
    }

    

    public function mvcCreator(): void {
        $this->autenticacaoRequired();

        $usuarioLogado = $_SESSION['usuario_logado'] ?? null;
        if (!$usuarioLogado || !method_exists($usuarioLogado, 'getEmail')) {
            $this->redirect(URL_BASE . '/login');
        }

        $usuarioService = new UsuarioService();
        $bancoService = new BancoService();
        $usuario = $usuarioService->getUsuarioPorEmail($usuarioLogado->getEmail());

        if (!$usuario || !method_exists($usuario, 'getIdUsuario')) {
            $this->redirect(URL_BASE . '/login');
        }

        $bancos = $bancoService->getBancoByUsuario($usuario->getIdUsuario());
        $bancoOpts = '';
        if (is_array($bancos)) {
            foreach($bancos as $banco){
                $bancoOpts .= '<option value="' . $banco['id_banco'] . '">' . $banco['nome_banco'] . '</option>';
            }
        }
        $this->view('projetos/mvcCreator', ['bancoOpts' => $bancoOpts]);
    }

    public function pageMaker(): void {
        $this->view('projetos/pageMaker');
    }

    public function historico(): void {
        $this->autenticacaoRequired();
        $historico = $this->sincronizarHistoricoEArquivos();
        $this->view('projetos/historico', ['historico' => $historico]);
    }

    public function saida(): void {
        $this->view('projetos/saida');
    }

    public function cadastrar(): void {
        $this->view("projetos/projeto_create");
    }

    public function editar(): void {
        
        $data = [];
        $id_projeto = $_POST['id_projeto'];
        $proj = $this->projetoService->getById($id_projeto);
        $nome = $proj['nome_projeto'];


        $data['nome'] = $nome;
        $this->view("projetos/projeto_edit",$data);

    }

    public function bools(){
        
        $validador = new Validador();
        $nome   = $_POST['nome'];
        $server = $_POST['server'];
        $user   = $_POST['user'];
        $pass   = $_POST['pass'];
        $banco  = $_POST['mvc-banco'];
        $this->obrigatorios($validador,$nome,$server,$user,$pass,$banco);
        $data = ["nome"=>$nome,"server"=>$server,"user"=>$user,"pass"=>$pass,'banco'=>$banco];
        $this->view("projetos/projeto_bools",$data);
    }

    public function editBools(){
        $validador = new Validador();
        $nome = $_POST['nome'];

        $validador->obrigatorio('nome',$nome);
        if($validador->temErros())$this->view("");
    }

    public function criar(){
        $validador = new Validador();
        $nome   = trim($_POST['nome']);
        $server = trim($_POST['server']);
        $user   = trim($_POST['user']);
        $pass   = trim($_POST['pass']);
        $banco  = trim($_POST['mvc-banco']);
        $options = [];
        foreach($_POST as $key=>$value){
            if(substr($key,0,3)=='opt'){
                $options[substr($key,4)] = (int)$value;
            }
        }
        $this->obrigatorios($validador,$nome,$server,$user,$pass,$banco);
        
        $projeto = new Projeto(1,1,null,$nome,date("Y-m-d H:i:s"),$options,null);


        $this->projetoService->insert($projeto);
        
        $this->redirect(URL_BASE);
        
        // $this->view("projetos/projeto_create");
    }

    private function obrigatorios(Validador $validador, $nome,$server,$user,$pass,$banco){
        $validador->obrigatorio('nome',$nome);
        $validador->obrigatorio('server',$server);
        $validador->obrigatorio('user',$user);
        // $validador->obrigatorio('pass',$pass);
        $validador->obrigatorio('banco',$banco);

        if($validador->temErros()){
            $data['erros'] = $validador->getErros();
            $data['nome'] = $nome;
            $data['server'] = $server;
            $data['user'] = $user;
            $data['pass'] = $pass;
            $data['banco'] = $banco;
            $this->view('/projetos/projeto_create',$data);
            die;
        }
    }

}