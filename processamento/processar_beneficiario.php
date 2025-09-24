<?php
session_start();
require_once '../classes/beneficiario.class.php';
require_once '../classes/categoria.class.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $beneficiario = new Beneficiario();
    
    // Atribuir valores do formulário
    $beneficiario->setNis($_POST['nis'] ?: null);
    $beneficiario->setCpf($_POST['cpf'] ?: null);
    $beneficiario->setNome($_POST['nome']);
    $beneficiario->setCod_bairro($_POST['cod_bairro']);
    $beneficiario->setEndereco($_POST['endereco']);
    $beneficiario->setComplemento($_POST['complemento'] ?: null);
    $beneficiario->setTelefone($_POST['telefone'] ?: null);
    $beneficiario->setCod_tipo($_POST['cod_tipo']);
    $beneficiario->setCod_usuario($_POST['cod_usuario']);
    $beneficiario->setSituacao(1); // Ativo por padrão
    
    // Se for admin e enviou categoria
    if ($_SESSION['int_level'] == 1 && !empty($_POST['cod_categoria'])) {
        $categoria = new Categoria();
        if ($categoriaObj = $categoria->buscarPorId($_POST['cod_categoria'])) {
            $beneficiario->setCategoria($categoriaObj);
        }
    }
    
    // Inserir beneficiário
    if ($beneficiario->inserirBeneficiario()) {
        echo json_encode(['success' => true, 'message' => 'Beneficiário cadastrado com sucesso']);
    } else {
        throw new Exception("Erro ao cadastrar beneficiário");
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}