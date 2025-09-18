<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
error_reporting(0);
ini_set('display_errors','Off');

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Não autenticado']);
    exit;
}

require_once __DIR__ . '/../classes/usuarios.class.php';
$u = new Usuario();

try {
    $cod  = filter_input(INPUT_POST, 'cod_usuario', FILTER_VALIDATE_INT);
    $data = [
      'vch_nome'    => $_POST['vch_nome'] ?? '',
      'vch_login'   => $_POST['vch_login'] ?? '',
      'cod_unidade' => $_POST['cod_unidade'] ?? 0,
      'vch_senha'   => $_POST['vch_senha'] ?? ''
    ];

    if (!$cod) {
        throw new InvalidArgumentException('ID inválido');
    }

    $ok = $u->atualizar($cod, $data);
    echo json_encode(['success'=>$ok]);
} catch (InvalidArgumentException $ex) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>$ex->getMessage()]);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Erro interno']);
}
