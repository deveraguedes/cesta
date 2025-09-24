<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once "../classes/beneficiario.class.php";

$showAll = isset($_GET['all']) && $_GET['all'] == 1;
$cod_unidade = $_SESSION["cod_unidade"] ?? 0;
$int_nivel   = $_SESSION["int_nivel"] ?? 2;
if ($showAll) $cod_unidade = 0;

function formatarCPF($cpf) {
  $cpf = preg_replace('/[^0-9]/', '', $cpf);
  return strlen($cpf) == 11 ? substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2) : $cpf;
}

$beneficiario = new Beneficiario();
$result = $beneficiario->exibirBeneficiario($cod_unidade, $int_nivel, 1, 999999);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Imprimir Relatório</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; color: #000; }
    h2 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }
    th { background: #f0f0f0; }
  </style>
</head>
<body onload="window.print()">
  <h2>Relatório de Beneficiários</h2>
  <p style="text-align:right">Total: <?= $result['total'] ?> beneficiários</p>
  <table>
    <thead>
      <tr>
        <th>CPF</th>
        <th>NIS</th>
        <th>Nome</th>
        <th>Unidade</th>
        <th>Situação</th>
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
              case 0: echo 'Inativo'; break;
              case 1: echo 'Ativo'; break;
              case 2: echo 'Pendente'; break;
              default: echo 'Desconhecido';
            }
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
