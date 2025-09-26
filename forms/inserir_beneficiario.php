<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

session_start();
include_once('../classes/beneficiario.class.php');
include_once('../classes/usuarios.class.php');
include_once('../classes/categoria.class.php');

if (!isset($_SESSION['user_id'])) {
    exit('Sessão expirada. Por favor, faça login novamente.');
}

$cod_usuario = $_SESSION['user_id'];
$cod_unidade = $_SESSION['cod_unidade'];

// Instanciar classes necessárias
$b = new Beneficiario();
$tipos = $b->exibirTipo();
$bairros = $b->exibirBairro();
?>

