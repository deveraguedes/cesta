<?php
session_start();
require_once '../../classes/login.class.php';

$base = dirname($_SERVER['SCRIPT_NAME']); // returns /cesta/usuario/processamento
$root = explode('/usuario', $base)[0];    // returns /cesta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['vch_login']);
    $senha   = trim($_POST['password']);

    if (!empty($usuario) && !empty($senha)) {
        $login   = new LoginUsuario();
        $success = $login->login($usuario, $senha);

        if ($success) {
            // ✅ Redirect to the correct path inside /cesta/
            header("Location: $root/beneficiario.php");
            exit;
        } else {
            // ❌ Incorrect credentials
            header('Location: ../login.php?response=5');
            exit;
        }
    } else {
        // ❌ Missing kfields
        header('Location: ../login.php?response=3');
        exit;
    }
} else {
    header('Location: ../login.php');
    exit;
}
