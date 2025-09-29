<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

session_start();
include_once('../../classes/beneficiario.class.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sessão expirada. Por favor, faça login novamente.']);
    exit;
}

// Obter dados da sessão
$cod_usuario = $_SESSION['user_id'];
$cod_unidade = $_SESSION['cod_unidade'];

// Verificar se os parâmetros necessários foram fornecidos
if (!isset($_GET["situacao"]) || !isset($_GET["cod_beneficiario"])) {
    echo json_encode(['status' => 'error', 'message' => 'Parâmetros insuficientes.']);
    exit;
}

// Obter parâmetros da requisição
$situacao = $_GET["situacao"];
$cod_beneficiario = $_GET["cod_beneficiario"];

// Criar e popular o objeto
$b = new Beneficiario();
$b->setCod_beneficiario($cod_beneficiario);
$b->setCod_usuario($cod_usuario);
$b->setCod_unidade($cod_unidade);

// Processar a alteração de situação
$resultado = false;
$mensagem = '';

if ($situacao == 1) {
    // Desativar beneficiário
    $situacao = 0;
    $b->setSituacao($situacao);
    $resultado = $b->excluirBeneficiario();
    $mensagem = 'Beneficiário desativado com sucesso.';
} else if ($situacao == 0) {
    // Ativar beneficiário
    $situacao = 1;
    $b->setSituacao($situacao);
    $resultado = $b->incluirBeneficiario();
    $mensagem = 'Beneficiário ativado com sucesso.';
}

// Retornar resposta em formato JSON para processamento AJAX
if ($resultado) {
    echo json_encode(['status' => 'success', 'message' => $mensagem, 'nova_situacao' => $situacao]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao alterar situação do beneficiário.']);
}