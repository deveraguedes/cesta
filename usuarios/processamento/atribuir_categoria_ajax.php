<?php
session_start();
header('Content-Type: application/json');

include_once "../classes/login.class.php";
include_once "../classes/categoria.class.php";
include_once "../classes/beneficiario.class.php";
include_once('conexao.class.php');

$l = new LoginUsuario();
if (!$l->isLoggedIn() || $_SESSION['int_level'] != 1) {
    echo json_encode(["success" => false, "message" => "Acesso negado."]);
    exit;
}

$cod_beneficiario = $_POST['cod_beneficiario'] ?? null;
$cod_categoria = $_POST['cod_categoria'] ?? null;

if (!$cod_beneficiario || !$cod_categoria) {
    echo json_encode(["success" => false, "message" => "Dados inválidos."]);
    exit;
}

try {
    $pdo = Database::conexao();
    $sql = "UPDATE beneficiario.beneficiario 
               SET cod_categoria = :cod_categoria 
             WHERE cod_beneficiario = :cod_beneficiario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cod_categoria', $cod_categoria, PDO::PARAM_INT);
    $stmt->bindParam(':cod_beneficiario', $cod_beneficiario, PDO::PARAM_INT);
    $stmt->execute();

    // Buscar nome da categoria
    $catStmt = $pdo->prepare("SELECT vch_categoria FROM beneficiario.categoria WHERE cod_categoria = :id");
    $catStmt->bindParam(':id', $cod_categoria, PDO::PARAM_INT);
    $catStmt->execute();
    $catNome = $catStmt->fetchColumn();

    echo json_encode([
        "success" => true,
        "cod_beneficiario" => $cod_beneficiario,
        "cod_categoria" => $cod_categoria,
        "categoria_nome" => $catNome
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro ao salvar: " . $e->getMessage()]);
}
