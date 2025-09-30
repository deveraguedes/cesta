<?php

/**
 * Classe de conexão ao banco de dados usando PDO no padrão Singleton.
 * Modo de Usar:
 * $db = Database::conexao();
 * E agora use as funções do PDO (prepare, query, exec) em cima da variável $db.
*/

class Database
{
    # Variável que guarda a conexão PDO.
    protected static $db;


    # Private construct - garante que a classe só possa ser instanciada internamente.
    private function __construct()
    {
        # Informações sobre o banco de dados:
       // $db_host = "10.0.1.56";
        $db_host = "10.0.1.7";
        //$db_host = "127.0.0.1";
        $db_nome = "sedes";
        $db_usuario = "sistemas";
        $db_senha = "geprosistemas";
        $db_driver = "pgsql";

        // $db_usuario = "root";
        // $db_senha = ""; 
        // $db_driver = "mysql";
        # Informações sobre o sistema:
        $sistema_titulo = "pascoa";
        $sistema_email = "alguem@gmail.com";
        //$db_nome2 = "siscomb_balanca";
        //dbcompras
        # Informações sobre o banco de dados:

        try {
            # Atribui o objeto PDO à variável $db.
            self::$db = new PDO("$db_driver:host=$db_host; dbname=$db_nome", $db_usuario, $db_senha);
            //self::$db2 = new PDO("$db_driver:host=$db_host; dbname=$db_nome2", $db_usuario, $db_senha);
            # Garante que o PDO lance exceções durante erros.
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //self::$db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            # Garante que os dados sejam armazenados com codificação UFT-8.
            // self::$db->exec('SET NAMES utf8');


            //self::$db2->exec('SET NAMES utf8');
        } catch (PDOException $e) {
            # Envia um e-mail para o e-mail oficial do sistema, em caso de erro de conexão.
            // mail($sistema_email, "PDOException em $sistema_titulo", $e->getMessage());
            # Então não carrega nada mais da página.
            die("Connection Error: " . $e->getMessage());
        }
    }
    # Método estático - acessível sem instanciação.
    public static function conexao()
    {
        # Garante uma única instância. Se não existe uma conexão, criamos uma nova.
        if (!self::$db) {
            new Database();
        }
        # Retorna a conexão.
        return self::$db;
        /*
		if (!self::$db2)
        {
            new Database();
        }
        # Retorna a conexão.
        return self::$db2;*/
    }
}