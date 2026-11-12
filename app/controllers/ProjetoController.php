<?php
namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\models\Projeto;
use app\services\ProjetoService;
use app\tools\gerador\GerenciadorGerador;
use app\tools\gerador\GeradorZip;

class ProjetoController extends Controller{
    private ProjetoService $projetoService;

    public function __construct(){
        $this->projetoService = new ProjetoService();
    }


    public function getDatabases(){
<<<<<<< Updated upstream
        $user   = trim($_POST['usuario']);
        $pass   = trim($_POST['senha']);
        $server = trim($_POST['servidor']);
        echo $this->projetoService->getDatabases("mysql:host=$server",$user,$pass);
=======
        if (empty($_SESSION['usuario_logado'])) {
            echo '<option value="">Selecione um banco</option>';
            return;
        }
        $sel = trim($_POST['selecionado'] ?? null);
        echo (new BancoService())->getBancoByUsuario($_SESSION['usuario_logado']->getIdUsuario(),"DB_OPTIONS",$sel);
>>>>>>> Stashed changes
    }

    public function getTabelas(): void {
        header('Content-Type: application/json');
        $user = trim($_POST['usuario'] ?? '');
        $pass = trim($_POST['senha'] ?? '');
        $server = trim($_POST['servidor'] ?? 'localhost');
        $banco = trim($_POST['banco'] ?? '');

        try {
            $tabelas = $this->projetoService->getTabelas("mysql:host=$server", $user, $pass, $banco);
            echo json_encode(['sucesso' => true, 'tabelas' => $tabelas]);
        } catch (\Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function gerarMvc(): void {
        header('Content-Type: application/json');
        $nomeProjeto = trim($_POST['nomeProjeto'] ?? 'meu_projeto');
        $user = trim($_POST['usuario'] ?? '');
        $pass = trim($_POST['senha'] ?? '');
        $server = trim($_POST['servidor'] ?? 'localhost');
        $banco = trim($_POST['banco'] ?? '');
        $tabelasSelecionadas = $_POST['tabelas'] ?? [];

        if (empty($banco) || empty($tabelasSelecionadas)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione o banco de dados e ao menos uma tabela.']);
            return;
        }

        try {
            // Pasta temporária para compilar o projeto gerado
            $pastaOutput = __DIR__ . '/../../public/temp/' . $nomeProjeto;
            $pastaApp = $pastaOutput . '/app';

            $gerenciador = new GerenciadorGerador();

            foreach ($tabelasSelecionadas as $tabela) {
                $colunasObj = $this->projetoService->getColunas("mysql:host=$server", $user, $pass, $banco, $tabela);
                $atributos = array_column($colunasObj, 'Field');
                $chavePrimaria = 'id';
                foreach ($colunasObj as $col) {
                    if (isset($col['Key']) && $col['Key'] === 'PRI') {
                        $chavePrimaria = $col['Field'];
                        break;
                    }
                }

                // Desativado a geração direta no app/ do projeto ativo:
                // $gerenciador->gerarTudo($tabela, $atributos, $chavePrimaria);

                // Copiar também para a pasta de exportação ZIP
                $geradorModel = new \app\tools\gerador\GeradorModel();
                $geradorRepo = new \app\tools\gerador\GeradorRepositorio();
                $geradorCtrl = new \app\tools\gerador\GeradorController();
                $geradorView = new \app\tools\gerador\GeradorView();

                $geradorModel->salvarModel($tabela, $atributos, $pastaApp . '/models');
                $geradorRepo->salvarRepositorio($tabela, $atributos, $chavePrimaria, $pastaApp . '/repositories');
                $geradorCtrl->salvarController($tabela, $atributos, $chavePrimaria, $pastaApp . '/controllers');
                $geradorView->salvarViews($tabela, $atributos, $chavePrimaria, $pastaApp . '/views');
            }

            // Criar arquivo .zip para download
            $pastaZipDestino = __DIR__ . '/../../public/downloads';
            if (!is_dir($pastaZipDestino)) {
                mkdir($pastaZipDestino, 0777, true);
            }
            $arquivoZip = $pastaZipDestino . '/' . $nomeProjeto . '.zip';

            $geradorZip = new GeradorZip();
            $geradorZip->compactarPasta($pastaOutput, $arquivoZip);

            // Apaga a pasta temporária de compilação após gerar o ZIP
            $this->excluirDiretorioRecursivo($pastaOutput);

            $downloadUrl = URL_BASE . '/projetos/downloadZip?file=' . urlencode($nomeProjeto . '.zip');

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
        $caminhoZip = __DIR__ . '/../../public/downloads/' . $nomeZip;

        $geradorZip = new GeradorZip();
        $geradorZip->enviarDownload($caminhoZip, $nomeZip);
    }

<<<<<<< Updated upstream
=======
    public function baixarPageMakerZip(): void {
        $html = $_POST['html'] ?? null;
        $css = $_POST['css'] ?? null;
        if (!is_string($html) || trim($html) === '' || !is_string($css) || trim($css) === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Gere a prévia antes de criar o arquivo ZIP.']);
            return;
        }

        $nomeInformado = trim((string) ($_POST['nome'] ?? 'pagina'));
        $nomeBase = preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo(str_replace('\\', '/', $nomeInformado), PATHINFO_FILENAME));
        $nomeBase = trim((string) $nomeBase, '_-');
        if ($nomeBase === '') {
            $nomeBase = 'pagina';
        }

        $pastaTemporaria = null;
        $arquivoZip = null;
        $conteudoZip = null;

        try {
            $identificador = bin2hex(random_bytes(12));
            $pastaTemporaria = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'devstudio_pagemaker_' . $identificador;
            $arquivoZip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'devstudio_pagemaker_' . $identificador . '.zip';
            if (!mkdir($pastaTemporaria, 0700)) {
                throw new \RuntimeException('Não foi possível preparar os arquivos para compactação.');
            }

            $arquivoHtml = $pastaTemporaria . DIRECTORY_SEPARATOR . $nomeBase . '.html';
            $arquivoCss = $pastaTemporaria . DIRECTORY_SEPARATOR . $nomeBase . '.css';
            if (file_put_contents($arquivoHtml, $html) === false || file_put_contents($arquivoCss, $css) === false) {
                throw new \RuntimeException('Não foi possível salvar os arquivos HTML e CSS temporários.');
            }

            $geradorZip = new \app\tools\gerador\GeradorZip();
            if (!$geradorZip->compactarPasta($pastaTemporaria, $arquivoZip)) {
                throw new \RuntimeException('Não foi possível compactar os arquivos do PageMaker.');
            }

            $conteudoZip = file_get_contents($arquivoZip);
            if ($conteudoZip === false) {
                throw new \RuntimeException('Não foi possível ler o arquivo ZIP gerado.');
            }
        } catch (\Throwable $e) {
            error_log('Falha ao gerar ZIP do PageMaker: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível gerar o arquivo ZIP. Tente novamente.']);
            return;
        } finally {
            if ($pastaTemporaria !== null) {
                foreach ([$pastaTemporaria . DIRECTORY_SEPARATOR . $nomeBase . '.html', $pastaTemporaria . DIRECTORY_SEPARATOR . $nomeBase . '.css'] as $arquivoTemporario) {
                    if (is_file($arquivoTemporario) && !unlink($arquivoTemporario)) {
                        error_log('Não foi possível remover arquivo temporário do PageMaker: ' . $arquivoTemporario);
                    }
                }
                if (is_dir($pastaTemporaria) && !rmdir($pastaTemporaria)) {
                    error_log('Não foi possível remover pasta temporária do PageMaker: ' . $pastaTemporaria);
                }
            }
            if ($arquivoZip !== null && is_file($arquivoZip) && !unlink($arquivoZip)) {
                error_log('Não foi possível remover ZIP temporário do PageMaker: ' . $arquivoZip);
            }
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $nomeBase . '.zip"');
        header('Content-Length: ' . strlen($conteudoZip));
        header('Cache-Control: no-store');
        echo $conteudoZip;
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
>>>>>>> Stashed changes

    public function index(): void {
        $this->view('projetos/home');
    }

    public function configGlobal(): void {
        $this->view('projetos/configGlobal');
    }

    public function guia(): void {
        $this->view('projetos/guia');
    }

    public function mvcCreator(): void {
<<<<<<< Updated upstream
        $this->view('projetos/mvcCreator');
=======
        $usuarioLogado = $_SESSION['usuario_logado'] ?? null;
        if (!$usuarioLogado) {
            $this->view('projetos/mvcCreator', ['bancoOpts' => '']);
            return;
        }

        if (!method_exists($usuarioLogado, 'getEmail')) {
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
>>>>>>> Stashed changes
    }

    public function pageMaker(): void {
        $this->view('projetos/pageMaker');
    }

    public function historico(): void {
        $this->view('projetos/historico');
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
        // print_r($_POST);
        $this->view("projetos/projeto_bools",$data);
    }

    public function editBools(){
        $validador = new Validador();
        $nome = $_POST['nome'];

        $validador->obrigatorio('nome',$nome);
        if($validador->temErros())$this->view("");
    }

    public function criar(){
        // var_dump($_POST);
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