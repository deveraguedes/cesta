<?php
session_start();
include_once "../classes/beneficiario.class.php";
include_once "../classes/conexao.class.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Sessão expirada. Por favor, faça login novamente.'); window.location='../usuarios/login.php';</script>";
    exit;
}

// Verificar se os parâmetros necessários foram fornecidos
if (!isset($_GET['cod_beneficiario']) || !isset($_GET['cod_usuario'])) {
    echo "<script>alert('Parâmetros inválidos.'); window.location='../beneficiario.php';</script>";
    exit;
}

$cod_beneficiario = (int)$_GET['cod_beneficiario'];
$cod_usuario = (int)$_GET['cod_usuario'];
$cod_unidade = $_SESSION['cod_unidade'];

// Instanciar a classe Beneficiario
$b = new Beneficiario();
$b->setCod_beneficiario($cod_beneficiario);
$b->setCod_usuario($cod_usuario);
$b->setCod_unidade($cod_unidade);
$b->setSituacao(1); // 1 = Incluído na cesta

try {
    // Incluir o beneficiário na cesta (o método já valida saldo e ajusta)
    $ok = $b->incluirBeneficiario();
    if ($ok) {
        echo "<script>alert('Beneficiário incluído com sucesso!'); window.location='../beneficiario.php';</script>";
    } else {
        echo "<script>alert('Não foi possível incluir o beneficiário.'); window.location='../beneficiario.php';</script>";
    }
} catch (Throwable $e) {
    echo "<script>alert('Erro ao incluir beneficiário: " . htmlspecialchars($e->getMessage()) . "'); window.location='../beneficiario.php';</script>";
}
?>