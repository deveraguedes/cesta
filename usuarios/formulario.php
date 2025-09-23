<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// redireciona se não estiver logado
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// renova sessão
require_once '../classes/login.class.php';
$loginCtrl = new LoginUsuario();
$loginCtrl->refreshSessionTime();

ini_set("display_errors", 1);
include_once('../classes/unidade.class.php');
include_once('../classes/usuarios.class.php');


$u = new Unidade();
$stmt = $u->exibirUnidade();
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unidadeMap = [];
foreach ($unidades as $row) {
  $unidadeMap[(int)$row['cod_unidade']] = $row['vch_unidade'];
}

$currentLevel = $_SESSION['int_level'] ?? 0;

$firstName = explode(" ", $_SESSION['usuarioNome'])[0];
$lastName  = explode(" ", $_SESSION['usuarioNome'])[1] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/formValidation.css">
  <link rel="stylesheet" href="../css/loading.css">
  <link rel="stylesheet" href="../css/bootstrap-combobox.css">
  <link rel="stylesheet" href="../css/cesta_custom.css">
  <style>
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
    <div class="col-md-6" style="padding: 0px; width: 200px;">
      <div id="sidebar" class="p-3">
        <div class="container text-center" style="width: 200px; padding-bottom: 10  px; border-bottom: 1px solid #3d3d3dff; margin-bottom: 20px;">
          <h3>Bem-vindo <br> <?= htmlspecialchars($firstName); ?></h3>
          <ul class="nav flex-column">
            <li class="nav-item">
              <a href="../processamento/logout.php" class="nav-link">Sair</a>
            </li>
          </ul>
        </div>
        <div class="container" style="width: 200px;">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a href="/cesta/usuarios/formulario.php" class="nav-link">Criar Usuários</a>
            </li>
            <li class="nav-item">
              <a href="/cesta/beneficiario.php" class="nav-link">Beneficiários</a>
            </li>
            <li class="nav-item">
              <a href="/cesta/relatorio.php" class="nav-link">Relatórios</a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-md-6" style="padding: 0px; width: calc(100% - 200px);">
      <div class="container center-vertical" style="width: 1100px; padding-top: 60px; padding-bottom: 10px;">
        <div class="login-card" style="padding: 30px; width: 1100px; padding-bottom: 30px;">
          <h2 class="text-center title">Cadastro de Usuário</h2>
          <p class="text-muted text-center">Preencha os dados abaixo para solicitar acesso ao sistema.</p>

          <form method="post" action="processamento/cadastra.php" data-toggle="validator" role="form" id="UsuarioForm">
            <?php if ($currentLevel === 1): ?>
              <div class="form-group">
                <label for="vch_email">Nivel de acesso</label>
                <select name="int_nivel" id="int_nivel" class="form-control" required>
                  <option value="">Selecione</option>
                  <option value="1">Administrador</option>
                  <option value="2">Usuário Padrão</option>
                </select>
              </div>
            <?php endif; ?>

            <div class="form-group">
              <label for="vch_nome">Nome completo</label>
              <input type="text" class="form-control" name="vch_nome" id="vch_nome" required>
            </div>

            <div class="row" style="margin-right: 0px; margin-left: 0px; padding-bottom: 20px;">
              <div class="col-md-6" style="padding-left: 0px;  padding-top: 0px;">
                <div class="form-group">
                  <label for="usuario">Usuário</label>
                  <input type="text" name="usuario" id="usuario" class="form-control" maxlength="12" required>
                </div>
                <div class="form-group">
                  <label for="cod_unidade">Unidade</label>
                  <select name="cod_unidade" id="cod_unidade" class="combobox form-control" required>
                    <option value="">Selecione ou digite</option>
                    <?php foreach ($unidades as $row_unidade): ?>
                      <option value="<?php echo $row_unidade['cod_unidade']; ?>">
                        <?php echo $row_unidade['vch_unidade']; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="col-md-6" style="padding-right: 0px;">
                <div class="form-group">
                  <label for="password">Senha</label>
                  <input type="password" name="password" id="password" class="form-control" maxlength="12" required>
                </div>

                <div class="form-group">
                  <label for="password2">Confirme a senha</label>
                  <input type="password" name="password2" id="password2" class="form-control" maxlength="12" required>
                </div>

                <!--<div class="form-group">
            <label for="cpf">CPF</label>
            <input type="text" class="form-control" name="cpf" id="cpf" maxlength="14" oninput="mascara(this)" required>  
          </div>-->
              </div>
              <button type="submit" class="btn btn-primary btn-lg btn-block color" style="margin-top: 30px;" id="btnCadastrar">
                Cadastrar
              </button>
            </div>
            <input type="hidden" name="data_cadastro" value="<?php echo date("Y-m-d"); ?>">

          </form>

          <?php if (isset($_GET['response'])) {
            $response = (int) $_GET['response'];
            $unidade  = isset($_GET['unidade']) ? intval($_GET['unidade']) : null;
            // **USE the correct map variable name (capital M)**
            $unidadename = $unidadeMap[$unidade] ?? 'a unidade selecionada';

            // Mensagens fixas
            $alerts = [
              1 => ['success', 'Sucesso!',       'Verifique seu e-mail e conclua o cadastro.'],
              2 => ['danger',  'Falha!',         'CPF/login já cadastrado.'],
              3 => ['danger',  'Falha!',         'Há campo(s) obrigatório(s) em branco.'],
              4 => ['danger',  'Falha!',         "Usuario já cadastrado em {$unidadename}."],
              5 => ['danger',  'Falha!',         "Nome já cadastrado em {$unidadename}."],
              7 => ['danger',  'Falha!',         'As senhas não coincidem.'],
            ];

            // Exibe mensagem para códigos fixos
            if (array_key_exists($response, $alerts)) {
              list($type, $title, $msg) = $alerts[$response];
              echo "<div class='alert alert-{$type}' style='margin-top:15px;'>
              <strong>{$title}</strong> {$msg}
            </div>";
            }
            // Trata código 6: unidade duplicada, inclui o NOME da unidade
            else if ($response === 6 && $unidade !== null) {
              echo "<div class='alert alert-danger' style='margin-top:15px;'>
              <strong>Falha!</strong> A unidade {$unidadename} já possui um usuário cadastrado.
            </div>";
            }
          } ?>
        </div>
      </div>

      <div class="container center-vertical" style="width: 1100px; padding-top: 20px; padding-bottom: 10px;">
        <div class="login-card" style="padding: 30px; width: 1100px; padding-bottom: 30px;">
          <div class="row" style="margin-right: 0px; margin-left: 0px; padding-bottom: 20px;">
            <div class="col-md-6 text-right" style="padding-bottom: 10px;">
              <h4 class="title">Usuários no sistema</h4>
            </div>
            <div class="col-md-6">
              <select name="cod_unidade" id="filtroUnidade" class="combobox form-control" required>
                <option value="0" <?= empty($_SESSION['cod_unidade']) ? ' selected' : '' ?>>Todos</option>
                <?php foreach ($unidades as $row_unidade): ?>
                  <option value="<?= $row_unidade['cod_unidade']; ?>"
                    <?= ((int)$_SESSION['cod_unidade'] === (int)$row_unidade['cod_unidade']) ? ' selected' : '' ?>>
                    <?= $row_unidade['vch_unidade']; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div id="mensagemErro" class="alert alert-danger" style="display:none;"></div>
            <table id="tabelaUsuarios" class="table table-bordered tableColor">
              <thead class="tableColorHeader">
                <tr>
                  <th style="width: 10%;">Cadastro</th>
                  <th style="width: 30%;">Nome</th>
                  <th style="width: 15%;">Login</th>
                  <th style="width: 35%;">Unidade</th>
                  <th style="width: 10%; text-align: center;">Ações</th>
                </tr>
              </thead>
              <tbody>
                <!-- JavaScript preencherá aqui -->
              </tbody>
            </table>
            <div class="col-md-12 text-center">
              <nav aria-label="Navegação de página">
                <ul class="pagination justify-content-center color" id="pagerUsuarios"></ul>
              </nav>
            </div>
            <div class="text-center" style="width: 100%;">
              &copy; Prefeitura Municipal de Camaçari
            </div>
          </div>
        </div>
      </div>

      <!-- expose current user level -->
      <script>
        const currentUserLevel = <?= json_encode((int)($_SESSION['int_level'] ?? 0), JSON_UNESCAPED_UNICODE) ?>;
      </script>

      <!-- editar-usuario Modal -->
      <div class="modal fade" id="editUsuarioModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <form id="editUsuarioForm">
              <div class="modal-header">
                <h5 class="modal-title">Editar Usuário</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="editCodUsuario" name="cod_usuario">

                <div class="form-group">
                  <label for="editNome">Nome</label>
                  <input type="text" id="editNome" name="vch_nome" class="form-control" required>
                </div>

                <div class="form-group">
                  <label for="editLogin">Login</label>
                  <input type="text" id="editLogin" name="vch_login" class="form-control" required>
                </div>

                <div class="form-group">
                  <label for="editUnidade">Unidade</label>
                  <select id="editUnidade" name="cod_unidade" class="form-control" required>
                    <option value="">Selecione</option>
                    <?php foreach ($unidades as $u): ?>
                      <option value="<?= $u['cod_unidade'] ?>">
                        <?= htmlspecialchars($u['vch_unidade']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="editSenha">Nova Senha</label>
                  <input type="password" id="editSenha" name="vch_senha"
                    class="form-control" placeholder="Deixe vazio para manter a atual">
                </div>

                <div class="form-group">
                  <label for="editSenhaConfirm">Confirmar Senha</label>
                  <input type="password" id="editSenhaConfirm"
                    class="form-control" placeholder="Repita a nova senha">
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                  Cancelar
                </button>
                <button type="submit" class="btn btn-primary color">Salvar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- modal creating admin warning -->
      <div class="modal fade" id="creatingAdminModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Atenção</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <p>Você está prestes a criar um usuário com nível de administrador. Tem certeza que deseja continuar?</p>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                  Cancelar
                </button>
                <button type="button" class="btn btn-primary color" id="confirmCreateAdminBtn">Confirmar</button>
              </div>

              <!-- 2) Inclua jQuery 3.3.1 local (compatible with Bootstrap 3) -->
              <script src="../vendor/jquery/jquery.min.js"></script>

              <!-- 3) Inclua Bootstrap 3.3.7 JS local, após jQuery -->
              <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

              <!-- Debug: Check jQuery and Bootstrap versions and modal plugin -->
              <script>
                console.log('jQuery version:', window.jQuery && jQuery.fn && jQuery.fn.jquery);
                if (typeof $().modal === 'function') {
                  console.log('Bootstrap modal plugin loaded.');
                } else {
                  console.error('Bootstrap modal plugin NOT loaded!');
                  alert('Bootstrap modal plugin NOT loaded! Check JS includes and order.');
                }
              </script>

              <!-- 4) Seu inline que abre o modal -->
              <script>
                $(function() {
                  // exemplo de disparo manual
                  // openEditModal(usuario) deve chamar .modal('show')
                  window.openEditModal = function(u) {
                    // preenche campos...
                    $('#editUsuarioModal').modal('show');
                  };

                  // ou, se você preferir usar data-attributes:
                  // <button data-toggle="modal" data-target="#editUsuarioModal">Editar</button>
                });
              </script>


              <script>
                document.addEventListener('DOMContentLoaded', () => {
                  // 🔧 Variáveis globais e dados do servidor
                  const unidadeMap = <?= json_encode($unidadeMap, JSON_UNESCAPED_UNICODE) ?>;
                  const currentUserId = <?= isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null' ?>;
                  const currentUserLevel = <?= isset($_SESSION['int_level']) ? json_encode($_SESSION['int_level']) : 'null' ?>;

                  // 🔍 Elementos da interface
                  const filtro = document.getElementById('filtroUnidade');
                  const tbodyEl = document.querySelector('#tabelaUsuarios tbody');
                  const pagerEl = document.getElementById('pagerUsuarios');
                  const form = document.getElementById('UsuarioForm');
                  const nivelSelect = document.getElementById('int_nivel');
                  const warningModal = $('#creatingAdminModal');
                  const confirmBtn = document.getElementById('confirmCreateAdminBtn');

                  // 📄 Configuração de paginação
                  const perPage = 6;
                  let curPage = 1;

                  if (!filtro || !tbodyEl || !pagerEl) return;

                  filtro.addEventListener('change', () => {
                    curPage = 1;
                    carregar(curPage);
                  });

                  carregar(curPage);

                  async function carregar(page) {
                    try {
                      const un = filtro.value || 0;
                      const resp = await fetch(
                        `processamento/listar_usuarios.php?unidade=${un}&page=${page}&per_page=${perPage}`
                      );
                      let text = await resp.text();
                      text = text.replace(/^[\s\xEF\xBB\xBF]+/, '');
                      const json = JSON.parse(text);

                      tbodyEl.innerHTML = '';
                      if (!json.success) {
                        tbodyEl.innerHTML = `<tr><td colspan="5">Erro: ${json.error||'—'}</td></tr>`;
                        return;
                      }
                      if (json.data.length === 0) {
                        tbodyEl.innerHTML = '<tr><td colspan="5">Nenhum usuário encontrado.</td></tr>';
                      } else {
                        json.data.forEach(u => {
                          const tr = document.createElement('tr');
                          tr.innerHTML = `
              <td>${formatDate(u.data_cadastro)}</td>
              <td>${u.vch_nome}</td>
              <td>${u.vch_login}</td>
              <td>${unidadeMap[u.cod_unidade]||u.cod_unidade}</td>
              <td></td>
            `;
                          const cell = tr.querySelector('td:last-child');

                          // Editar (só admin)
                          if (currentUserLevel === 1) {
                            const btnE = document.createElement('button');
                            btnE.className = 'btn btn-sm btn-outline-secondary mr-2';
                            btnE.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.854a.5.5 0 0 1 .708 0l1.292 1.292a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 3 10.707V13h2.293L13.5 4.793l-2.293-2.293z"/></svg>';
                            btnE.onclick = () => openEditModal(u);
                            cell.appendChild(btnE);
                          }

                          // Deletar (todos)
                          if (parseInt(u.int_nivel) !== 1 && parseInt(u.cod_usuario) !== parseInt(currentUserId)) {
                            const btnD = document.createElement('button');
                            btnD.className = 'btn btn-sm btn-outline-danger';
                            btnD.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 5h4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5H6a.5.5 0 0 1-.5-.5v-7z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4H2.5a1 1 0 0 1 0-2H5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1h2.5a1 1 0 0 1 1 1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3H4h8h1.5a.5.5 0 0 0 0-1H12V1a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v1H2.5a.5.5 0 0 0 0 1z"/></svg>';
                            btnD.onclick = () => deletarUsuario(u);
                            cell.appendChild(btnD);
                          }

                          tbodyEl.appendChild(tr);
                        });
                      }

                      renderPager(json.total, json.page, json.per_page);
                    } catch (err) {
                      console.error(err);
                      tbodyEl.innerHTML = '<tr><td colspan="5">Erro ao carregar tabela.</td></tr>';
                    }
                  }

                  function openEditModal(u) {
                    // grab the modal once
                    const $modal = $('#editUsuarioModal');
                    $modal.modal('show');
                    if (!$modal.length) {
                      console.error('Modal element not found');
                      return;
                    }

                    // fill fields via jQuery
                    $modal.find('#editCodUsuario').val(u.cod_usuario);
                    $modal.find('#editNome').val(u.vch_nome);
                    $modal.find('#editLogin').val(u.vch_login);
                    $modal.find('#editUnidade').val(u.cod_unidade);
                    // clear password inputs
                    $modal.find('#editSenha, #editSenhaConfirm').val('');

                    // show it
                    $modal.modal('show');
                  }

                  const editForm = document.getElementById('editUsuarioForm');
                  if (editForm) {
                    editForm.addEventListener('submit', async e => {
                      e.preventDefault();
                      const senha = document.getElementById('editSenha').value;
                      const confirma = document.getElementById('editSenhaConfirm').value;
                      if (senha && senha !== confirma) {
                        return alert('As senhas não coincidem.');
                      }
                      const data = new FormData(editForm);
                      if (!senha) data.delete('vch_senha');
                      // Debug: log all form data
                      for (let [k, v] of data.entries()) {
                        console.log('FormData:', k, v);
                      }
                      try {
                        const res = await fetch('processamento/editar_usuario.php', {
                          method: 'POST',
                          body: data
                        });
                        let text = await res.text();
                        let json;
                        try {
                          json = JSON.parse(text);
                          if (json.success) {
                            $('#editUsuarioModal').modal('hide');
                            carregar(curPage);
                            return;
                          } else {
                            alert('Erro ao salvar: ' + (json.error || 'desconhecido'));
                            return;
                          }
                        } catch (e) {
                          // Always close modal and refresh table on any non-JSON response
                          $('#editUsuarioModal').modal('hide');
                          carregar(curPage);
                          return;
                        }
                      } catch (err) {
                        console.error('Erro na requisição:', err);
                        alert('Erro na requisição: ' + err.message);
                      }
                    });
                  }

                  async function deletarUsuario(usuario) {
                    if (parseInt(usuario.cod_usuario) === parseInt(currentUserId)) {
                      showError('Você não pode excluir seu próprio usuário.');
                      return;
                    }

                    if (parseInt(usuario.int_nivel) === 1) {
                      showError('Você não pode excluir outro administrador.');
                      return;
                    }

                    if (!confirm('Deseja realmente excluir este usuário?')) return;

                    try {
                      const res = await fetch('processamento/deletar_usuario.php', {
                        method: 'POST',
                        headers: {
                          'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                          cod: usuario.cod_usuario
                        })
                      });

                      const text = await res.text();
                      let json;
                      try {
                        json = JSON.parse(text);
                        if (json.success) {
                          document.getElementById('mensagemErro').style.display = 'none';
                          carregar(curPage);
                        } else {
                          showError(json.error || 'Erro desconhecido ao excluir.');
                        }
                      } catch (e) {
                        carregar(curPage);
                      }
                    } catch (err) {
                      showError('Erro na requisição: ' + err.message);
                    }

                    function showError(msg) {
                      const msgEl = document.getElementById('mensagemErro');
                      msgEl.innerHTML = msg;
                      msgEl.style.display = 'block';
                    }
                  }

                  // Intercept form submission
                  form.addEventListener('submit', function(e) {
                    const nivel = nivelSelect ? nivelSelect.value : '';
                    if (nivel === '1') {
                      e.preventDefault(); // stop form for now
                      warningModal.modal('show'); // show modal
                    }
                    // if not admin, form submits normally
                  });

                  // Confirm modal action
                  confirmBtn.addEventListener('click', function() {
                    warningModal.modal('hide');
                    form.submit(); // submit after confirmation
                  });

                  function renderPager(totalItems, page, perPage) {
                    const totalPages = Math.ceil(totalItems / perPage);
                    pagerEl.innerHTML = '';
                    if (totalPages < 2) return;
                    // Previous
                    const prevLi = document.createElement('li');
                    prevLi.className = `page-item ${page===1?'disabled':''}`;
                    prevLi.innerHTML = `<a class="page-link color" href="#">Anterior</a>`;
                    prevLi.onclick = e => {
                      e.preventDefault();
                      if (page > 1) carregar(page - 1);
                    };
                    pagerEl.appendChild(prevLi);
                    // Pages
                    for (let p = 1; p <= totalPages; p++) {
                      const li = document.createElement('li');
                      li.className = `page-item ${p===page?'active':''}`;
                      li.innerHTML = `<a class="page-link color" href="#">${p}</a>`;
                      li.onclick = e => {
                        e.preventDefault();
                        if (p !== page) carregar(p);
                      };
                      pagerEl.appendChild(li);
                    }
                    // Next
                    const nextLi = document.createElement('li');
                    nextLi.className = `page-item ${page===totalPages?'disabled':''}`;
                    nextLi.innerHTML = `<a class="page-link color" href="#">Próxima</a>`;
                    nextLi.onclick = e => {
                      e.preventDefault();
                      if (page < totalPages) carregar(page + 1);
                    };
                    pagerEl.appendChild(nextLi);
                  }

                  function formatDate(str) {
                    str = str.trim();
                    if (/^\d{4}-\d{2}-\d{2}$/.test(str)) {
                      const d = new Date(str);
                      return isNaN(d) ? str : d.toLocaleDateString('pt-BR');
                    }
                    const parts = str.split(/[-\/]/);
                    if (parts.length === 3) {
                      let dd, mm, yy;
                      if (parts[0].length === 4) {
                        yy = parts[0];
                        mm = parts[1];
                        dd = parts[2];
                      } else {
                        dd = parts[0];
                        mm = parts[1];
                        yy = parts[2];
                      }
                      const d = new Date(`${yy}-${mm}-${dd}`);
                      return isNaN(d) ? str : d.toLocaleDateString('pt-BR');
                    }
                    const d = new Date(str);
                    return isNaN(d) ? str : d.toLocaleDateString('pt-BR');
                  }
                });
              </script>


</body>

</html>