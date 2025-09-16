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
    <div class="login-card" style="max-width: 400px; width: 100%; min-height: 500px; padding-top: 20px;">
      <div class="text-center mb-4" style="padding-bottom: 10px;">
        <h1 class="mt-2 title">Acesso ao Sistema</h1>
        <p class="text-muted">Controle de cadastro de beneficiários.</p>
      </div>
      <?php
      if (isset($_GET['response'])) {
        $response = $_GET['response'];
        $messages = [
          1 => ['success', 'Sucesso!', 'Verifique seu e-mail e conclua o cadastro.'],
          2 => ['danger', 'Falha!', 'E-mail já cadastrado.'],
          3 => ['danger', 'Falha!', 'Usuário ou senha incorretos ou seu usuário ainda não foi validado, verifique seu e-mail.'],
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
      <form method="post" action="processamento/logar.php" style="padding-top: 10px;">
        <div class="form-group mb-3">
          <input type="text" class="form-control input-lg" id="vch_login" name="vch_login" placeholder="Usuario" required>
        </div>
        <div class="form-group mb-3">
          <input type="password" name="password" class="form-control input-lg" placeholder="Senha" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block color"> Entrar</button>
        <a href="recuperar_senha.php" class="btn btn-primary btn-lg btn-block color">Esqueci minha senha</a>
      </form>
      <div class="center-vertical" style="padding-top: 3  0px; font-size: 14px; color: gray;">
        <p>&copy; Prefeitura Municipal de Camaçari </p>
      </div>  
    </div>
  </div>  
</body>

</html>