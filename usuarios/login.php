<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

if (isset($_SESSION['user_id'])) {
  header('Location: ../beneficiario.php'); // Redireciona para a página principal se já estiver logado
  exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/formValidation.css">
  <link rel="stylesheet" href="../css/loading.css">
  <link rel="stylesheet" href="../css/bootstrap-combobox.css">
  <link rel="stylesheet" href="../css/cesta_custom.css">
  <!-- custom css for the page -->
  <style>
  </style>
</head>

<body>
  <!-- Header Nav -->
  <!-- <nav class="navbar navbar-inverse"  >
    <div class="container-fluid">
      <p>aaa</p>
    </div>
  </nav> -->
  <!-- Login form -->

  <div class="container center-vertical" style="width: 450px;">
    <div class="login-card" style="max-width: 400px; width: 100%; min-height: 400px; padding-top: 20px;">
      <div class="text-center mb-4" style="padding-bottom: 10px;">
        <h1 class="mt-2 title">Acesso ao Sistema</h1>
        <p class="text-muted">Controle de cadastro de beneficiários.</p>
      </div>
      <form method="post" action="processamento/logar.php" style="padding-top: 10px;">
        <div class="form-group mb-3">
          <input type="text" class="form-control input-lg" id="vch_login" name="vch_login" placeholder="Usuario" required>
        </div>
        <div class="form-group mb-3" style="padding-bottom: 10px;">
          <input type="password" name="password" class="form-control input-lg" placeholder="Senha" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block color"> Entrar</button>
      </form>
      <?php
      if (isset($_GET['response'])) {
        $response = $_GET['response'];
        $messages = [
          1 => ['danger', 'Falha!', 'Usuário ou senha incorretos, em caso de dúvidas entre em contato com o administrador.'],
          2 => ['danger', 'Falha!', 'Preencha todos os campos para entrar.'],
          3 => ['success', 'Sucesso!', 'Verifique seu e-mail e conclua o cadastro.'],
          4 => ['success', 'Sucesso!', 'Usuário foi ativado!'],
          5 => ['success', 'Sucesso!', 'Verifique seu e-mail e recupere sua senha.']
        ];

        if (array_key_exists($response, $messages)) {
          list($type, $title, $text) = $messages[$response];
          echo "<div class='alert alert-$type alert-dismissible modern-alert fade in' style='margin-top:15px;'>
                <strong>$title</strong> $text
              </div>";
        }
      }
      ?>
      <div class="center-vertical" style="padding-top: 20px; font-size: 14px; color: gray;">
        <p>&copy; Prefeitura Municipal de Camaçari </p>
      </div>
    </div>
  </div>
</body>

</html>