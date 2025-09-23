<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once "../classes/beneficiario.class.php";

// detect "show all" button
$showAll = isset($_GET['all']) && $_GET['all'] == 1;

// current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// items per page
$perPage = 20;

$cod_unidade = $_SESSION["cod_unidade"] ?? 0;
$int_nivel   = $_SESSION["int_nivel"] ?? 2;

// if user clicked "Show All", force cod_unidade = 0
if ($showAll) {
  $cod_unidade = 0;
}

function formatarCPF($cpf)
{
  $cpf = preg_replace('/[^0-9]/', '', $cpf); // Remove caracteres não numéricos 
  if (strlen($cpf) != 11) {
    return $cpf; // Retorna o CPF sem formatação se não tiver 11 dígitos 
  }
  $cpfFormatado = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
  return $cpfFormatado;
}

$beneficiario = new Beneficiario();
$result = $beneficiario->exibirBeneficiario($cod_unidade, $int_nivel, $page, $perPage);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Relatório</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/cesta_custom.css">
  <style>
    #searchInput {
      max-width: 250px;
    }
  </style>
</head>

<body>
  <div class="container mt-5 center-vertical" style="max-width: 1350px;">
    <div class="login-card" style="width: 100%;">
      <div class="row mb-4" style="padding-bottom: 20px;">
        <div class="col-md-5 text-center">
          <h1>Relatório</h1>
        </div>
        <div class="col-md-3 text-center" style="padding-top: 30px;">
          <input type="text" class="form-control" placeholder="Pesquisar..." id="searchInput">
        </div>
        <div class="col-md-4 text-center" style="padding-top: 30px;">
          <a href="?all=1" class="btn btn-primary color">Todas as unidades</a>
          <a href="?page=1" class="btn btn-primary color">Minha Unidade</a>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-hover" id="dataTable">
          <thead class="thead-dark">
            <tr>
              <th>CPF</th>
              <th>NIS</th>
              <th>NOME</th>
              <th>UNIDADE</th>
              <th>SITUAÇÃO</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($result['data'] as $row): ?>
              <tr>
                <td><?= htmlspecialchars(formatarCPF($row['cpf'])) ?></td>
                <td><?= htmlspecialchars($row['nis']) ?></td>
                <td><?= htmlspecialchars($row['nome']) ?></td>
                <td><?= htmlspecialchars($row['vch_bairro']) ?></td>
                <td>
                  <?php
                  switch ($row['situacao']) {
                    case 0:
                      echo '<span class="badge badge-secondary">Inativo</span>';
                      break;
                    case 1:
                      echo '<span class="badge badge-success" style="background-color: green;">Ativo</span>';
                      break;
                    case 2:
                      echo '<span class="badge badge-warning">Pendente</span>';
                      break;
                    default:
                      echo '<span class="badge badge-dark">Desconhecido</span>';
                  }
                  ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php
      $totalPages = ceil($result['total'] / $result['perPage']);
      if ($totalPages > 1):
        $current = $result['page'];
        $range = 2; // how many pages to show around current
      ?>
        <nav class="center-vertical" aria-label="Page navigation" style="padding-top: 0px;">
          <ul class="pagination justify-content-center color">
            <!-- First -->
            <li class="page-item <?= $current == 1 ? 'disabled' : '' ?>">
              <a class="page-link color" href="?page=1<?= $showAll ? '&all=1' : '' ?>">Primeira</a>
            </li>
            <!-- Previous -->
            <li class="page-item <?= $current == 1 ? 'disabled' : '' ?>">
              <a class="page-link color" href="?page=<?= $current - 1 ?><?= $showAll ? '&all=1' : '' ?>">Anterior</a>
            </li>

            <!-- Page numbers -->
            <?php
            $start = max(1, $current - $range);
            $end   = min($totalPages, $current + $range);
            if ($start > 1) {
              echo '<li class="page-item color disabled"><span class="page-link">…</span></li>';
            }
            for ($p = $start; $p <= $end; $p++): ?>
              <li class="page-item <?= $p == $current ? 'active' : '' ?>">
                <a class="page-link color" href="?page=<?= $p ?><?= $showAll ? '&all=1' : '' ?>"><?= $p ?></a>
              </li>
            <?php endfor;
            if ($end < $totalPages) {
              echo '<li class="page-item color disabled"><span class="page-link">…</span></li>';
            }
            ?>

            <!-- Next -->
            <li class="page-item <?= $current == $totalPages ? 'disabled' : '' ?>">
              <a class="page-link color" href="?page=<?= $current + 1 ?><?= $showAll ? '&all=1' : '' ?>">Próxima</a>
            </li>
            <!-- Last -->
            <li class="page-item <?= $current == $totalPages ? 'disabled' : '' ?>">
              <a class="page-link color" href="?page=<?= $totalPages ?><?= $showAll ? '&all=1' : '' ?>">Última</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>

  <script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
      // normalize the search term: lowercase and remove dots/dashes/spaces
      const filter = this.value.toLowerCase().replace(/[.\-\s]/g, '');
      const rows = document.querySelectorAll('#dataTable tbody tr');

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;

        cells.forEach(cell => {
          // normalize cell text the same way
          const text = cell.textContent.toLowerCase().replace(/[.\-\s]/g, '');
          if (text.includes(filter)) {
            match = true;
          }
        });

        row.style.display = match ? '' : 'none';
      });
    });
  </script>
</body>

</html>