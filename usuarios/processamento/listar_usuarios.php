<?php

// Block direct access by GET or non-AJAX
if (php_sapi_name() !== 'cli') {
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !$isAjax) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../classes/usuarios.class.php';

try {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Sessão expirada. Por favor, faça login novamente.');
    }
    
    // Obter nível e unidade do usuário logado
    $int_nivel = $_SESSION['int_level'] ?? 2;
    $cod_unidade = $_SESSION['cod_unidade'] ?? 0;
    

        
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $per  = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 6;

    // Usar a classe Usuario (nova) se disponível, senão usar Usuarios (antiga)
    if (class_exists('Usuario')) {
        $usuario = new Usuario();
        $pag = $usuario->listarPaginada($codUn, $page, $per, $int_nivel);
    } else {
        $usuario = new Usuarios();
        $consulta = $usuario->exibirUsuarios($codUn, $int_nivel);
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
        
        // Simular paginação para manter compatibilidade
        $total = count($data);
        $offset = ($page - 1) * $per;
        $paginatedData = array_slice($data, $offset, $per);
        
        $pag = [
            'data' => $paginatedData,
            'total' => $total,
            'page' => $page,
            'per_page' => $per
        ];
    }

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