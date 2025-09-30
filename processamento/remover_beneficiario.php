<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../usuarios/login.php');
    exit;
}

// Verificar se os parâmetros necessários foram enviados
if (!isset($_GET['cod_beneficiario']) || !isset($_GET['cod_usuario'])) {
    header('Location: ../beneficiario.php?erro=parametros_invalidos');
    exit;
}

// Incluir a classe Beneficiario
require_once '../classes/beneficiario.class.php';

// Obter os parâmetros
$cod_beneficiario = (int)$_GET['cod_beneficiario'];
$cod_usuario = (int)$_GET['cod_usuario'];

// Instanciar a classe Beneficiario
$beneficiario = new Beneficiario();

// Definir os valores necessários
$beneficiario->setCod_beneficiario($cod_beneficiario);
$beneficiario->setCod_usuario($cod_usuario);
$beneficiario->setSituacao(0); // 0 = removido da cesta

try {
    // Obter a unidade do beneficiário antes de excluir
    $pdo = Database::conexao();
    $stmt = $pdo->prepare("SELECT cod_unidade FROM beneficiario.beneficiario WHERE cod_beneficiario = :cod_beneficiario");
    $stmt->bindParam(':cod_beneficiario', $cod_beneficiario);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $beneficiario->setCod_unidade($result['cod_unidade']);
        
        // Executar a exclusão (remoção da cesta)
        $beneficiario->excluirBeneficiario();
        
        // Redirecionar de volta para a página de beneficiários com mensagem de sucesso
        header('Location: ../beneficiario.php?sucesso=beneficiario_removido');
    } else {
        // Beneficiário não encontrado
        header('Location: ../beneficiario.php?erro=beneficiario_nao_encontrado');
    }
} catch (Exception $e) {
    // Em caso de erro, redirecionar com mensagem de erro
    header('Location: ../beneficiario.php?erro=' . urlencode($e->getMessage()));
}
?>