<?php
session_start();
require_once '../../classes/login.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['vch_login']);
    $senha = trim($_POST['password']);

    if (!empty($usuario) && !empty($senha)) {
        $login = new LoginUsuario(); // Use your actual class name
        $success = $login->login($usuario, $senha); // Returns true or false

        if ($success) {
            // Session variables are already set inside the login() method
            header('Location: ../test.php'); // ✅ Redirect on success
            exit;
        } else {
            header('Location: ../login.php?response=5'); // ❌ Incorrect credentials
            exit;
        }
    } else {
        header('Location: ../login.php?response=3'); // ❌ Missing fields
        exit;
    }
} else {
    header('Location: ../login.php');
    exit;
}
