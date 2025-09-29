<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../classes/beneficiario.class.php';

// Permitir apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Verificar permissão de nível (ex: só admin pode remover)
    if (($_SESSION['int_nivel'] ?? 0) != 1) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
        exit;
    }

    // Validar entrada
    if (empty($_POST['cod_beneficiario']) || empty($_POST['cod_unidade'])) {
        echo json_encode(['success' => false, 'message' => 'Dados insuficientes.']);
        exit;
    }

    $beneficiario = new Beneficiario();
    $beneficiario->setCod_beneficiario($_POST['cod_beneficiario']);
    $beneficiario->setCod_usuario($_SESSION['user_id'] ?? 0);
    $beneficiario->setCod_unidade($_POST['cod_unidade']);
    $beneficiario->setSituacao(0); // inativo

    if ($beneficiario->excluirBeneficiario()) {
        echo json_encode(['success' => true, 'message' => 'Beneficiário removido com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover beneficiário.']);
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
