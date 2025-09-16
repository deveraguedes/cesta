<?php
require_once 'conexao.class.php';
session_start();

class LoginUsuario {
    private $db;

    public function __construct() {
        $this->db = Database::conexao();
    }

    public function login($vch_login, $senha) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM beneficiario.usuario WHERE vch_login = :login LIMIT 1");
            $stmt->bindParam(':login', $vch_login, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $storedHash = $user['vch_senha'];

                // Support both md5 and password_hash formats
                $valid = false;
                if (strlen($storedHash) === 32) {
                    // Legacy md5
                    $valid = (md5($senha) === $storedHash);
                } else {
                    // Modern hash
                    $valid = password_verify($senha, $storedHash);
                }

                if ($valid) {
                    $_SESSION['user_session'] = $user['cod_usuario'];
                    $_SESSION['usuarioNome'] = $user['vch_login'];
                    $_SESSION['int_nivel'] = $user['int_nivel'];
                    $_SESSION['cod_unidade'] = $user['cod_unidade'];
                    $_SESSION['sessiontime'] = time() + 10000;
                    return true;
                }
            }

            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_session']);
    }

    public function logout($redirect = '../beneficiario.php') {
        session_unset();
        session_destroy();
        header("Location: $redirect");
        exit;
    }

    public function refreshSessionTime($tempo) {
        if (isset($tempo)) {
            if ($tempo < time()) {
                $this->logout('../index.php');
            } else {
                $_SESSION['sessiontime'] = time() + 3000;
            }
        }
    }

    public function recuperarSenha($email) {
        $stmt = $this->db->prepare("SELECT * FROM beneficiario.usuarios WHERE vch_email = :email AND status = 2 LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'token' => $user['token'],
                'cod_usuario' => $user['cod_usuario'],
                'email' => $user['vch_email']
            ];
        }

        return false;
    }
}
?>
