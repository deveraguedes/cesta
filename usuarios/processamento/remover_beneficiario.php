<?php
session_start();
header('Content-Type: application/json');
require_once '../../classes/beneficiario.class.php';
require_once '../../classes/usuario.class.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.']);
    exit;
}

// Verificar se os parâmetros necessários foram fornecidos
if (($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST["cod_beneficiario"]) || !isset($_POST["cod_unidade"]))) ||
    ($_SERVER['REQUEST_METHOD'] === 'GET' && (!isset($_GET["cod_beneficiario"]) || !isset($_GET["cod_usuario"])))) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros insuficientes.']);
    exit;
}

// Obter parâmetros da requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_beneficiario = $_POST["cod_beneficiario"];
    $cod_unidade = $_POST["cod_unidade"];
    $cod_usuario = $_SESSION['user_id'];
} else {
    $cod_beneficiario = $_GET["cod_beneficiario"];
    $cod_usuario = $_GET["cod_usuario"];
    $cod_unidade = $_SESSION['cod_unidade'];
}

// Verificar permissões do usuário
$u = new Usuarios();
$dados_usuario = $u->exibirUsuarioCod($cod_usuario)->fetch(PDO::FETCH_ASSOC);

if ($dados_usuario['nivel'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Você não tem permissão para realizar esta operação.']);
    exit;
}

// Criar e popular o objeto beneficiário
$b = new Beneficiario();
$b->setCod_beneficiario($cod_beneficiario);
$b->setCod_usuario($cod_usuario);
$b->setCod_unidade($cod_unidade);
$b->setSituacao(0);

// Processar a remoção
$resultado = $b->excluirBeneficiario();


// Retornar resultado
if ($resultado) {
    echo json_encode(['success' => true, 'message' => 'Beneficiário removido da cesta com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao remover beneficiário da cesta.']);
}
?>
