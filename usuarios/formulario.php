<?php
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
</head>

<body>

  <div class="container center-vertical" style="width: 950px; padding-top: 60px; padding-bottom: 10px;">
    <div class="login-card" style="padding: 30px; width: 950px; padding-bottom: 30px;">
      <h2 class="text-center title">Cadastro de Usuário</h2>
      <p class="text-muted text-center">Preencha os dados abaixo para solicitar acesso ao sistema.</p>

      <form method="post" action="processamento/cadastra.php" data-toggle="validator" role="form">

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
          <button type="submit" class="btn btn-primary btn-lg btn-block color" style="margin-top: 30px;">
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
      <div class="text-center" style="width: 100%;">
        &copy; Prefeitura Municipal de Camaçari
      </div>
    </div>
  </div>

  <!--<div>
    <h4 class="title">Usuários pendentes de validação</h4>
  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Nome</th>
        <th>Unidade</th>
        <th>Login</th>
        <th>Senha</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php
      /*$usuario = new Usuarios();
      $consulta = $usuario->dadosTemporarios();
      while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
          <td>{$row['vch_nome']}</td>
          <td>{$row['vch_unidade']}</td>
          <td>{$row['vch_login']}</td>
          <td>{$row['vch_senha']}</td>
          <td>
            <a href='../forms/alterar_usuario.php?cod_usuario={$row['cod_usuario']}' data-toggle='modal' data-target='#myModal'>
              <span class='glyphicon glyphicon-pencil'></span>
            </a>
            <a href='forms/excluir_usuario.php?cod_usuario={$row['cod_usuario']}' data-toggle='modal' data-target='#myModal'>
              <span class='glyphicon glyphicon-trash'></span>
            </a>
          </td>
        </tr>";
      }*/
      ?>
    </tbody>
  </table>
</div>-->
</body>

<script>
  function mascara(i) {
    let v = i.value;

    // Remove tudo que não seja número, ponto ou traço
    v = v.replace(/[^0-9.-]/g, "");

    // Remove múltiplos pontos ou traços consecutivos
    v = v.replace(/(\.{2,})/g, ".");
    v = v.replace(/(-{2,})/g, "-");

    // Remove ponto ou traço fora das posições corretas
    v = v.replace(/^\.|^-/g, ""); // não começa com ponto ou traço
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})\.(\d{3})(\d)/, "$1.$2.$3");
    v = v.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, "$1.$2.$3-$4");

    i.value = v;
  }
</script>


</html>