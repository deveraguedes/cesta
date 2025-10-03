<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once "../classes/beneficiario.class.php";
include_once "../classes/categoria.class.php";
include_once "../baseurl.php";



if (!isset($_SESSION['user_id'])) {
  header('Location: ../usuarios/login.php'); // Redireciona para a página de login se não estiver logado
  exit;
}

$firstName = explode(" ", $_SESSION['usuarioNome'])[0];
$lastName  = explode(" ", $_SESSION['usuarioNome'])[1] ?? '';

$showAll  = isset($_GET['all']) && $_GET['all'] == 1;
$allPages = isset($_GET['allPages']) && $_GET['allPages'] == 1;
$page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$perPage  = 20;

// Quando solicitada busca em todas páginas, carrega tudo em uma só página
if ($allPages) {
  $page = 1;
  $perPage = 5000; // limite alto para trazer todos os registros
}

$cod_unidade = $_SESSION["cod_unidade"] ?? 0;
$int_nivel   = $_SESSION["int_level"] ?? 2;

// Quando "Todas as unidades" estiver selecionado, não restringe por unidade
if ($showAll) {
  $cod_unidade = 0;
}

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
  <title>Relatórios</title>
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

    /* Force modal to cover entire viewport */
    .modal {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100% !important;
      height: 100% !important;
      display: none;
      z-index: 1050;
    }

    .modal.show {
      display: block;
    }

    /* Backdrop, if missing */
    .modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1040;
    }

    /* Sidebar vinho – fica fixo dos pés à cabeça */
    #sidebar {
      position: fixed;
      top: 0;
      /* começa no topo */
      bottom: 0;
      /* vai até o rodapé */
      left: 0;
      /* colado na esquerda */
      width: 220px;
      /* sua largura fixa */
      background-color: #4b0010;
      color: #fff;
      overflow-y: auto;
      /* scroll interno caso o menu seja longo */
      border-radius: 0 15px 15px 0;
      padding: 1rem;
    }

    #sidebar .nav-link {
      color: #fff;
      transition: background .3s;
    }

    #sidebar .nav-link:hover {
      background-color: #a8324a;
      border-radius: 8px;
    }

    /* Ajuste o conteúdo principal para não ficar sob o sidebar */
    #content {
      margin-left: 250px;
      /* empurra o conteúdo pra direita */
    }
  </style>
</head>

<body>
  <div class="row" style="margin-right: 0px; margin-left: 0px;">
    <!-- Sidebar -->
    <div class="col-md-2" style="padding: 0px; width: 200px;">
      <div id="sidebar" class="p-3">
        <div class="container text-center" style="width: 200px; padding-bottom: 10  px; border-bottom: 1px solid #3d3d3dff; margin-bottom: 20px;">
          <h3>Bem-vindo <br> <?= htmlspecialchars($firstName); ?></h3>
          <?php require_once __DIR__ . '/../baseurl.php'; ?>
          <ul class="nav flex-column">
            <li class="nav-item">
              <a href="<?= abs_url('processamento/logout.php') ?>" class="nav-link">Sair</a>
            </li>
          </ul>
        </div>
        <div class="container" style="width: 200px;">
          <ul class="nav flex-column">
            <?php if ($int_nivel == 1): ?>
              <li class="nav-item"><a href="<?= abs_url('usuarios/formulario.php') ?>" class="nav-link">Criar Usuários</a></li>
            <?php endif; ?>
            <li class="nav-item">
              <a href="<?= abs_url('beneficiario.php') ?>" class="nav-link">Beneficiários</a>
            </li>
            <li class="nav-item">
              <a href="<?= abs_url('relatorios/relat.php') ?>" class="nav-link">Relatórios</a>
            </li>
            <?php if ($int_nivel == 1 || $int_nivel == 3): ?>
              <li class="nav-item"><a href="<?= abs_url('categoria.php') ?>" class="nav-link">Categorias</a></li>
            <?php endif; ?>
            <?php if ($int_nivel == 1): ?>
              <li class="nav-item">
                <form action="<?= abs_url('processamento/inport_tab_pagamento.php') ?>" method="post" enctype="multipart/form-data" style="display:inline;">
                  <label class="nav-link mb-0" style="cursor:pointer; font-weight: normal;padding: 10px; padding-left: 15px;">
                    Importar folha de pagamento
                    <input type="file" name="csvfile" accept=".csv" style="display:none;" onchange="this.form.submit()">
                  </label>
                </form>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-md-10" style="padding-left: 20px; max-width: 1100px;">
      <!-- Main Content -->
      <div class="container mt-5 center-vertical" style="max-width: 1100px;">
        <div class="login-card" style="width: 100%;">
          <div class="row mb-4" style="padding-bottom: 10px; padding-top: 10px; width: 100%;">
            <div class="col-md-5 text-center">
              <h1>Relatórios</h1>
            </div>
            <div class="col-md-7 text-center" style="padding-top: 10px; left: 0;">
              <div class="btn-group">
                <div class="input-group" style="margin-bottom: 10px; padding-right: 10px; width: 100%; left: 0;">
                  <input type="text" style="width: 100%; left: 0;" class="form-control" placeholder="Pesquisar..." id="searchInput" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                
                <a href="<?= abs_url('relatorios/relat.php?all=1') ?>" class="btn btn-primary color">Todas as unidades</a>
                
                <a href="<?= abs_url('relatorios/relat.php?page=1') ?>" class="btn btn-primary color">Minha Unidade</a>
                <button onclick="exportFullCSV()" class="btn btn-success color">Exportar CSV</button>
                <!-- <button onclick="printFullTable()" class="btn btn-outline-dark">🖨️ Imprimir Lista</button> -->
              </div>
            </div>
          </div>

          <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
            <table class="table table-bordered table-hover" id="dataTable">
              <thead class="thead-dark">
                <tr>
                  <th>NIS</th>
                  <th>CPF</th>
                  <th>NOME</th>
                  <th>UNIDADE</th>
                  <th>SITUAÇÃO</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($result['data'] as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['nis']) ?></td>
                    <td><?= htmlspecialchars(formatarCPF($row['cpf'])) ?></td>
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
    </div>
  </div>

  <script>
    const isAllPages = <?= $allPages ? 'true' : 'false' ?>;
    const showAllUnits = <?= $showAll ? 'true' : 'false' ?>;
    const baseUrl = "<?= abs_url('relatorios/relat.php') ?>";

    function applyFilter(str) {
      const filter = (str || '').toLowerCase().replace(/[.\-\s]/g, '');
      const rows = document.querySelectorAll('#dataTable tbody tr');
      rows.forEach(row => {
        const text = row.innerText.toLowerCase().replace(/[.\-\s]/g, '');
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    }

    // Debounced search: when not on allPages yet, navigate ONCE to load all data
    (function() {
      const input = document.getElementById('searchInput');
      if (!input) return;
      let timer = null;
      let navigated = false;
      const handler = function() {
        const q = this.value || '';
        if (!isAllPages) {
          if (navigated) return; // prevent loops
          navigated = true;
          clearTimeout(timer);
          timer = setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            params.set('page', '1');
            if (showAllUnits) params.set('all', '1'); else params.delete('all');
            params.set('allPages', '1');
            params.set('q', q);
            window.location.href = baseUrl + '?' + params.toString();
          }, 250);
          return;
        }
        applyFilter(q);
      };
      input.addEventListener('input', handler);
    })();

    // Apply initial filter if q exists
    (function() {
      const params = new URLSearchParams(window.location.search);
      const q = params.get('q') || '';
      if (q) applyFilter(q);
    })();

    // Export full dataset (all pages) to CSV
    function exportFullCSV() {
      window.location.href = "<?= abs_url('relatorios/relat_export.php') ?><?= $showAll ? '?all=1' : '' ?>";
    }

    // Print full dataset (all pages)
    function printFullTable() {
      window.open("<?= abs_url('relatorios/relat_print.php') ?><?= $showAll ? '?all=1' : '' ?>", "_blank");
    }
  </script>
</body>

</html>