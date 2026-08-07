<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
class Creator {
    private $con;
    private $servidor ;
    private $banco;
    private $usuario;
    private $senha;
    private $tabelas;
    function __construct() {
        if(isset($_GET['id']))
            $this->buscaBancodeDados();
        else {
            $this->criaDiretorios();
            $this->conectar(1);
            $this->buscaTabelas();
            $this->ClassesModel();
            $this->ClasseConexao();
            $this->ClassesControl();
            $this->classesView();
            $this->ClassesDao();
            $this->compactar();
            header("Location:home.php?criado=1");
        }
    }//fimConsytruct
    function criaDiretorios() {
        $dirs = [
            "sistema",
            "sistema/model",
            "sistema/control",
            "sistema/view",
            "sistema/dao",
            "sistema/css"
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    header("Location:index.php?msg=0");
                }
            }
        }
        copy('estilos.css','sistema/css/estilos.css');
    }//fimDiretorios
    function conectar($id){
        $this->servidor = $_REQUEST["servidor"];
        $this->usuario = $_REQUEST["usuario"];
        $this->senha = $_REQUEST["senha"];
        
        if ($id == 1) {
           $this->banco = $_POST["banco"];
        } else {
            // Para buscar bancos, conectar sem especificar banco ou usar mysql
            $this->banco = "";
        }
        
        try {
            if ($id == 0) {
                // Conexão apenas para listar bancos (sem especificar banco)
                $this->con = new PDO(
                    "mysql:host=" . $this->servidor,
                    $this->usuario,
                    $this->senha,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
            } else {
                // Conexão normal com banco especificado
                $this->con = new PDO(
                    "mysql:host=" . $this->servidor . ";dbname=" . $this->banco,
                    $this->usuario,
                    $this->senha,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
            }
        } catch (Exception $e) {
            if ($id == 0) {
                // Se for busca de bancos, não redirecionar, deixar o erro ser tratado
                throw $e;
            } else {
                header("Location:index.php?msg=1");
            }
        }
    }//fimConectar
    function buscaBancodeDados(){
        try {
            $this->conectar(0);
            $sql = "SHOW databases";
            $query = $this->con->query($sql);
            
            if ($query) {
                $databases = $query->fetchAll(PDO::FETCH_ASSOC);
                
                // Filtrar bancos do sistema que normalmente não queremos mostrar
                $bancosIgnorar = ['information_schema', 'performance_schema', 'mysql', 'sys'];
                
                $encontrouBanco = false;
                foreach ($databases as $database){
                    $nomeDB = $database["Database"];
                    if (!in_array($nomeDB, $bancosIgnorar)) {
                        echo "<option value='{$nomeDB}'>{$nomeDB}</option>";
                        $encontrouBanco = true;
                    }
                }
                
                if (!$encontrouBanco) {
                    echo "<option value=''>Nenhum banco de usuário encontrado</option>";
                }
            } else {
                echo "<option value=''>Erro ao executar consulta</option>";
            }
            
            $this->con = null;
        }
        catch (Exception $e) {
            $mensagemErro = $e->getMessage();
            
            // Verificar tipos específicos de erro e dar sugestões
            if (strpos($mensagemErro, 'Access denied') !== false) {
                if (strpos($mensagemErro, 'using password: YES') !== false) {
                    echo "<option value=''>❌ Senha incorreta - Verifique a senha do MySQL</option>";
                } else {
                    echo "<option value=''>❌ Usuário sem senha - Tente deixar a senha em branco</option>";
                }
            } elseif (strpos($mensagemErro, 'Connection refused') !== false) {
                echo "<option value=''>❌ MySQL não está rodando - Inicie o XAMPP</option>";
            } elseif (strpos($mensagemErro, 'Unknown MySQL server host') !== false) {
                echo "<option value=''>❌ Servidor não encontrado - Verifique o endereço</option>";
            } else {
                echo "<option value=''>❌ Erro: " . htmlspecialchars($mensagemErro) . "</option>";
            }
        }
    }//BuscaBD
    function buscaTabelas(){
       try {
           $sql = "SHOW TABLES";
           $query = $this->con->query($sql);
           $this->tabelas = $query->fetchAll(PDO::FETCH_ASSOC);
       }
       catch (Exception $e) {
           header("Location:index.php?msg=3");
       }
    }//fimBuscaTabelas
    function buscaAtributos($nomeTabela){
        $sql="show columns from ".$nomeTabela;
        $atributos = $this->con->query($sql)->fetchAll(PDO::FETCH_OBJ);
        return $atributos;
    }//fimBuscaAtributos
    function ClassesModel() {
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array) $tabela)[0];
            $atributos=$this->buscaAtributos($nomeTabela);
            $nomeAtributos="";
            $geters_seters="";
            foreach ($atributos as $atributo) {
                $atributo=$atributo->Field;
                $nomeAtributos.="\tprivate \${$atributo};\n";
                $metodo=ucfirst($atributo);
                $geters_seters.="\tfunction get".$metodo."(){\n";
                $geters_seters.="\t\treturn \$this->{$atributo};\n\t}\n";
                $geters_seters.="\tfunction set".$metodo."(\${$atributo}){\n";
                $geters_seters.="\t\t\$this->{$atributo}=\${$atributo};\n\t}\n";
            }
            $nomeClasse=ucfirst($nomeTabela);
            $conteudo = <<<EOT
<?php
class {$nomeClasse} {
{$nomeAtributos}
{$geters_seters}
}
?>
EOT;
            file_put_contents("sistema/model/{$nomeTabela}.php", $conteudo);

        }
    }//fimModel
    function ClasseConexao(){
        $conteudo = <<<EOT

<?php
class Conexao {
    private \$server;
    private \$banco;
    private \$usuario;
    private \$senha;
    function __construct() {
        \$this->server = '{$this->servidor}';
        \$this->banco = '{$this->banco}';
        \$this->usuario = '{$this->usuario}';
        \$this->senha = '{$this->senha}';
    }
    
    function conectar() {
        try {
            \$conn = new PDO(
                "mysql:host=" . \$this->server . ";dbname=" . \$this->banco,\$this->usuario,
                \$this->senha
            );
            return \$conn;
        } catch (Exception \$e) {
            echo "Erro ao conectar com o Banco de dados: " . \$e->getMessage();
        }
    }
}
?>
EOT;
        file_put_contents("sistema/model/conexao.php", $conteudo);
    }//fimConexao
    function ClassesControl(){
    foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $atributos=$this->buscaAtributos($nomeTabela);
            $nomeClasse=ucfirst($nomeTabela);
            $posts="";
            foreach ($atributos as $atributo) {
                $campo = $atributo->Field;
                // Pula campos auto_increment na inserção
                if (strpos($atributo->Extra, 'auto_increment') !== false) {
                    continue;
                }
                $posts.= "\$this->{$nomeTabela}->set".ucFirst($campo).
                    "(\$_POST['{$campo}']);\n\t\t";
            }

            $conteudo = <<<EOT
<?php
require_once("../model/{$nomeTabela}.php");
require_once("../dao/{$nomeTabela}Dao.php");
class {$nomeClasse}Control {
    private \${$nomeTabela};
    private \$acao;
    private \$dao;
    public function __construct(){
       \$this->{$nomeTabela}=new {$nomeClasse}();
      \$this->dao=new {$nomeClasse}Dao();
      \$this->acao=\$_GET["a"];
      \$this->verificaAcao(); 
    }
    function verificaAcao(){
       switch(\$this->acao){
          case 1:
            \$this->inserir();
          break;
          case 2:
            \$this->excluir();
          break;
          case 3:
            \$this->alterar();
          break;
       }
    }
  
    function inserir(){
        {$posts}
        \$this->dao->inserir(\$this->{$nomeTabela});
    }
    function excluir(){
        \$this->dao->excluir(\$_REQUEST['id']);
    }
    function alterar(){
        {$posts}
        \$this->dao->alterar(\$this->{$nomeTabela}, \$_POST['id']);
    }
    function buscarId(\$id){
        return \$this->dao->buscarId(\$id);
    }
    function buscaTodos(){}

}
new {$nomeClasse}Control();
?>
EOT;
            file_put_contents("sistema/control/{$nomeTabela}Control.php", $conteudo);
        }

    }//fimControl
    function compactar() {
        $folderToZip = 'sistema';
        $outputZip = 'sistema.zip';
        
        // Remove o arquivo zip anterior se existir
        if (file_exists($outputZip)) {
            unlink($outputZip);
        }
        
        $zip = new ZipArchive();
        if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return false;
        }
        $folderPath = realpath($folderToZip);  // Corrigido aqui
        if (!is_dir($folderPath)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folderPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        return $zip->close();
    }//fimCompactar
    function ClassesDao(){
     foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $nomeClasse = ucfirst($nomeTabela);
            $atributos=$this->buscaAtributos($nomeTabela);
            $id="";
            foreach($atributos as $atributo) {
                if ($atributo->Key == "PRI")
                    $id = $atributo->Field;
            }
            // Filtra apenas os campos que não são auto_increment
            $camposInserir = [];
            foreach ($atributos as $atributo) {
                if (strpos($atributo->Extra, 'auto_increment') === false) {
                    $camposInserir[] = $atributo->Field;
                }
            }
            
            $sqlCols = implode(', ', $camposInserir);
            $placeholders = implode(', ', array_fill(0, count($camposInserir), '?'));
         $vetAtributos=[];
         $AtributosMetodos="";

         foreach ($camposInserir as $atributo) {
             $atr=ucfirst($atributo);
             array_push($vetAtributos,"\${$atributo}");
             $AtributosMetodos.="\${$atributo}=\$obj->get{$atr}();\n";
         }
         $atributosOk=implode(",",$vetAtributos);
         $conteudo = <<<EOT
<?php
require_once("../model/conexao.php");
class {$nomeClasse}Dao {
    private \$con;
    public function __construct(){
       \$this->con=(new Conexao())->conectar();
    }
function inserir(\$obj) {
    \$sql = "INSERT INTO {$nomeTabela} ({$sqlCols}) VALUES ({$placeholders})";
    \$stmt = \$this->con->prepare(\$sql);
    {$AtributosMetodos}
    \$stmt->execute([{$atributosOk}]);
}
function listaGeral(){
    \$sql = "select * from {$nomeTabela}";
    \$query = \$this->con->query(\$sql);
    \$dados = \$query->fetchAll(PDO::FETCH_ASSOC);
    return \$dados;
}
function excluir(\$id){
    \$sql = "delete from {$nomeTabela} where {$id}=\$id";
    \$query = \$this->con->query(\$sql);
    header("Location:../view/lista{$nomeClasse}.php");
}
function alterar(\$obj, \$id){
    {$AtributosMetodos}
    \$campos = [];
    \$valores = [];
    
EOT;

// Gerar os campos SET dinamicamente, excluindo a chave primária e auto_increment
foreach ($atributos as $atributoObj) {
    $campoNome = $atributoObj->Field;
    if ($campoNome != $id && strpos($atributoObj->Extra, 'auto_increment') === false) {
        $conteudo .= "    \$campos[] = '{$campoNome} = ?';\n";
        $conteudo .= "    \$valores[] = \${$campoNome};\n";
    }
}

$conteudo .= <<<EOT
    
    \$sql = "UPDATE {$nomeTabela} SET " . implode(", ", \$campos) . " WHERE {$id} = ?";
    \$valores[] = \$id;
    
    \$stmt = \$this->con->prepare(\$sql);
    \$stmt->execute(\$valores);
    header("Location:../view/lista{$nomeClasse}.php");
}
function buscarId(\$id){
    \$sql = "SELECT * FROM {$nomeTabela} WHERE {$id} = ?";
    \$stmt = \$this->con->prepare(\$sql);
    \$stmt->execute([\$id]);
    return \$stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>
EOT;
            file_put_contents("sistema/dao/{$nomeTabela}Dao.php", $conteudo);
        }

    }//fimDao
    function classesView() {
        //formulários
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array) $tabela)[0];
            $nomeClasse = ucfirst($nomeTabela);
            $atributos=$this->buscaAtributos($nomeTabela);
            $formCampos="";
            foreach ($atributos as $atributo) {
                $campo = $atributo->Field;
                // Pula campos auto_increment (normalmente IDs)
                if (strpos($atributo->Extra, 'auto_increment') !== false) {
                    continue;
                }
                $formCampos .= "<label for='{$campo}'>{$campo}</label>\n";
                $formCampos .= "<input type='text' name='{$campo}'><br>\n";
            }
            $conteudo = <<<HTML
<html>
    <head>
        <title>Cadastro de {$nomeTabela}</title>
        <link rel="stylesheet" href="../css/estilos.css">
    </head>
    <body>
        <a href="../../home.php" class="btn-voltar">🏠 Voltar ao Início</a>
        <form action="../control/{$nomeTabela}Control.php?a=1" method="post">
        <h1>Cadastro de {$nomeTabela}</h1>
            {$formCampos}
             <button type="submit">Enviar</button>
        </form>
    </body>
</html>
HTML;
            file_put_contents("sistema/view/{$nomeTabela}.php", $conteudo); // Exemplo salvando como arquivo
        }
        
        //formulários de alteração
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array) $tabela)[0];
            $atributos=$this->buscaAtributos($nomeTabela);
            $formCampos="";
            $idField="";
            
            foreach ($atributos as $atributo) {
                $atributoObj = $atributo;
                $atributoNome = $atributo->Field;
                if ($atributoObj->Key == "PRI") {
                    $idField = $atributoNome;
                    $formCampos .= "<input type='hidden' name='id' value=\"<?php echo \$dados['{$atributoNome}']; ?>\">\n";
                } else {
                    $formCampos .= "<label for='{$atributoNome}'>{$atributoNome}</label>\n";
                    $formCampos .= "<input type='text' name='{$atributoNome}' value=\"<?php echo \$dados['{$atributoNome}']; ?>\"><br>\n";
                }
            }
            
            $nomeClasse = ucfirst($nomeTabela);
            $conteudo = <<<HTML
<html>
    <head>
        <title>Alterar {$nomeTabela}</title>
        <link rel="stylesheet" href="../css/estilos.css">
    </head>
    <body>
        <a href="../../home.php" class="btn-voltar">🏠 Voltar ao Início</a>
        <?php
        require_once("../dao/{$nomeTabela}Dao.php");
        \$dao = new {$nomeClasse}Dao();
        \$dados = \$dao->buscarId(\$_GET['id']);
        ?>
        <form action="../control/{$nomeTabela}Control.php?a=3" method="post">
        <h1>Alterar {$nomeTabela}</h1>
            {$formCampos}
             <button type="submit">Alterar</button>
             <a href="lista{$nomeClasse}.php">Voltar</a>
        </form>
    </body>
</html>
HTML;
            file_put_contents("sistema/view/alterar{$nomeClasse}.php", $conteudo);
        }
        
        //Listas
        foreach ($this->tabelas as $tabela) {
            $nomeTabela = array_values((array)$tabela)[0];
            $nomeTabelaUC=ucfirst($nomeTabela);
            $atributos=$this->buscaAtributos($nomeTabela);
            $attr = "";
            $id="";
            foreach($atributos as $atributo){
                if($atributo->Key=="PRI")
                    $id="{\$dado['{$atributo->Field}']}";

                $attr.= "echo \"<td>{\$dado['{$atributo->Field}']}</td>\";\n";
            }
            $conteudo="";
            $conteudo = <<<HTML

<html>
    <head>
        <title>Lista de {$nomeTabela}</title>
        <link rel="stylesheet" href="../css/estilos.css">
    </head>
    <body>
      <a href="../../home.php" class="btn-voltar">🏠 Voltar ao Início</a>
      <?php
      require_once("../dao/{$nomeTabela}Dao.php");
   \$dao=new {$nomeTabela}DAO();
   \$dados=\$dao->listaGeral();
    echo "<table border=1>";
    foreach(\$dados as \$dado){
        echo "<tr>";
       {$attr}
       echo "<td>".
       "<a href='../control/{$nomeTabela}Control.php?id={$id}&a=2'> Excluir</a>".
       "</td>";
       echo "<td>".
       "<a href='alterar{$nomeTabelaUC}.php?id={$id}'> Alterar</a>".
       "</td>";
       echo "</tr>";
    }
    echo "</table>";
     ?>  
    </body>
</html>
HTML;           
  file_put_contents("sistema/view/lista{$nomeTabelaUC}.php", $conteudo);        
        }
    }//fimView
 
}
new Creator();
