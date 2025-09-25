<?php
session_start();

include_once "classes/usuarios.class.php";
include_once "classes/login.class.php";
include_once "classes/beneficiario.class.php";
include_once "classes/categoria.class.php";

$l = new LoginUsuario();
if ($l->isLoggedIn() && $_SESSION['int_level'] > 0) {
  $l->refreshSessionTime();
} else {
  echo "<script>alert('Para continuar, efetue um novo login.');</script>";
  $l->logout();
}

$cod_unidade = $_SESSION["cod_unidade"];
$int_nivel   = $_SESSION["int_level"];
$cod_usuario = $_SESSION["user_id"];

$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 50;

$b = new Beneficiario();
$beneficiarios = $b->exibirBeneficiario($cod_unidade, $int_nivel, $page, $perPage);

$firstName = explode(" ", $_SESSION['usuarioNome'])[0];
$lastName  = explode(" ", $_SESSION['usuarioNome'])[1] ?? '';

$c = new Categoria();
if ($int_nivel == 1) {
  $c->criarCategoriasPadrao();
  $categorias = $c->listarCategorias();
}

/* ====== PROCESSA SALVAR CATEGORIA ====== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cod_beneficiario'], $_POST['cod_categoria'])) {
  $cod_beneficiario = (int) $_POST['cod_beneficiario'];
  $cod_categoria = (int) $_POST['cod_categoria'];

  $pdo = Database::conexao();
  $sql = "UPDATE beneficiario.beneficiario 
             SET cod_categoria = :cod_categoria 
           WHERE cod_beneficiario = :cod_beneficiario";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':cod_categoria', $cod_categoria, PDO::PARAM_INT);
  $stmt->bindParam(':cod_beneficiario', $cod_beneficiario, PDO::PARAM_INT);
  $stmt->execute();

  echo "<script>alert('Categoria salva com sucesso!'); window.location='beneficiario.php';</script>";
  exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Lista de Beneficiários</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/cesta_custom.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #800020;
    }

    #sidebar {
      min-height: 100vh;
      background-color: #4b0010;
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

    #content {
      padding: 20px;
      background: #fff;
      width: 100%;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      margin: 20px;
    }

    table {
      border-radius: 12px;
      overflow: hidden;
    }

    table th {
      background-color: #f1f1f1;
    }

    .btn {
      border-radius: 10px;
    }
  </style>

  <script src="js/beneficiario.js"></script>
</head>

<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <div id="sidebar" class="p-3">
      <div class="container text-center" style="padding-bottom: 10px; border-bottom: 1px solid #3d3d3dff; margin-bottom: 20px;">
        <h3>Bem-vindo <br> <?= htmlspecialchars($firstName); ?></h3>
        <a href="processamento/logout.php" class="nav-link">Sair</a>
      </div>
      <div class="container">
        <ul class="nav flex-column">
          <li class="nav-item"><a href="usuarios/formulario.php" class="nav-link">Criar Usuários</a></li>
          <li class="nav-item"><a href="beneficiario.php" class="nav-link">Beneficiários</a></li>
          <li class="nav-item"><a href="relatorios/relat.php" class="nav-link">Relatórios</a></li>
          <?php if ($int_nivel == 1): ?>
            <li class="nav-item"><a href="categoria.php" class="nav-link">Categorias</a></li>
          <?php endif; ?>
          <?php if ($int_nivel == 1): ?>
            <li class="nav-item"><a href="processamento/inport_tab_pagamento.php" class="nav-link">Importar tabela de Categorias</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Modal de Adicionar Beneficiário -->
<div class="modal fade" id="modalBeneficiario" tabindex="-1" role="dialog" aria-labelledby="modalBeneficiarioLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <!-- Conteúdo será carregado via AJAX -->
    </div>
  </div>
</div>

    <!-- Conteúdo -->
    <div id="content">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="mb-0">Lista de Beneficiários</h2>
           <a href="#" class="btn btn-success" data-toggle="modal" data-target="#modalBeneficiario">
              + Adicionar Beneficiário
</a>        </div>

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
              <?php if ($int_nivel == 1): ?><th>Categoria</th><?php endif; ?>
              <th>Situação</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($beneficiarios['data'] as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row["nis"]); ?></td>
                <td><?= htmlspecialchars($row["cpf"]); ?></td>
                <td><?= htmlspecialchars($row["nome"]); ?></td>
                <td><?= htmlspecialchars($row["vch_bairro"]); ?></td>
                <td><?= htmlspecialchars($row["localidade"]); ?></td>
                <td><?= htmlspecialchars($row["endereco"]); ?></td>
                <td><?= htmlspecialchars($row["vch_tipo"]); ?></td>
                <?php if ($int_nivel == 1): ?>
                  <td>
                    <?= htmlspecialchars($row["categoria"] ?? "Sem categoria"); ?><br>
                    <a href="#" class="btn btn-sm btn-warning mt-1"
                      data-toggle="modal"
                      data-target="#modalCategoria"
                      data-id="<?= $row['cod_beneficiario']; ?>">
                      Definir
                    </a>
                  </td>
                <?php endif; ?>
                <td><?= $row["situacao"] == 1 ? "Incluído na Cesta" : "Fora da Cesta"; ?></td>
                <td>
                  <a href="forms/alterar_beneficiario.php?cod_beneficiario=<?= $row['cod_beneficiario']; ?>&cod_usuario=<?= $cod_usuario; ?>" class="btn btn-sm btn-primary mb-1">Alterar</a>
                  <?php if ($row["situacao"] == 1): ?>
                    <a href="processamento/remover_beneficiario.php?cod_beneficiario=<?= $row['cod_beneficiario']; ?>&cod_usuario=<?= $cod_usuario; ?>" class="btn btn-sm btn-danger mb-1">Remover da Cesta</a>
                  <?php else: ?>
                    <a href="processamento/inserir_beneficiario.php?cod_beneficiario=<?= $row['cod_beneficiario']; ?>&cod_usuario=<?= $cod_usuario; ?>" class="btn btn-sm btn-success mb-1">Inserir na Cesta</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Paginação estilo avançado -->
        <?php
        $totalPages = ceil($beneficiarios['total'] / $perPage);
        if ($totalPages > 1):
          $current = $page;
          $range   = 2;
        ?>
          <nav class="center-vertical" aria-label="Page navigation" style="padding-top: 0px;">
            <ul class="pagination justify-content-center color">
              <li class="page-item <?= $current == 1 ? 'disabled' : '' ?>">
                <a class="page-link color" href="?page=1">Primeira</a>
              </li>
              <li class="page-item <?= $current == 1 ? 'disabled' : '' ?>">
                <a class="page-link color" href="?page=<?= $current - 1 ?>">Anterior</a>
              </li>
              <?php
              $start = max(1, $current - $range);
              $end   = min($totalPages, $current + $range);

              if ($start > 1) {
                echo '<li class="page-item color disabled"><span class="page-link">…</span></li>';
              }

              for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?= $p == $current ? 'active' : '' ?>">
                  <a class="page-link color" href="?page=<?= $p ?>"><?= $p ?></a>
                </li>
              <?php endfor;

              if ($end < $totalPages) {
                echo '<li class="page-item color disabled"><span class="page-link">…</span></li>';
              }
              ?>

              <li class="page-item <?= $current == $totalPages ? 'disabled' : '' ?>">
                <a class="page-link color" href="?page=<?= $current + 1 ?>">Próxima</a>
              </li>
              <li class="page-item <?= $current == $totalPages ? 'disabled' : '' ?>">
                <a class="page-link color" href="?page=<?= $totalPages ?>">Última</a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Modal Categoria -->
  <div class="modal fade" id="modalCategoria" tabindex="-1" role="dialog" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post">
          <div class="modal-header">
            <h5 class="modal-title" id="modalCategoriaLabel">Atribuir Categoria</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="cod_beneficiario" id="cod_beneficiario">
            <div class="form-group">
              <label for="cod_categoria">Categoria:</label>
              <select name="cod_categoria" id="cod_categoria" class="form-control" required>
                <option value="">-- Selecione --</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= $cat['cod_categoria']; ?>"><?= htmlspecialchars($cat['vch_categoria']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Salvar</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="js/beneficiario.js"></script>
  <script>
    $(document).ready(function() {
      console.log('Inicializando DataTables e configurações...');
      
      $('#tabela').DataTable({
        paging: false, // desabilita paginação do DataTables
        searching: true,
        ordering: true,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
        }
      });

      $('#modalCategoria').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var codBeneficiario = button.data('id');
        $('#cod_beneficiario').val(codBeneficiario);
      });
    });
  </script>
</body>

</html>