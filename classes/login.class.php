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
            error_log("[LOGIN DEBUG] Tentando login para: $vch_login");
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
                error_log("[LOGIN DEBUG] Usuário não encontrado: $vch_login");
                return false;
            }

            $storedHash = $user['vch_senha'];
            $valid = false;

            // legacy MD5?
                // 1. Custom hash (UTF-16LE MD5, uppercase hex)
                $uni = iconv('UTF-8', 'UTF-16LE', $senha);
                $customHash = strtoupper(bin2hex(hash('md5', $uni, true)));
                if ($customHash === $storedHash) {
                    error_log("[LOGIN DEBUG] Senha válida por hash custom para $vch_login");
                    $valid = true;
                }
                // 2. Legacy plain MD5 (32-char lowercase)
                elseif (strlen($storedHash) === 32 && md5($senha) === $storedHash) {
                    error_log("[LOGIN DEBUG] Senha válida por legacy MD5 para $vch_login");
                    $valid = true;
                }
                // 3. password_hash (bcrypt, etc)
                elseif (password_verify($senha, $storedHash)) {
                    error_log("[LOGIN DEBUG] Senha válida por password_verify para $vch_login");
                    $valid = true;
                } else {
                    error_log("[LOGIN DEBUG] Senha inválida para $vch_login. Hash esperado: $storedHash, hash calculado: $customHash");
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

            error_log("[LOGIN DEBUG] Login bem-sucedido para $vch_login");
            return true;
        } catch (PDOException $e) {
            error_log("[LOGIN ERROR] " . $e->getMessage());
            return false;
        }
    }

    public function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }

    public function logout(string $redirect = 'index.php'): void {
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
