<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

if (isset($_SESSION['user_id'])) {
  header('Location: beneficiario.php'); // Redireciona para a página principal se já estiver logado
  exit;
} else {
  header('Location: usuarios/login.php'); // Redireciona para a página de login se não estiver logado
  exit;
}