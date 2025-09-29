<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');

include_once('../../classes/beneficiario.class.php');

// Verificar login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sessão expirada. Por favor, faça login novamente.'
    ]);
    exit;
}

$cod_usuario = $_SESSION['user_id'];
$cod_unidade = $_SESSION['cod_unidade'] ?? null;

// Verificar parâmetros obrigatórios
if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST["situacao"], $_POST["cod_beneficiario"], $_POST["cod_unidade"])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros insuficientes.']);
    exit;
}

$situacao = (int) $_POST["situacao"]; // 1 = inserir, 0 = remover
$cod_beneficiario = (int) $_POST["cod_beneficiario"];
$cod_unidade = (int) $_POST["cod_unidade"];

try {
    $b = new Beneficiario();
    $b->setCod_beneficiario($cod_beneficiario);
    $b->setCod_usuario($cod_usuario);
    $b->setCod_unidade($cod_unidade);
    $b->setSituacao($situacao);

    if ($situacao === 1) {
        // Inserir beneficiário na cesta
        $b->incluirBeneficiario();
        echo json_encode([
            'success' => true,
            'message' => 'Beneficiário inserido na cesta com sucesso.'
        ]);
    } elseif ($situacao === 0) {
        // Remover beneficiário da cesta
        $b->excluirBeneficiario();
        echo json_encode([
            'success' => true,
            'message' => 'Beneficiário removido da cesta com sucesso.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Valor de situação inválido.'
        ]);
    }
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}
