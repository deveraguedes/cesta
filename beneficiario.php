<?php
session_start();

include_once "classes/usuarios.class.php";
include_once "classes/login.class.php";
include_once "classes/beneficiario.class.php";
include_once "classes/categoria.class.php";
include_once "classes/unidade.class.php";

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
$saldoVagas = $result ? $result['saldo'] : 0;

// Count active beneficiaries
$stmt = $pdo->prepare("SELECT COUNT(*) as ativos FROM beneficiario.beneficiario WHERE cod_unidade = :cod_unidade AND situacao = 1");
$stmt->bindParam(':cod_unidade', $cod_unidade, PDO::PARAM_INT);
$stmt->execute();
$ativosBeneficiarios = $stmt->fetch(PDO::FETCH_ASSOC)['ativos'];

// Calculate total spots (saldo + active beneficiaries)
$totalVagas = $saldoVagas + $ativosBeneficiarios;

// The available spots is the saldo value
$vagasDisponiveis = $saldoVagas;

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
          <?php if ($int_nivel == 1): ?>
            <li class="nav-item"><a href="usuarios/formulario.php" class="nav-link">Criar Usuários</a></li>
          <?php endif; ?>
          <li class="nav-item"><a href="beneficiario.php" class="nav-link">Beneficiários</a></li>
          <li class="nav-item"><a href="relatorios/relat.php" class="nav-link">Relatórios</a></li>
          <?php if ($int_nivel == 1 || $int_nivel == 3): ?>
            <li class="nav-item"><a href="categoria.php" class="nav-link">Categorias</a></li>
          <?php endif; ?>
            <?php if ($int_nivel == 1): ?>
              <li class="nav-item">
                <form id="formImportFolhaBeneficiarios" action="processamento/inport_tab_pagamento.php" method="post" enctype="multipart/form-data" style="display:inline;">
                  <label class="nav-link mb-0" style="cursor:pointer;">
                    Importar folha de pagamento
                    <input type="file" name="csvfile" accept=".csv" style="display:none;" onchange="document.getElementById('importOverlay').classList.remove('d-none'); this.form.submit()">
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

            <form id="formBeneficiario" name="form" method="POST" action="usuarios/processamento/processar_beneficiario.php" data-toggle="validator" role="form">
              <input type="hidden" id="cod_usuario" name="cod_usuario" value="<?php echo htmlspecialchars($cod_usuario); ?>">
              <?php if ($int_nivel == 1): ?>
                <div class="form-group">
                  <label for="cod_unidade">Unidade</label>
                  <select id="cod_unidade" name="cod_unidade" class="form-control" required>
                    <?php
                    $u = new Unidade();
                    $unidades = $u->exibirUnidade();
                    while ($unidade = $unidades->fetch(PDO::FETCH_ASSOC)):
                    ?>
                      <option value="<?= $unidade['cod_unidade'] ?>" <?= ($unidade['cod_unidade'] == $cod_unidade) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($unidade['vch_unidade']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
              <?php else: ?>
                <input type="hidden" id="cod_unidade" name="cod_unidade" value="<?php echo htmlspecialchars($cod_unidade); ?>">
              <?php endif; ?>

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
              <strong>Vagas disponíveis:</strong> <?= $vagasDisponiveis ?> de <?= $totalVagas ?>
            </div>
          </div>
        </div>

        <!-- Mensagens de página (sucesso/erro) -->
        <div id="pageAlert" class="alert d-none" role="alert" style="display:none;"></div>

        <?php
        // Modo debug desativado: não exibir logs temporários de importação
        if (!empty($_SESSION['import_debug'])) {
          unset($_SESSION['import_debug']);
        }
        ?>

        <div class="table-responsive">
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
                    <!-- Botão Alterar -->
                    <a href="#"
                      class="btn btn-sm btn-primary mb-1"
                      data-toggle="modal"
                      data-target="#modalAlterarBeneficiario"
                      data-id="<?= $row['cod_beneficiario']; ?>"
                      data-usuario="<?= $cod_usuario; ?>">
                      Alterar
                    </a>

                    <!-- Botão Inserir/Remover da cesta  -->
                    <button type="button"
                      class="btn btn-sm <?= $row['situacao'] == 1 ? 'btn-danger btn-cesta' : 'btn-success btn-cesta'; ?> mb-1"
                      data-id="<?= $row['cod_beneficiario']; ?>"
                      data-situacao="<?= $row['situacao'] == 1 ? 0 : 1; ?>"
                      data-unidade="<?= $cod_unidade; ?>"
                      data-usuario="<?= $cod_usuario; ?>">
                      <?= $row['situacao'] == 1 ? 'Remover da Cesta' : 'Inserir na Cesta'; ?>
                    </button>

                  </td>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

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

  <!-- Modal Alterar Beneficiário -->
  <div class="modal fade" id="modalAlterarBeneficiario" tabindex="-1" role="dialog" aria-labelledby="modalAlterarBeneficiarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <!-- Conteúdo será carregado via AJAX -->
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

      $('#modalAlterarBeneficiario').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var codBeneficiario = button.data('id');
        var codUsuario = button.data('usuario');

        // Carregar o conteúdo do formulário via AJAX
        $.ajax({
          url: 'usuarios/processamento/alterar_beneficiario.php',
          type: 'GET',
          data: {
            cod_beneficiario: codBeneficiario,
            cod_usuario: codUsuario
          },
          success: function(response) {
            $('#modalAlterarBeneficiario .modal-content').html(response);
          },
          error: function() {
            showPageAlert('danger', 'Erro ao carregar o formulário de alteração.');
          }
        });
      });

      // Botão Remover da Cesta
      $(document).on('click', '.btn-remover', function() {
        var codBeneficiario = $(this).data('id');
        var codUnidade = $(this).data('unidade');

        if (confirm('Tem certeza que deseja remover este beneficiário da cesta?')) {
          $.ajax({
            url: 'usuarios/processamento/remover_beneficiario.php',
            type: 'POST',
            data: {
              cod_beneficiario: codBeneficiario,
              cod_unidade: codUnidade
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                showPageAlert('success', response.message);
                setTimeout(function() {
                  window.location.reload();
                }, 1200);
              } else {
                showPageAlert('danger', 'Erro: ' + response.message);
              }
            },
            error: function() {
              showPageAlert('danger', 'Erro ao processar a solicitação.');
            }
          });
        }
      });

      // Botão Inserir na Cesta
      $(document).on('click', '.btn-inserir', function() {
        var codBeneficiario = $(this).data('id');
        var codUnidade = $(this).data('unidade');

        if (confirm('Tem certeza que deseja inserir este beneficiário na cesta?')) {
          $.ajax({
            url: 'usuarios/processamento/alterar_situacao.php',
            type: 'POST',
            data: {
              cod_beneficiario: codBeneficiario,
              cod_unidade: codUnidade,
              situacao: 1
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                showPageAlert('success', response.message);
                setTimeout(function() {
                  window.location.reload();
                }, 1200);
              } else {
                showPageAlert('danger', 'Erro: ' + response.message);
              }
            },
            error: function() {
              showPageAlert('danger', 'Erro ao processar a solicitação.');
            }
          });
        }
      });
    });
  </script>
  <script>
    // Helper para mensagens de página (sucesso/erro)
    function showPageAlert(type, message) {
      var el = document.getElementById('pageAlert');
      if (!el) return;
      var cls = 'alert alert-' + type + ' alert-dismissible fade show';
      el.className = cls;
      el.style.display = '';
      el.innerHTML = '<span>' + (message || '') + '</span>' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Fechar">' +
        '<span aria-hidden="true">&times;</span></button>';
      el.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }

    function clearPageAlert() {
      var el = document.getElementById('pageAlert');
      if (el) {
        el.className = 'alert d-none';
        el.style.display = 'none';
        el.innerHTML = '';
      }
    }
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Expose vagasDisponiveis to client scripts for gating
      window.vagasDisponiveis = <?= (int)$vagasDisponiveis ?>;
      document.querySelectorAll(".btn-cesta").forEach(btn => {
        btn.addEventListener("click", function() {
          const cod_beneficiario = this.dataset.id;
          const situacao = this.dataset.situacao;
          const cod_unidade = this.dataset.unidade;
          const cod_usuario = this.dataset.usuario;

          // Block insertion when no vagas, provide subtle inline feedback by preventing action
          if (parseInt(situacao, 10) === 1 && window.vagasDisponiveis <= 0) {
            this.classList.add('disabled');
            this.setAttribute('title', 'Sem vagas disponíveis nesta unidade');
            return; // no alerts, no top notifications
          }

          fetch("usuarios/processamento/alterar_situacao.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              body: `cod_beneficiario=${cod_beneficiario}&situacao=${situacao}&cod_unidade=${cod_unidade}&cod_usuario=${cod_usuario}`
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                showPageAlert('success', data.message || 'Operação realizada com sucesso.');
                setTimeout(function() {
                  location.reload();
                }, 1200);
              } else {
                console.error('Erro:', data.message);
                showPageAlert('danger', (data.message || 'Operação não permitida.'));
              }
            })
            .catch(err => console.error("Falha na requisição:", err));
        });
      });
    });
  </script>

  <!-- Fallback configurável de WebSocket de recarregamento (evita erros quando endpoint não responde) -->
  <script>
    (function() {
      var defaultEndpoint = (location.protocol === 'https:' ?
        'wss://' + location.host + '/cesta/ws/ws' :
        'ws://' + location.host + '/cesta/ws/ws');
      var endpoint = window.RELOAD_WS_ENDPOINT || defaultEndpoint;
      try {
        if ('WebSocket' in window) {
          var ws = new WebSocket(endpoint);
          ws.onopen = function() {
            console.log('Reload WebSocket conectado:', endpoint);
          };
          ws.onmessage = function(ev) {
            if (ev.data === 'reload') {
              location.reload();
            }
          };
          ws.onerror = function(err) {
            console.warn('Reload WebSocket erro:', err);
          };
          ws.onclose = function() {
            console.warn('Reload WebSocket fechado');
          };
          window.ReloadSocket = ws;
        }
      } catch (e) {
        console.warn('Falha ao iniciar Reload WebSocket:', e);
      }
    })();
  </script>
  <script>
    $('#modalCesta').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget); // Botão que abriu o modal
      var id = button.data('id');
      var situacao = button.data('situacao');
      var acao = button.data('acao');

      // Atualiza os inputs hidden do form
      var modal = $(this);
      modal.find('#modal_cod_beneficiario').val(id);
      modal.find('#modal_situacao').val(situacao);

      // Texto da confirmação
      modal.find('#textoConfirmacao').text("Deseja realmente " + acao + "?");
    });
  </script>
  <script>
    $(function() {
      function showMessage(text, type) {
        var box = $('#mensagem-acao');
        if (!box.length) {
          $('body').append('<div id="mensagem-acao" class="alert d-none" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>');
          box = $('#mensagem-acao');
        }
        box.removeClass('d-none alert-success alert-danger').addClass(type === 'success' ? 'alert-success' : 'alert-danger');
        box.text(text).show();
        clearTimeout(window._msgTimeout);
        window._msgTimeout = setTimeout(function() {
          box.fadeOut(300, function() {
            box.addClass('d-none').show();
          });
        }, 4000);
      }

      $(document).on('click', '.btn-cesta', function(e) {
        e.preventDefault();
        var btn = $(this);
        var cod = btn.data('id');
        var situacao = parseInt(btn.data('situacao'), 10); // 1 = inserir, 0 = remover
        var unidade = btn.data('unidade');
        var usuario = btn.data('usuario');

        var texto = (situacao === 1) ? 'Deseja inserir este beneficiário na cesta?' : 'Deseja remover este beneficiário da cesta?';
        if (!confirm(texto)) return;

        $.ajax({
          url: 'usuarios/processamento/alterar_situacao.php',
          method: 'POST',
          dataType: 'json',
          data: {
            cod_beneficiario: cod,
            situacao: situacao,
            cod_unidade: unidade,
            cod_usuario: usuario
          }
        }).done(function(resp) {
          if (resp.success) {
            showMessage(resp.message, 'success');

            // Atualiza botão
            if (situacao === 1) {
              btn.removeClass('btn-success').addClass('btn-danger').text('Remover da Cesta').data('situacao', 0);
            } else {
              btn.removeClass('btn-danger').addClass('btn-success').text('Inserir na Cesta').data('situacao', 1);
            }

            // Atualiza célula situação
            btn.closest('tr').find('.situacao-cell').text(situacao === 1 ? 'Incluído na Cesta' : 'Fora da Cesta');

          } else {
            showMessage(resp.message || 'Erro ao processar.', 'danger');
          }
        }).fail(function(xhr, status) {
          showMessage('Erro na requisição: ' + status, 'danger');
        });
      });
    });
  </script>


</body>
<div class="modal fade" id="modalCesta" tabindex="-1" role="dialog" aria-labelledby="modalCestaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" action="usuarios/processamento/alterar_situacao.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCestaLabel">Confirmação</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <p id="textoConfirmacao">Tem certeza?</p>
          <input type="hidden" name="cod_beneficiario" id="modal_cod_beneficiario">
          <input type="hidden" name="situacao" id="modal_situacao">
          <input type="hidden" name="cod_usuario" value="<?= $cod_usuario; ?>">
          <input type="hidden" name="cod_unidade" value="<?= $cod_unidade; ?>">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Confirmar</button>
        </div>
      </div>
    </form>
  </div>
</div>


</html>
  <!-- Overlay de progresso de importação (global) -->
  <div id="importOverlay" class="d-none" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:1050;display:flex;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:20px 28px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.2);text-align:center;">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <div style="margin-top:12px;font-weight:500;">Importando arquivo, por favor aguarde…</div>
      <small class="text-muted" style="display:block;margin-top:4px;">Não feche esta janela até concluir.</small>
    </div>
  </div>