<?php
namespace app\tools\gerador;



class GeradorConfig{


    public static function gerarConfig(){
        $arquivo = <<<PHP
            <?php
            define('DEV_ENVIRONMENT', true); // Mostra erros de desenvolvimento

            if (DEV_ENVIRONMENT == true) {
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);
            }

            
            define('DB_HOST',''); // Insira aqui seu host do banco de dados
            define('DB_NAME',''); // Insira aqui o nome do seu banco de dados
            define('DB_USER',''); // Insira aqui o nome do usuário do banco de dados
            define('DB_PASS',''); // Insira aqui a senha do banco de dados

PHP;
    }
}