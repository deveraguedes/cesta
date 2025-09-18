<?php
require_once 'conexao.class.php';
if (session_status() === PHP_SESSION_NONE ) {
    session_start();
}

class LoginUsuario {
    private $db;

    public function __construct() {
        $this->db = Database::conexao();
    }

    /**
     * Tenta autenticar e, em caso de sucesso,
     * armazena no $_SESSION:
     *   - user_id       → cod_usuario
     *   - usuarioNome   → vch_nome (ou login)
     *   - int_level     → int_nivel
     *   - cod_unidade   → cod_unidade
     *   - sessiontime   → timestamp de expiração
     */
    public function login(string $vch_login, string $senha): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT cod_usuario
                     , vch_login
                     , vch_nome
                     , vch_senha
                     , int_nivel
                     , cod_unidade
                  FROM beneficiario.usuario
                 WHERE vch_login = :login
                  LIMIT 1
            ");
            $stmt->bindParam(':login', $vch_login, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                return false;
            }

            $storedHash = $user['vch_senha'];
            $valid = false;

            // legacy MD5?
            if (strlen($storedHash) === 32 && md5($senha) === $storedHash) {
                $valid = true;
            }
            // bcrypt ou password_hash()
            elseif (password_verify($senha, $storedHash)) {
                $valid = true;
            }

            if (!$valid) {
                return false;
            }

            // regenerar ID de sessão
            session_regenerate_id(true);

            // grava os dados que o front-end espera
            $_SESSION['user_id']       = $user['cod_usuario'];
            $_SESSION['usuarioNome']   = $user['vch_nome'];
            $_SESSION['int_level']     = (int)$user['int_nivel'];
            $_SESSION['cod_unidade']   = (int)$user['cod_unidade'];
            $_SESSION['sessiontime']   = time() + 1200; // 20 min de inatividade

            return true;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }

    public function logout(string $redirect = '../beneficiario.php'): void {
        session_unset();
        session_destroy();
        header("Location: $redirect");
        exit;
    }

    /**
     * Verifica se a sessão expirou e, se não, renova o tempo.
     */
    public function refreshSessionTime(): void {
        if (empty($_SESSION['sessiontime']) || $_SESSION['sessiontime'] < time()) {
            $this->logout('../index.php');
        }
        // renova 20 min a partir de agora
        $_SESSION['sessiontime'] = time() + 1200;
    }
}
?>
