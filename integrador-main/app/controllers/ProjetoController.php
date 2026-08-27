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
        $user   = trim($_POST['usuario']);
        $pass   = trim($_POST['senha']);
        $server = trim($_POST['servidor']);
        echo $this->projetoService->getDatabases("mysql:host=$server",$user,$pass);
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
        $this->view('projetos/mvcCreator');
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

     public function phpmeuamigo(): void {
        $this->view('projetos/phpmeuamigo');
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