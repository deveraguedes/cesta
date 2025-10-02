<?php
session_start();
include_once "classes/conexao.class.php";
include_once "classes/login.class.php";
include_once "classes/categoria.class.php";

$l = new LoginUsuario();

//  Verifica login e nível de acesso (somente nível 1 pode acessar)
if (!$l->isloggedin() || ($_SESSION['int_level'] ?? 0) != 1) {
    echo "<script>alert('Acesso negado.'); window.location='beneficiario.php';</script>";
    exit;
}

$categoria = new Categoria();


//  Adicionar categoria
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nome"]) && ($_SESSION['int_level'] ?? 0) == 1) {
    $nome = trim($_POST["nome"]);
    if (!empty($nome)) {
        try {
            $categoria->adicionarCategoria($nome);
            header("Location: categoria.php");
            exit;
        } catch (Exception $e) {
            echo "<script>alert('".$e->getMessage()."'); window.location='categoria.php';</script>";
            exit;
        }
    }
}

//  Excluir categoria
if (isset($_GET["excluir"]) && ($_SESSION['int_level'] ?? 0) == 1) {
    $id = (int)$_GET["excluir"];
    if ($id > 0) {
        try {
            $categoria->excluirCategoria($id);
            header("Location: categoria.php");
            exit;
        } catch (Exception $e) {
            echo "<script>alert('".$e->getMessage()."'); window.location='categoria.php';</script>";
            exit;
        }
    }
}

//  Listar categorias
$categorias = $categoria->listarCategorias();

//  Pega o primeiro nome do usuário logado
$firstName = explode(" ", $_SESSION['usuarioNome'])[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Categorias</title>
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
    .btn {
      border-radius: 10px;
    }
  </style>
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
          <li class="nav-item"><a href="categoria.php" class="nav-link active">Categorias</a></li>
        </ul>
      </div>
    </div>

    <div id="content">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="mb-0">Categorias</h2>
        </div>

        <!-- Formulário para adicionar -->
        <?php if (($_SESSION['int_level'] ?? 0) == 1): ?>
          <form method="post" class="form-inline mb-3">
            <input type="text" name="nome" class="form-control mr-2" placeholder="Nova categoria" required>
            <button type="submit" class="btn btn-success">Adicionar</button>
          </form>
        <?php endif; ?>

        <!-- Lista de categorias -->
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Categoria</th>
              <?php if (($_SESSION['int_level'] ?? 0) == 1): ?><th>Ações</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($categorias)): ?>
              <?php foreach ($categorias as $cat): ?>
                <tr>
                  <td><?= $cat["cod_categoria"] ?></td>
                  <td><?= htmlspecialchars($cat["vch_categoria"]) ?></td>
                  <?php if (($_SESSION['int_level'] ?? 0) == 1): ?>
                    <td>
                      <a href="?excluir=<?= $cat['cod_categoria'] ?>" 
                         class="btn btn-sm btn-danger" 
                         onclick="return confirm('Excluir categoria?')">Excluir</a>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?= (($_SESSION['int_level'] ?? 0) == 1 ? 3 : 2); ?>" class="text-center">
                  Nenhuma categoria cadastrada.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
