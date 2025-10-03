<?php
session_start();

include_once "../../classes/usuarios.class.php";
include_once "../../classes/login.class.php";
include_once "../../classes/conexao.class.php";

// Verifica se o usuário está logado
$l = new LoginUsuario();
if (!$l->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Verifica se foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../alterar_senha.php?response=6');
    exit;
}

// Recebe os dados do formulário
$senha_atual = $_POST['senha_atual'] ?? '';
$senha_nova = $_POST['senha_nova'] ?? '';
$senha_confirmar = $_POST['senha_confirmar'] ?? '';

// Validações básicas
if (empty($senha_atual) || empty($senha_nova) || empty($senha_confirmar)) {
    header('Location: ../alterar_senha.php?response=4');
    exit;
}

if (strlen($senha_nova) < 6) {
    header('Location: ../alterar_senha.php?response=5');
    exit;
}

if ($senha_nova !== $senha_confirmar) {
    header('Location: ../alterar_senha.php?response=3');
    exit;
}

try {
    $pdo = Database::conexao();
    $user_id = $_SESSION['user_id'];
    
    // Busca a senha atual do usuário no banco
    $sql = "SELECT vch_senha FROM beneficiario.usuario WHERE cod_usuario = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        header('Location: ../alterar_senha.php?response=6');
        exit;
    }
    
    // Verifica se a senha atual está correta (suporte a diferentes formatos de hash)
    $storedHash = $usuario['vch_senha'];
    $senhaValida = false;
    
    // 1. Custom hash (UTF-16LE MD5, uppercase hex)
    $uni = iconv('UTF-8', 'UTF-16LE', $senha_atual);
    $customHash = strtoupper(bin2hex(hash('md5', $uni, true)));
    if ($customHash === $storedHash) {
        $senhaValida = true;
    }
    // 2. Legacy plain MD5 (32-char lowercase)
    elseif (strlen($storedHash) === 32 && md5($senha_atual) === $storedHash) {
        $senhaValida = true;
    }
    // 3. password_hash (bcrypt, etc)
    elseif (password_verify($senha_atual, $storedHash)) {
        $senhaValida = true;
    }
    
    if (!$senhaValida) {
        header('Location: ../alterar_senha.php?response=2');
        exit;
    }
    
    // Criptografa a nova senha
    $senha_nova_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
    
    // Atualiza a senha no banco
    $sql_update = "UPDATE beneficiario.usuario 
                   SET vch_senha = :senha_nova, 
                       data_alteracao = CURRENT_TIMESTAMP 
                   WHERE cod_usuario = :user_id";
    
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':senha_nova', $senha_nova_hash, PDO::PARAM_STR);
    $stmt_update->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($stmt_update->execute()) {
        header('Location: ../alterar_senha.php?response=1');
        exit;
    } else {
        header('Location: ../alterar_senha.php?response=6');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Erro ao alterar senha: " . $e->getMessage());
    header('Location: ../alterar_senha.php?response=6');
    exit;
}
?>