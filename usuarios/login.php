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

  <!-- custom css for the page -->
  <style>
    /* default colors */

    :root {
      /* wine colors */
      --wine-base: #800020;
      --wine-dark: #4B0010;
      --wine-bright: #A8324A;
    }

    /* formulario de login */

    .center-vertical {
      min-height: auto;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding-top: 40px;
    }

    .login-card {
      background: linear-gradient(to bottom right, #ffffff, #f8f9fa);
      border-radius: 6px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    body {
      margin: 0;
      height: 100vh;
      background: conic-gradient(var(--wine-dark) 0deg 120deg, var(--wine-base) 120deg 240deg, var(--wine-bright) 240deg 360deg);
    }

    /* buttons and title color code */

    .title {
      color: var(--wine-dark);
      font-weight: bold;
    }

    .color {
      background-color: var(--wine-base);
      border: none;
      transition: background-color 0.3s ease;
    }

    .color:hover {
      background-color: var(--wine-dark);
    }

    /* Alerta modernizados */
    .modern-alert {
      border-radius: 6px;
      border: none;
      font-size: 14px;
      padding: 12px 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .alert-success.modern-alert {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-danger.modern-alert {
      background-color: #f8d7da;
      color: #721c24;
    }

    .alert-warning.modern-alert {
      background-color: #fff3cd;
      color: #856404;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-8px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .modern-alert {
      animation: fadeInDown 0.3s ease-out;
    }

    /* all fonts bigger */
    h1 {
      font-size: 40px;
    }

    h2 {
      font-size: 35px;
    }

    h3 {
      font-size: 30px;
    }

    h4 {
      font-size: 25px;
    }

    h5 {
      font-size: 20px;
    }

    h6 {
      font-size: 16px;
    }

    p,
    .lead,
    .text-muted,
    .form-control {
      font-size: 18px;
    }
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