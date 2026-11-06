<?php
namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\BancoRepository;

class BancoService{
    private ?BancoRepository $banco_repository = null;

    public function __construct(){
    }

    private function repo(): BancoRepository {
        if ($this->banco_repository === null) {
            $this->banco_repository = new BancoRepository();
        }
        return $this->banco_repository;
    }

    /**
     * Conecta à instância MySQL local da máquina do usuário (SHOW DATABASES via PDO)
     * e retorna a lista de esquemas disponíveis filtrando bancos internos do SGBD.
     * Não exige autenticação nem persiste dados no DevStudio.
     */
    public static function listarBancosMysqlLocal(
        string $host = 'localhost',
        string $porta = '3306',
        string $user = 'root',
        string $pass = ''
    ): array {
        $host  = trim($host) !== '' ? trim($host) : (defined('DB_HOST') ? DB_HOST : 'localhost');
        $porta = trim($porta) !== '' ? trim($porta) : '3306';
        $user  = trim($user) !== '' ? trim($user) : (defined('DB_USER') ? DB_USER : 'root');

        $dsn = "mysql:host={$host};port={$porta}";
        $pdo = ConnectionFactory::specialConn($dsn, $user, $pass);

        $stm = $pdo->query("SHOW DATABASES");
        $databases = $stm->fetchAll(\PDO::FETCH_COLUMN);

        $esquemasSistema = ['information_schema', 'performance_schema', 'mysql', 'sys'];
        return array_values(array_filter($databases, function ($db) use ($esquemasSistema) {
            return !in_array(strtolower((string) $db), $esquemasSistema, true);
        }));
    }



    public function insert($fk_usuario,$nome_banco,$usuario_banco,$senha_banco,$host,$porta){
        $result = null;
        if($this->getBanco($nome_banco,'nome_banco')){
            if(!$this->getBanco($usuario_banco,'usuario_banco')){
                $result = $this->repo()->insert($fk_usuario,$nome_banco,$usuario_banco,$senha_banco,$host,$porta);
            }
        }else{
            $result = $this->repo()->insert($fk_usuario,$nome_banco,$usuario_banco,$senha_banco,$host,$porta);
        }
        

        return $result;
    }

    public function getBancoById($value){
        $result = $this->repo()->getBancoById($value);

        return $result;
    }
    public function getBanco($value,$param = 'id_banco'){
        $result = $this->repo()->getBanco($value,$param);

        return $result;
    }

    public function getBancoEspecifico($nome_banco,$usuario_banco,$fk_usuario){
        $result = $this->repo()->getBancoEspecifico($nome_banco,$usuario_banco,$fk_usuario);

        return $result;
    }

    public function getBancoByUsuario($fk_usuario,$opt = "",$sel = null){
        $result = $this->repo()->getBancoByUsuario($fk_usuario,$opt,$sel);

        return $result;
    }

    public function getAllBancos(){
        $result = $this->repo()->getAllBancos();

        return $result;
    }

    public function updateConfig($id_banco, $nome_banco, $usuario_banco, $senha_banco, $host, $porta){
        return $this->repo()->updateConfig($id_banco, $nome_banco, $usuario_banco, $senha_banco, $host, $porta);
    }

    public function delete($id_banco){
        return $this->repo()->delete($id_banco);
    }
}