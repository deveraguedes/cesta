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
// fetch all rows by setting a very large perPage
$result = $beneficiario->exibirBeneficiario($cod_unidade, $int_nivel, 1, 999999);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=relatorio.csv');

$output = fopen('php://output', 'w');
// header row
fputcsv($output, ['CPF', 'NIS', 'Nome', 'Unidade', 'Situação']);

foreach ($result['data'] as $row) {
  switch ($row['situacao']) {
    case 0: $situacao = 'Inativo'; break;
    case 1: $situacao = 'Ativo'; break;
    case 2: $situacao = 'Pendente'; break;
    default: $situacao = 'Desconhecido';
  }
  fputcsv($output, [
    formatarCPF($row['cpf']),
    $row['nis'],
    $row['nome'],
    $row['vch_bairro'],
    $situacao
  ]);
}
fclose($output);
exit;
