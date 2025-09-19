<?php

// Block direct access by GET or non-AJAX
if (php_sapi_name() !== 'cli') {
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !$isAjax) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../classes/usuarios.class.php';

try {
    $codUn  = filter_input(INPUT_GET, 'unidade',  FILTER_VALIDATE_INT) ?: 0;
    $page   = filter_input(INPUT_GET, 'page',     FILTER_VALIDATE_INT) ?: 1;
    $per    = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 6;

    $usuario = new Usuario();
    $pag     = $usuario->listarPaginada($codUn, $page, $per);

    echo json_encode([
        'success'  => true,
        'data'     => $pag['data'],
        'total'    => $pag['total'],
        'page'     => $pag['page'],
        'per_page' => $pag['per_page'],
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;