<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include_once(__DIR__ . '/../../classes/beneficiario.class.php');
include_once(__DIR__ . '/../../classes/usuarios.class.php');
include_once(__DIR__ . '/../../classes/categoria.class.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $beneficiario = new Beneficiario();

    // Atribuir valores do formulário
    $beneficiario->setNis($_POST['nis'] ?? null);
    $beneficiario->setCpf($_POST['cpf'] ?? null);
    $beneficiario->setNome($_POST['nome'] ?? null);
    $beneficiario->setCod_bairro($_POST['cod_bairro'] ?? null);
    $beneficiario->setEndereco($_POST['endereco'] ?? null);
    $beneficiario->setComplemento($_POST['complemento'] ?? null);
    $beneficiario->setTelefone($_POST['telefone'] ?? null);
    $beneficiario->setCod_tipo($_POST['cod_tipo'] ?? null);
    $beneficiario->setCod_usuario($_POST['cod_usuario'] ?? ($_SESSION['user_id'] ?? null));
    $beneficiario->setSituacao(1); // Ativo por padrão

    // Controle de nível: apenas nível 1 define categoria
    if (!empty($_POST['cod_categoria']) && ($_SESSION['int_nivel'] ?? 0) == 1) {
        $categoria = new Categoria();
        $categoriaObj = $categoria->buscarPorId($_POST['cod_categoria']);
        if ($categoriaObj) {
            $beneficiario->setCategoria($categoriaObj['cod_categoria']);
        }
    }

    // Inserir beneficiário
    if ($beneficiario->inserirBeneficiario()) {
        echo json_encode(['success' => true, 'message' => 'Beneficiário cadastrado com sucesso']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar beneficiário']);
        exit;
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
