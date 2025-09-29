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
error_reporting(E_ALL);
ini_set('display_errors','On');

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Não autenticado']);
    exit;
}

require_once __DIR__ . '/../../classes/usuarios.class.php';
$u = new Usuario();

try {
    $cod  = filter_input(INPUT_POST, 'cod_usuario', FILTER_VALIDATE_INT);
    $data = [
      'vch_nome'    => $_POST['vch_nome'] ?? '',
      'vch_login'   => $_POST['vch_login'] ?? '',
            'cod_unidade' => $_POST['cod_unidade'] ?? 0,
            'vch_senha'   => $_POST['vch_senha'] ?? '',
            'int_nivel'   => isset($_POST['int_nivel']) ? (int)$_POST['int_nivel'] : null
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
