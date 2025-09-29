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

// Calculate total and active beneficiaries to determine available spots
$pdo = Database::conexao();

// Get the total number of vagas from saldo_unidade table
$stmt = $pdo->prepare("SELECT saldo FROM beneficiario.saldo_unidade WHERE cod_unidade = :cod_unidade");
$stmt->bindParam(':cod_unidade', $cod_unidade, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$maxBeneficiarios = $result ? $result['saldo'] : 0;

// Count active beneficiaries
$stmt = $pdo->prepare("SELECT COUNT(*) as ativos FROM beneficiario.beneficiario WHERE cod_unidade = :cod_unidade AND situacao = 1");
$stmt->bindParam(':cod_unidade', $cod_unidade, PDO::PARAM_INT);
$stmt->execute();
$ativosBeneficiarios = $stmt->fetch(PDO::FETCH_ASSOC)['ativos'];

// Calculate available spots
$vagasDisponiveis = $maxBeneficiarios - $ativosBeneficiarios;

// Provide data for the modal form (types and neighborhoods)
$tipos = $b->exibirTipo();
$bairros = $b->exibirBairro();

$firstName = explode(" ", $_SESSION['usuarioNome'])[0];
$lastName  = explode(" ", $_SESSION['usuarioNome'])[1] ?? '';

$c = new Categoria();
// Allow administrators (1) and sedes (3) to manage categories
if ($int_nivel == 1 || $int_nivel == 3) {
  $c->criarCategoriasPadrao();
  $categorias = $c->listarCategorias();
}

/* ====== PROCESSA SALVAR CATEGORIA ====== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cod_beneficiario'], $_POST['cod_categoria'])) {
  // Only administrators (1) or sedes (3) may change categories
  if (!($int_nivel == 1 || $int_nivel == 3)) {
    echo "<script>alert('Permissão negada para alterar categoria'); window.location='beneficiario.php';</script>";
    exit;
  }

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

  <!-- Global numeric-only helper for inline onkeypress handlers -->
  <script>
    function somenteNumeros(e) {
      const charCode = e.charCode ? e.charCode : e.keyCode;
      if (charCode !== 8 && charCode !== 9) {
        if (charCode < 48 || charCode > 57) return false;
      }
      return true;
    }
  </script>
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
          <?php if ($int_nivel == 1 || $int_nivel == 3): ?>
            <li class="nav-item"><a href="categoria.php" class="nav-link">Categorias</a></li>
          <?php endif; ?>
          <?php if ($int_nivel == 1): ?>
            <li class="nav-item">
              <form action="processamento/inport_tab_pagamento.php" method="post" enctype="multipart/form-data" style="display:inline;">
                <label class="nav-link mb-0" style="cursor:pointer;">
                  Importar folha de pagamento
                  <input type="file" name="csvfile" accept=".csv" style="display:none;" onchange="this.form.submit()">
                </label>
              </form>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Modal de Adicionar Beneficiário -->
    <div class="modal fade" id="modalBeneficiario" tabindex="-1" role="dialog" aria-labelledby="modalBeneficiarioLabel">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Inserir Beneficiário</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <!-- ALERTA DINÂMICO -->
            <div id="alertBeneficiario" class="alert d-none"></div>

            <form id="formBeneficiario" name="form" method="POST" action="processamento/processar_beneficiario.php" data-toggle="validator" role="form">
              <input type="hidden" id="cod_usuario" name="cod_usuario" value="<?php echo htmlspecialchars($cod_usuario); ?>">
              <input type="hidden" id="cod_unidade" name="cod_unidade" value="<?php echo htmlspecialchars($cod_unidade); ?>">

              <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" class="form-control">
              </div>

              <div class="form-group">
                <label for="nis">NIS</label>
                <input type="text" id="nis" name="nis" class="form-control">
              </div>

              <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" class="form-control" name="nome" required>
              </div>

              <div class="form-group">
                <label for="cod_bairro">Bairro</label>
                <select name="cod_bairro" id="cod_bairro" class="form-control" required>
                  <option value="">Selecione o bairro</option>
                  <?php while ($bairro = $bairros->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $bairro['cod_bairro'] ?>"><?= htmlspecialchars($bairro['vch_bairro']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="endereco">Endereço</label>
                <input type="text" id="endereco" class="form-control" name="endereco" required>
              </div>

              <div class="form-group">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento" class="form-control" name="complemento">
              </div>

              <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" class="form-control" name="telefone">
              </div>

              <div class="form-group">
                <label for="cod_tipo">Tipo de Beneficiário</label>
                <select name="cod_tipo" id="cod_tipo" class="form-control" required>
                  <option value="">Selecione o tipo</option>
                  <?php while ($tipo = $tipos->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $tipo['cod_tipo'] ?>">
                      <?= htmlspecialchars($tipo['vch_tipo']) ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>

              <?php if ($_SESSION['int_level'] == 1 || $_SESSION['int_level'] == 3): ?>
                <div class="form-group">
                  <label for="cod_categoria">Categoria</label>
                  <select name="cod_categoria" id="cod_categoria" class="form-control">
                    <option value="">Selecione a categoria</option>
                    <?php
                    $c = new Categoria();
                    $categorias = $c->listarCategorias();
                    foreach ($categorias as $cat):
                    ?>
                      <option value="<?= $cat['cod_categoria'] ?>">
                        <?= htmlspecialchars($cat['vch_categoria']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Conteúdo -->
    <div id="content">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="mb-0">Lista de Beneficiários</h2>
          <div class="d-flex flex-column align-items-end">
            <a href="#" class="btn btn-success mb-2 <?= $vagasDisponiveis <= 0 ? 'disabled' : '' ?>" data-toggle="modal" data-target="<?= $vagasDisponiveis > 0 ? '#modalBeneficiario' : '' ?>">
              + Adicionar Beneficiário
            </a>
            <div class="badge <?= $vagasDisponiveis > 0 ? 'badge-info' : 'badge-danger' ?> p-2">
              <strong>Vagas disponíveis:</strong> <?= $vagasDisponiveis ?> de <?= $maxBeneficiarios ?>
            </div>
          </div>
        </div>

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
              <?php if ($int_nivel == 1 || $int_nivel == 3): ?><th>Categoria</th><?php endif; ?>
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
                <?php if ($int_nivel == 1 || $int_nivel == 3): ?>
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
                    <?php if ($vagasDisponiveis > 0): ?>
                      <a href="processamento/inserir_beneficiario.php?cod_beneficiario=<?= $row['cod_beneficiario']; ?>&cod_usuario=<?= $cod_usuario; ?>" class="btn btn-sm btn-success mb-1">Inserir na Cesta</a>
                    <?php else: ?>
                      <button class="btn btn-sm btn-secondary mb-1" disabled>Sem vagas disponíveis</button>
                    <?php endif; ?>
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

  <!-- Load local Inputmask shim (provides minimal live CPF formatting) and the modal behavior script -->
  <script src="vendor/inputmask/cpf-local-mask.js"></script>
  <script src="js/beneficiario-modal.js"></script>

  <script>
    $(document).ready(function() {
    $('#tabela').DataTable({
      paging: false, // desabilita paginação do DataTables
      searching: true,
      ordering: true,
      language: {
  // Use local copy to avoid cross-origin XHR and CDN availability issues
  url: "vendor/datatables/plug-ins/1.13.6/i18n/pt-BR.json"
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