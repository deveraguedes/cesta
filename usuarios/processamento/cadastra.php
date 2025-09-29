<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


session_start();
require_once '../../classes/usuarios.class.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../formulario.php');
  exit;
}

try {
  // Enforce unidade for int_nivel 2 or 3 users: they can only create users in their own unidade
  if (isset($_SESSION['int_level']) && ((int)$_SESSION['int_level'] === 2 || (int)$_SESSION['int_level'] === 3)) {
    $_POST['cod_unidade'] = (int)$_SESSION['cod_unidade'];
  }
  $usuario = new Usuario();
  $usuario->setData($_POST);
  $usuario->cadastrar();

  header('Location: ../formulario.php?response=1');
  exit;
} catch (InvalidArgumentException $e) {
  // identifica o tipo de erro
  $msg = $e->getMessage();

  switch ($msg) {
    case 'campo_obrigatorio':
      $code = 3;
      break;
    case 'senha_mismatch':
      $code = 7;
      break;
    case 'login_duplicado':
      $code = 4;
      break;
    case 'nome_duplicado':
      $code = 5;
      break;
    case 'unidade_duplicada':
      // inclui o código da unidade no redirect
      $code    = 6;
      $unidade = $usuario->getCodUnidade();
      header("Location: ../formulario.php?response={$code}&unidade={$unidade}");
      exit;
    default:
      $code = 3;
  }
  
  $unidade = $usuario->getCodUnidade();
  header("Location: ../formulario.php?response={$code}&unidade={$unidade}");
  exit;
} catch (PDOException $e) {
  // unique constraint inesperada (ex: índice único não mapeado)
  if ($e->getCode() === '23000') {
    header('Location: ../formulario.php?response=2');
    exit;
  }
  die('Erro ao cadastrar usuário: ' . $e->getMessage());
}
