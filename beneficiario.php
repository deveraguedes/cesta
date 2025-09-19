<?php
include_once "classes/usuarios.class.php";
include_once "classes/login.class.php";
include_once "classes/beneficiario.class.php";

$l = new LoginUsuario();
if ($l->isLoggedIn() && $_SESSION['int_level'] > 0) {
  $l->refreshSessionTime();
} else {
  echo "<script>alert('Para continuar, efetue um novo login.');</script>";
  $l->logout();
}

// Recupera dados da sessão
$cod_unidade = $_SESSION["cod_unidade"];
$int_nivel   = $_SESSION["int_level"];
$cod_usuario = $_SESSION["user_id"];

$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 50;

$b = new Beneficiario();
$beneficiarios = $b->exibirBeneficiario($cod_unidade, $int_nivel, $page, $perPage);

$firstName = explode(" ", $_SESSION['usuarioNome'])[0];
$lastName  = explode(" ", $_SESSION['usuarioNome'])[1] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Cadastro de Beneficiários</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #800020;
      /* fundo vinho */
    }

    /* Sidebar vinho */
    #sidebar {
      min-height: 100vh;
      background-color: #4b0010;
      /* vinho mais escuro */
      color: #fff;
      border-radius: 0 15px 15px 0;
    }

    #sidebar .nav-link {
      color: #fff;
      transition: 0.3s;
    }

    #sidebar .nav-link:hover {
      background-color: #a8324a;
      border-radius: 8px;
    }

    /* Conteúdo principal */
    #content {
      padding: 20px;
      background: #fff;
      width: 100%;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      margin: 20px;
    }

    /* Tabela */
    table.dataTable {
      border-radius: 12px;
      overflow: hidden;
    }

    table.dataTable th {
      background-color: #f1f1f1;
    }

    .btn {
      border-radius: 10px;
    }
  </style>
</head>

<body>

  <div class="d-flex">
    <!-- Sidebar -->
    <div id="sidebar" class="p-3" style="width: 250px;">
      <div class="container text-center" style="padding-bottom: 10  px; border-bottom: 1px solid #3d3d3dff; margin-bottom: 20px;">
        <h3>Bem-vindo <br> <?= htmlspecialchars($firstName); ?></h3>
        <a href="processamento/logout.php" class="nav-link">Sair</a>
      </div>
      <div class="container">
        <ul class="nav flex-column">
          <li class="nav-item">
            <a href="usuarios/formulario.php" class="nav-link">Criar Usuários</a>
          </li>
          <li class="nav-item">
            <a href="beneficiario.php" class="nav-link">Beneficiários</a>
          </li>
          <li class="nav-item">
            <a href="relatorio.php" class="nav-link">Relatórios</a>
          </li>
          <li class="nav-item">
            <a href="usuarios/logout.php" class="nav-link">Sair</a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Conteúdo -->
    <div id="content">
      <div class="container-fluid">
        <h2 class="mb-4">Cadastro de Beneficiários</h2>

        <table id="tabela" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>NIS</th>
              <th>CPF</th>
              <th>Nome</th>
              <th>Bairro</th>
              <th>Localidade</th>
              <th>Endereço</th>
              <th>Tipo de Beneficiário</th>
              <th>Situação</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($beneficiarios['data'] as $row) { ?>
              <tr>
                <td><?= $row["nis"] ?></td>
                <td><?= $row["cpf"] ?></td>
                <td><?= $row["nome"] ?></td>
                <td><?= $row["vch_bairro"] ?></td>
                <td><?= $row["localidade"] ?></td>
                <td><?= $row["endereco"] ?></td>
                <td><?= $row["vch_tipo"] ?></td>
                <td>
                  <?= $row["situacao"] == 1 ? "Incluído na Cesta" : "Fora da Cesta" ?>
                </td>
                <td>
                  <!-- Botão Alterar -->
                  <a href="forms/alterar_beneficiario.php?cod_beneficiario=<?= $row['cod_beneficiario']; ?>&cod_usuario=<?= $cod_usuario; ?>"
                    class="btn btn-sm btn-primary mb-1">
                    Alterar
                  </a>

                  <!-- Botões Inserir / Remover -->
                  <?php if ($row["situacao"] == 1) { ?>
                    <a href="processamento/remover_beneficiario.php?cod_beneficiario=<?= $row['cod_beneficiario']; ?>&cod_usuario=<?= $cod_usuario; ?>"
                      class="btn btn-sm btn-danger mb-1">
                      Remover da Cesta
                    </a>
                  <?php } else { ?>
                    <a href="processamento/inserir_beneficiario.php?cod_beneficiario=<?= $row['cod_beneficiario']; ?>&cod_usuario=<?= $cod_usuario; ?>"
                      class="btn btn-sm btn-success mb-1">
                      Inserir na Cesta
                    </a>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#tabela').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
        },
        pageLength: 25,
        order: [
          [2, 'asc']
        ]
      });
    });
  </script>
</body>

</html>