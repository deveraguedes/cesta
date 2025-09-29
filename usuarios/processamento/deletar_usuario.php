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
// 4) Enforce unidade restriction for level 2/3: they can only delete users in their unidade
if (isset($_SESSION['int_level']) && ((int)$_SESSION['int_level'] === 2 || (int)$_SESSION['int_level'] === 3)) {
  // fetch target user's unidade
  $pdo = Database::conexao();
  $stmt = $pdo->prepare('SELECT cod_unidade FROM beneficiario.usuario WHERE cod_usuario = :cod');
  $stmt->execute([':cod' => $cod]);
  $targetUn = $stmt->fetchColumn();
  if ($targetUn === false) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Usuário não encontrado']);
    exit;
  }
  if ((int)$targetUn !== (int)($_SESSION['cod_unidade'] ?? 0)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permissão negada']);
    exit;
  }
}

if (!$u->deletar($cod)) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Falha ao excluir']);
} else {
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(['success' => true]);
  exit;
}
