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
use app\tools\gerador\GerenciadorGerador;
use app\tools\gerador\GeradorZip;
use app\tools\SchemaInspector;
use app\services\UsuarioService;

class ProjetoController extends Controller{
    private ProjetoService $projetoService;

    public function __construct(){
        $this->projetoService = new ProjetoService();
    }


    public function getDatabases(){
        
        $sel = trim($_POST['selecionado'] ?? null);
        echo (new BancoService())->getBancoByUsuario($_SESSION['usuario_logado']->getIdUsuario(),"DB_OPTIONS",$sel);
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
        
        
        foreach($atributos as $atributo){
            foreach($atributo as $att){
                // print_r($att);
            }
        }

        // print_r($bancoSelecionado);
        // print_r($tabelas);
        // print_r($atributos);
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
            // print_r($tabelaEspecifica);
            // print_r($schema->getAtributos($value[0]));
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
        header('Content-Type: application/json');
        $bancoService = new BancoService();
        $tabelaService = new TabelaService();
        $atributoService = new AtributoService();
        $nomeProjeto = trim($_POST['nomeProjeto'] ?? 'meu_projeto');
        $user = trim($_POST['usuario'] ?? '');
        $pass = trim($_POST['senha'] ?? '');
        $server = trim($_POST['servidor'] ?? 'localhost');
        $banco = trim($_POST['banco'] ?? '');
        $tabelasSelecionadas = $_POST['tabelas'] ?? [];
        
        $bancoSelecionado = $bancoService->getBancoById($banco);
        $tabelas = $tabelaService->getTabelasByFk_banco($bancoSelecionado['id_banco']);
        $atributos = [];
        foreach ($tabelas as $tabela) {
            $atributos[] = $atributoService->getAtributosByFk_tabela($tabela->getId_tabela());
        }
        // print_r($bancoSelecionado);
        if(!$bancoSelecionado){
            echo json_encode(['sucesso' => false, 'mensagem' => 'Banco de dados não encontrado.']);
            return;
        }

        // if (empty($banco) || empty($tabelasSelecionadas)) {
        //     echo json_encode(['sucesso' => false, 'mensagem' => 'Selecione o banco de dados e ao menos uma tabela.']);
        //     return;
        // }

        try {
            // Pasta temporária para compilar o projeto gerado
            $pastaOutput = __DIR__ . '/../../public/temp/' . $nomeProjeto;
            $pastaApp = $pastaOutput . '/app';

            $gerenciador = new GerenciadorGerador();

            foreach ($tabelas as $tabela) {
                
                
                
    
                // Desativado a geração direta no app/ do projeto ativo:
                // $gerenciador->gerarTudo($tabela, $atributos, $chavePrimaria);

                // Copiar também para a pasta de exportação ZIP
                $geradorModel = new \app\tools\gerador\GeradorModel();
                $geradorRepo = new \app\tools\gerador\GeradorRepositorio();
                $geradorCtrl = new \app\tools\gerador\GeradorController();
                $geradorView = new \app\tools\gerador\GeradorView();

                $geradorModel->salvarModel($tabela, $atributos, $pastaApp . '/models');
                $geradorRepo->salvarRepositorio($tabela, $atributos, 'id', $pastaApp . '/repositories');
                $geradorCtrl->salvarController($tabela, $atributos, 'id', $pastaApp . '/controllers');
                $geradorView->salvarViews($tabela, $atributos, 'id', $pastaApp . '/views');
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
        $this->autenticacaoRequired();
        $this->view('projetos/home');
    }

    public function configGlobal(): void {
        $this->autenticacaoRequired();
        $this->view('projetos/configGlobal');
    }

    public function guia(): void {
        $this->view('projetos/guia');
    }

    

    public function mvcCreator(): void {
        $this->autenticacaoRequired();
        $usuarioService = new UsuarioService();
        $bancoService = new BancoService();
        $usuario = $usuarioService->getUsuarioPorEmail($_SESSION['usuario_logado']->getEmail());
        // print_r($usuario);
        $bancos = $bancoService->getBancoByUsuario($usuario->getIdUsuario());
        // print_r($bancos);
        $bancoOpts = '';
        foreach($bancos as $banco){
            $bancoOpts .= '<option value="' . $banco['id_banco'] . '">' . $banco['nome_banco'] . '</option>';
        }
        // print $bancoOpts;
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