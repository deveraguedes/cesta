<?php

// Block direct access by GET or non-AJAX
if (php_sapi_name() !== 'cli') {
  $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$isAjax) {
    http_response_code(403);
    exit('Acesso negado.');
  }
}

session_start();
header('Content-Type: application/json; charset=UTF-8');

// 1) só usuário logado pode deletar
if (empty($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Não autenticado']);
  exit;
}

// 2) incluímos sua classe corretamente
require_once '../../classes/usuarios.class.php';
$u = new Usuario();

// 3) lê do POST
$cod = filter_input(INPUT_POST, 'cod', FILTER_VALIDATE_INT);
if (!$cod) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'ID inválido']);
  exit;
}

// 4) chama o método deletar()
if (!$u->deletar($cod)) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Falha ao excluir']);
} else {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(['success' => true]);
  exit;
}
