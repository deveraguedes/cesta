<?php

include_once('conexao.class.php');

class Usuarios {

    public $cod_usuario;
    public $vch_nome;
    public $vch_login;
    public $vch_senha;
    public $int_nivel;
    public $vch_email;
    public $data_cadastro;
    public $date_alteracao;
    public $cod_categoria_usuario;
    public $vch_status;
    public $cod_funcionario;
    public $cod_unidade;

    public $token;

    public function setCod_usuario($cod_usuario) {
        $this->cod_usuario = $cod_usuario;
    }

    public function setVch_nome($vch_nome) {
        $this->vch_nome = $vch_nome;
    }

    public function setVch_login($vch_login) {
        $this->vch_login = $vch_login;
    }

    public function setVch_senha($vch_senha) {
        $this->vch_senha = $vch_senha;
    }

    public function setInt_nivel($int_nivel) {
        $this->int_nivel = $int_nivel;
    }

    public function setDate_cadastro($data_cadastro) {
        $this->data_cadastro = $data_cadastro;
    }

    public function setCod_unidade($cod_unidade) {
        $this->cod_unidade = $cod_unidade;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function getCod_usuario() {
        return $this->cod_usuario;
    }

    public function getVch_nome() {
        return $this->vch_nome;
    }

    public function getVch_login() {
        return $this->vch_login;
    }

    public function getVch_senha() {
        return $this->vch_senha;
    }

    public function getInt_nivel() {
        return $this->int_nivel;
    }

    public function getVch_email() {
        return $this->vch_email;
    }

    public function getDate_cadastro() {
        return $this->date_cadastro;
    }

    public function getDate_alteracao() {
        return $this->date_alteracao;
    }

    public function getCod_categoria_usuario() {
        return $this->cod_categoria_usuario;
    }

    public function getVch_status() {
        return $this->vch_status;
    }

    public function getCod_funcionario() {
        return $this->cod_funcionario;
    }

    public function getCod_empresa() {
        return $this->cod_empresa;
    }

    public function getToken() {
        return $this->token;
    }

    /*
    public function inserirUsuarios() {
        try {
            $pdo = Database::conexao();

            $consulta = $pdo->prepare("INSERT into beneficiario.usuario( vch_nome, vch_login, cod_unidade, vch_senha, int_nivel, data_cadastro)
                   values ( :vch_nome, :vch_login, :cod_unidade, :vch_senha, :int_nivel, :data_cadastro);");

            //$consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':vch_nome', $this->vch_nome);
            $consulta->bindParam(':vch_login', $this->vch_login);
            $consulta->bindParam(':vch_senha', $this->vch_senha);
            $consulta->bindParam(':int_nivel', $this->int_nivel);
            $consulta->bindParam(':cod_unidade', $this->cod_unidade);
            $consulta->bindParam(':data_cadastro', $this->data_cadastro);

            $consulta->execute();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }
*/

    public function inserirUsuarios() {
        try {
            $pdo = Database::conexao();
            $pdo->beginTransaction();
            $this->vch_login = $this->gerar_login();
            $this->vch_senha_plain = $this->gerar_senha();
            $resultado = $this->consultarUsuarioLogin($this->vch_login);

            while(count($resultado) != 0){
                $this->vch_login= $this->gerar_login();
                $this->vch_senha_plain = $this->gerar_senha();
                $resultado = $this->consultarUsuarioLogin($this->vch_login);
            }
            $this->vch_senha=(md5($this->vch_senha_plain));
            $consulta = $pdo->prepare("INSERT into beneficiario.usuario( vch_nome, vch_login, cod_unidade, vch_senha, int_nivel, data_cadastro)
                   values ( :vch_nome, :vch_login, :cod_unidade, :vch_senha, :int_nivel, :data_cadastro);");
            //$consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':vch_nome', $this->vch_nome);
            $consulta->bindParam(':vch_login', $this->vch_login);
            $consulta->bindParam(':vch_senha', $this->vch_senha);
            $consulta->bindParam(':int_nivel', $this->int_nivel);
            $consulta->bindParam(':cod_unidade', $this->cod_unidade);
            $consulta->bindParam(':data_cadastro', $this->data_cadastro);
            $consulta->execute();
            
            $consulta = $pdo->prepare("INSERT into beneficiario.usuario_temporario (vch_senha, cod_usuario)
            values(:vch_senha_plain, :cod_usuario)");
    
            $this->cod_usuario = $pdo->lastInsertId();
            $consulta->bindParam(':vch_senha_plain', $this->vch_senha_plain);
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->execute();
            $pdo->commit();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            die(var_dump($e));
            echo "Ocorreu um erro: $e";
            $pdo->rollBack();
        }
    }



    public function alterarUsuarios() {
        $pdo = Database::conexao();
        try {
            $consulta = $pdo->prepare("UPDATE beneficiario.usuario SET vch_nome = :vch_nome, vch_login = :vch_login, 
              vch_senha = :vch_senha, int_nivel = :int_nivel WHERE cod_usuario = :cod_usuario;");
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':vch_nome', $this->vch_nome);
            $consulta->bindParam(':vch_login', $this->vch_login);
            $consulta->bindParam(':vch_senha', $this->vch_senha);
            $consulta->bindParam(':int_nivel', $this->int_nivel);

            $consulta->execute();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }

    public function excluirUsuarios() {
        $pdo = Database::conexao();
        try {
            $consulta = $pdo->prepare("DELETE FROM beneficiario.usuario WHERE cod_usuario = :cod_usuario;");
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);


            $consulta->execute();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }

    public function deletarUsuarios($cod_usuario) {
        $pdo = Database::conexao();
        try {
            $consulta = $pdo->prepare("DELETE FROM beneficiario.usuario WHERE cod_usuario = :cod_usuario;");
            $consulta->bindParam(':cod_usuario', $cod_usuario);


            $consulta->execute();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }



    public function exibirUsuarios() {
        $pdo = Database::conexao();
        $sql = "SELECT * FROM beneficiario.usuario order by cod_usuario DESC";
        $consulta = $pdo->prepare($sql);
        $consulta->execute();
        return $consulta;
    }

    public function exibirUsuarioCod($cod) {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.usuario where cod_usuario = $cod";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }

    public function localizarUsuarios($nome) {
        $pdo = Database::conexao();
        $sql = "SELECT * FROM beneficiario.usuario where  vch_nome like '%$nome%'";
        $consulta = $pdo->prepare($sql);
        $consulta->execute();
        return $consulta;
    }

    public function consultarUsuarioLogin($login){
        try{
            $pdo = Database::conexao();
            $sql="SELECT * FROM beneficiario.usuario where vch_login like '$login'";
            $consulta = $pdo->query($sql)->fetchAll();
            return $consulta;
        }catch(PDOException $e){
            echo "Ocorreu um erro: $e";
        }
    }



    public function gerar_login(){
        $letras = "abcdefghijklmnopqrstuvwxyz";
        $login = "";
          for($i = 0; $i<4; $i++){
            $login .= $letras[random_int(0,25)]; 
          }
           return $login;
        }
    
      public function gerar_senha(){
        $numeros = "123456789";
        $senha = "";
          for($i = 0; $i<4; $i++){
            $senha .=  $numeros[random_int(0,8)]; 
          }
          return $senha;
        }



        public function dadosTemporarios(){
            $pdo = Database::conexao();
            $sql = "SELECT usuario.vch_nome, usuario.vch_login, usuario_temporario.vch_senha, unidade.vch_unidade, usuario.cod_usuario
                        FROM beneficiario.usuario
                        INNER JOIN beneficiario.usuario_temporario
                        on usuario_temporario.cod_usuario = usuario.cod_usuario
                        INNER JOIN beneficiario.unidade
                        on unidade.cod_unidade = usuario.cod_unidade
                        order by usuario.vch_nome";
           
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        }
        
  

}

?>