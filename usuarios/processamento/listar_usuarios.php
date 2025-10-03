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
// Evitar que avisos/erros em HTML corrompam a resposta JSON
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../classes/usuarios.class.php';

try {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Sessão expirada. Por favor, faça login novamente.');
    }
    
    // Obter nível e unidade do usuário logado
    $int_nivel   = $_SESSION['int_level'] ?? 2;
    $cod_unidade = $_SESSION['cod_unidade'] ?? 0;

    // Permitir filtro por unidade via GET. Admin (nivel 1) pode usar 0 para listar todas.
    $reqUnidade = filter_input(INPUT_GET, 'unidade', FILTER_VALIDATE_INT);
    if ($reqUnidade !== null) {
        if ($int_nivel == 1) {
            $cod_unidade = max(0, (int)$reqUnidade); // 0 = todas as unidades
        } else {
            // Níveis não-admin não podem ver "todas"; mantém a unidade da sessão quando 0
            $cod_unidade = ($reqUnidade > 0) ? (int)$reqUnidade : (int)$cod_unidade;
        }
    }

    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $per  = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 6;

    // Usar a classe Usuario (nova) se disponível, senão usar Usuarios (antiga)
    if (class_exists('Usuario')) {
        $usuario = new Usuario();
        $pag = $usuario->listarPaginada((int)$cod_unidade, $page, $per, $int_nivel);
    } else {
        $usuario = new Usuarios();
        $consulta = $usuario->exibirUsuarios((int)$cod_unidade, $int_nivel);
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