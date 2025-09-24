<?php
session_start();
include_once "classes/conexao.class.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = Database::conexao();

        $sql = "INSERT INTO beneficiario.beneficiario 
                   (nis, cpf, nome, cod_bairro, endereco, telefone, cod_tipo, cod_unidade, situacao)
                VALUES 
                   (:nis, :cpf, :nome, :cod_bairro, :endereco, :telefone, :cod_tipo, :cod_unidade, :situacao)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nis', $_POST['nis']);
        $stmt->bindValue(':cpf', $_POST['cpf']);
        $stmt->bindValue(':nome', $_POST['nome']);
        $stmt->bindValue(':cod_bairro', $_POST['cod_bairro']);
        $stmt->bindValue(':endereco', $_POST['endereco']);
        $stmt->bindValue(':telefone', $_POST['telefone']);
        $stmt->bindValue(':cod_tipo', $_POST['cod_tipo']);
        $stmt->bindValue(':cod_unidade', $_SESSION['cod_unidade'], PDO::PARAM_INT);
        $stmt->bindValue(':situacao', 0, PDO::PARAM_INT); // por padrão, fora da cesta

        if ($stmt->execute()) {
            echo "<script>alert('Beneficiário cadastrado com sucesso!'); window.location='../beneficiario.php';</script>";
            exit;
        } else {
            echo "<script>alert('Erro ao cadastrar beneficiário.');</script>";
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Novo Beneficiário</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="mb-4">Cadastrar Novo Beneficiário</h2>
    <form method="POST">
      <div class="form-group">
        <label>NIS</label>
        <input type="text" name="nis" class="form-control" required>
      </div>
      <div class="form-group">
        <label>CPF</label>
        <input type="text" name="cpf" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Bairro (código)</label>
        <input type="number" name="cod_bairro" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Endereço</label>
        <input type="text" name="endereco" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Telefone</label>
        <input type="text" name="telefone" class="form-control">
      </div>
      <div class="form-group">
        <label>Tipo de Beneficiário (código)</label>
        <input type="number" name="cod_tipo" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success">Salvar</button>
      <a href="../beneficiario.php" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</body>
</html>
