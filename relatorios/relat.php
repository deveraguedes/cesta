<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once "../classes/beneficiario.class.php";

$showAll = isset($_GET['all']) && $_GET['all'] == 1;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$perPage = 20;

$cod_unidade = $_SESSION["cod_unidade"] ?? 0;
$int_nivel   = $_SESSION["int_nivel"] ?? 2;
if ($showAll) $cod_unidade = 0;

function formatarCPF($cpf)
{
  $cpf = preg_replace('/[^0-9]/', '', $cpf);
  return strlen($cpf) == 11 ? substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2) : $cpf;
}

$beneficiario = new Beneficiario();
$result = $beneficiario->exibirBeneficiario($cod_unidade, $int_nivel, $page, $perPage);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Relatório</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/cesta_custom.css">
  <style>
    #searchInput {
      max-width: 250px;
    }

    thead th {
      position: sticky;
      top: 0;
      background-color: #343a40;
      /* dark header */
      color: #fff;
      z-index: 2;
    }

    #dataTable td {
      font-size: 14px;
    }

    #dataTable tbody tr:hover {
      background-color: #f1f1f1;
    }

    .btn-group a,
    .btn-group button {
      margin-right: 5px;
    }
  </style>
</head>

<body>
  <div class="container mt-5 center-vertical" style="max-width: 1350px;">
    <div class="login-card" style="width: 100%;">
      <div class="row mb-4" style="padding-bottom: 10px; padding-top: 10px; width: 100%;">
        <div class="col-md-5 text-center">
          <h1>Relatório</h1>
        </div>
        <div class="col-md-7 text-center" style="padding-top: 10px; left: 0;">
          <div class="btn-group">
            <div class="input-group" style="margin-bottom: 10px; padding-right: 10px; width: 100%; left: 0;">
              <input type="text" style="width: 100%; left: 0;" class="form-control" placeholder="Pesquisar..." id="searchInput">
            </div>
            <a href="?all=1" class="btn btn-primary color">Todas as unidades</a>
            <a href="?page=1" class="btn btn-primary color">Minha Unidade</a>
            <button onclick="exportFullCSV()" class="btn btn-success color">Exportar CSV</button>
            <!-- <button onclick="printFullTable()" class="btn btn-outline-dark">🖨️ Imprimir Lista</button> -->
          </div>
        </div>
      </div>

      <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
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
        $range = 2;
      ?>
        <nav class="center-vertical" aria-label="Page navigation" style="padding-top: 0px;">
          <ul class="pagination justify-content-center color">
            <li class="page-item <?= $current == 1 ? 'disabled' : '' ?>">
              <a class="page-link color" href="?page=1<?= $showAll ? '&all=1' : '' ?>">Primeira</a>
            </li>
            <li class="page-item <?= $current == 1 ? 'disabled' : '' ?>">
              <a class="page-link color" href="?page=<?= $current - 1 ?><?= $showAll ? '&all=1' : '' ?>">Anterior</a>
            </li>
            <?php
            $start = max(1, $current - $range);
            $end   = min($totalPages, $current + $range);
            if ($start > 1) echo '<li class="page-item color disabled"><span class="page-link">…</span></li>';
            for ($p = $start; $p <= $end; $p++): ?>
              <li class="page-item <?= $p == $current ? 'active' : '' ?>">
                <a class="page-link color" href="?page=<?= $p ?><?= $showAll ? '&all=1' : '' ?>"><?= $p ?></a>
              </li>
            <?php endfor;
            if ($end < $totalPages) echo '<li class="page-item color disabled"><span class="page-link">…</span></li>';
            ?>
            <li class="page-item <?= $current == $totalPages ? 'disabled' : '' ?>">
              <a class="page-link color" href="?page=<?= $current + 1 ?><?= $showAll ? '&all=1' : '' ?>">Próxima</a>
            </li>
            <li class="page-item <?= $current == $totalPages ? 'disabled' : '' ?>">
              <a class="page-link color" href="?page=<?= $totalPages ?><?= $showAll ? '&all=1' : '' ?>">Última</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>

  <script>
    // Simple search across all columns (ignores punctuation)
    document.getElementById('searchInput').addEventListener('input', function() {
      const filter = this.value.toLowerCase().replace(/[.\-\s]/g, '');
      const rows = document.querySelectorAll('#dataTable tbody tr');
      rows.forEach(row => {
        const text = row.innerText.toLowerCase().replace(/[.\-\s]/g, '');
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // Export full dataset (all pages) to CSV
    function exportFullCSV() {
      window.location.href = "relat_export.php<?= $showAll ? '?all=1' : '' ?>";
    }

    // Print full dataset (all pages)
    function printFullTable() {
      window.open("relat_print.php<?= $showAll ? '?all=1' : '' ?>", "_blank");
    }
  </script>
</body>

</html>