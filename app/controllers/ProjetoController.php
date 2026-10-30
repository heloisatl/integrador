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
use app\tools\gerador\FiltroTabelas;
use app\tools\gerador\MotorGeracao;
use app\tools\gerador\OpcoesGeracao;
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

        // $user = trim($_POST['usuario'] ?? '');
        // $pass = trim($_POST['senha'] ?? '');
        // $server = trim($_POST['servidor'] ?? 'localhost');
        $banco = trim($_POST['banco'] ?? '');


        try {
            $tabelaService = new TabelaService();
            $tabelas = $tabelaService->getTabelasByFk_banco($banco,'prox_etap');
            echo json_encode(['sucesso' => true, 'tabelas' => $tabelas]);
        } catch (\Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    
    //Lê as escolhas do usuário (banco, tabelas e camadas) e devolve os dados prontos para o MotorGeracao.
    //A leitura do banco e os filtros acontecem aqui; o motor só recebe dados já preparados.
    
    private function montarContextoGeracao(): array {
        $banco = trim($_POST['banco'] ?? '');
        if (empty($banco)) {
            throw new \InvalidArgumentException('Selecione um banco de dados antes de gerar o sistema.');
        }

        $bancoSelecionado = (new BancoService())->getBancoById($banco);
        if (!$bancoSelecionado) {
            throw new \InvalidArgumentException('Banco de dados não encontrado.');
        }

        // Etapa 1: tabelas que o usuário desmarcou em "Tabelas Detectadas"
        $tabelas = (new TabelaService())->getTabelasByFk_banco($bancoSelecionado['id_banco']);
        $tabelas = FiltroTabelas::fromRequest($_POST['tabelasDesativadas'] ?? null)->filtrar($tabelas);
        if (empty($tabelas)) {
            throw new \InvalidArgumentException('Selecione ao menos uma tabela para gerar o projeto.');
        }

        // Etapa 2: camadas/artefatos desmarcados em "Opções de Geração" (+ regra RN 06)
        $opcoes = OpcoesGeracao::fromRequest($_POST, $bancoSelecionado);
        if (empty($opcoes->camadasAtivas())) {
            throw new \InvalidArgumentException('Selecione ao menos uma camada ou artefato para gerar.');
        }

        $atributoService = new AtributoService();
        $atributosPorTabela = [];
        foreach ($tabelas as $tabela) {
            $atributosPorTabela[$tabela->getId_tabela()] = $atributoService->getAtributosByFk_tabela($tabela->getId_tabela());
        }

        return [
            'banco' => $bancoSelecionado,
            'tabelas' => $tabelas,
            'atributos' => $atributosPorTabela,
            'opcoes' => $opcoes,
        ];
    }

    
    //Pré-visualização da etapa "Estrutura de Arquivos": devolve exatamente os caminhos que o
    //MotorGeracao criaria para as tabelas e camadas selecionadas (mesma fonte usada no ZIP).
    
    public function previewEstrutura(): void {
        $this->autenticacaoJsonRequired();

        try {
            $ctx = $this->montarContextoGeracao();
            $caminhos = (new MotorGeracao())->listarCaminhos($ctx['tabelas'], $ctx['atributos'], $ctx['opcoes'], $ctx['banco']);

            echo json_encode([
                'sucesso' => true,
                'arquivos' => $caminhos,
                'total' => count($caminhos),
                'avisos' => $ctx['opcoes']->avisos(),
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    public function gerarMvc(): void {
        $this->autenticacaoJsonRequired();

        $nomeProjeto = trim($_POST['nomeProjeto'] ?? '') ?: 'meu_projeto';

        if (preg_match('/\s/', $nomeProjeto)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'O nome do projeto não deve conter espaços.']);
            return;
        }

        // O nome vira nome de pasta (que é apagada e recriada) e de .zip: só caracteres seguros.
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $nomeProjeto)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'O nome do projeto deve conter apenas letras, números, "_" e "-".']);
            return;
        }

        if (empty($_SESSION['usuario_logado'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Sessão expirada. Faça login novamente.']);
            return;
        }

        try {
            $ctx = $this->montarContextoGeracao();
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            return;
        }

        $projeto = new Projeto(
            $_SESSION['usuario_logado']->getIdUsuario(),
            (int) $ctx['banco']['id_banco'],
            null,
            $nomeProjeto,
            date('Y-m-d H:i:s'),
            [
                'comentarios' => $ctx['opcoes']->comentariosAtivos() ? 1 : 0,
                'views' => $ctx['opcoes']->ativa('views') ? 1 : 0
            ],
            null
        );

        $resultadoInsert = $this->projetoService->insert($projeto);
        if (!$resultadoInsert) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Não foi possível salvar o projeto no banco de dados.']);
            return;
        }

        $pastaOutput = __DIR__ . '/../projetos/temp/' . $nomeProjeto;
        $nomeZip = $nomeProjeto .'-' . $resultadoInsert .'.zip';
        $pastaDownloads = __DIR__ . '/../projetos/downloads/';
        if(!is_dir($pastaDownloads)){
            mkdir($pastaDownloads, 0755, true);
        }
        $arquivoZip = $pastaDownloads . $nomeZip;
        try {
            // Remove sobras de uma geração anterior interrompida: o ZIP só pode conter o que foi selecionado agora
            $this->excluirDiretorioRecursivo($pastaOutput);

            // O Motor cria somente as pastas/arquivos das camadas ativas e das tabelas selecionadas
            $motor = new MotorGeracao();
            $arquivos = $motor->gerar($ctx['tabelas'], $ctx['atributos'], $ctx['opcoes'], $pastaOutput, $ctx['banco']);

            // Criar arquivo .zip para download
            $geradorZip = new \app\tools\gerador\GeradorZip();
            if (!$geradorZip->compactarPasta($pastaOutput, $arquivoZip)) {
                throw new \RuntimeException('Não foi possível criar o arquivo .zip do projeto.');
            }

            // Apaga a pasta temporária de compilação após gerar o ZIP
            $this->excluirDiretorioRecursivo($pastaOutput);

            $downloadUrl = URL_BASE . '/projetos/downloadZip?file=' . urlencode($nomeZip);

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Sistema MVC gerado com sucesso!',
                'downloadUrl' => $downloadUrl,
                'total' => count($arquivos),
                'arquivos' => $arquivos,
                'avisos' => $ctx['opcoes']->avisos()
            ]);
        } catch (\Throwable $e) {
            $this->excluirDiretorioRecursivo($pastaOutput);
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
    public function index(): void {
        $this->autenticacaoRequired();
        $this->view('projetos/home');
    }

    public function guia(): void {
        $this->view('projetos/guia');
    }

    

    public function mvcCreator(): void {
        $this->autenticacaoRequired();
        $usuarioService = new UsuarioService();
        $bancoService = new BancoService();
        $usuario = $usuarioService->getUsuarioPorEmail($_SESSION['usuario_logado']->getEmail());
        $bancos = $bancoService->getBancoByUsuario($usuario->getIdUsuario());
        $bancoOpts = '';
        foreach($bancos as $banco){
            $bancoOpts .= '<option value="' . $banco['id_banco'] . '">' . $banco['nome_banco'] . '</option>';
        }
        $this->view('projetos/mvcCreator', ['bancoOpts' => $bancoOpts]);
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