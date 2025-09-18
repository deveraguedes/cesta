<?php
include_once "classes/usuarios.class.php";
include_once "classes/login.class.php";
include_once "classes/beneficiario.class.php";

$l = new LoginUsuario();
if ($l->isloggedin() != "" && $_SESSION['int_nivel'] > 0) {
    $l->refreshSessionTime($_SESSION["sessiontime"]);
} else {
    echo "<script>alert('Para continuar, efetue um novo login.');</script>";
    $l->logout();
}

$cod_unidade = $_SESSION["cod_unidade"];
$int_nivel   = $_SESSION["int_nivel"];
$cod_usuario = $_SESSION["user_session"];

$b = new Beneficiario();
$result = $b->exibirBeneficiario($cod_unidade, $int_nivel);
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
      background-color: #800020; /* fundo vinho */
    }
    /* Sidebar vinho */
    #sidebar {
      min-height: 100vh;
      background-color: #4b0010; /* vinho mais escuro */
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
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
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
  <div id="sidebar" class="p-3">
    <h4 class="text-center">Menu</h4>
    <ul class="nav flex-column">
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
          <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)) { ?>
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
      order: [[2, 'asc']]
    });
  });
</script>
</body>
</html>
