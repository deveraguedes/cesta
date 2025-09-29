<?php
// Quick local CLI test runner for processar_beneficiario.php logic — uses same include paths
// Ensure working directory matches the processamento folder so relative includes in the target file resolve correctly
chdir(__DIR__);

// Build a deterministic valid CPF from base digits and insert a temporary record
require_once __DIR__ . '/../classes/conexao.class.php';
$pdo = Database::conexao();

function cpf_from_base($base9) {
	$b = preg_replace('/\D/', '', $base9);
	$b = str_pad(substr($b,0,9), 9, '0', STR_PAD_LEFT);
	$sum = 0;
	for ($i=0;$i<9;$i++) $sum += intval($b[$i]) * (10 - $i);
	$rest = ($sum * 10) % 11; if ($rest == 10) $rest = 0;
	$d1 = $rest;
	$sum = 0;
	for ($i=0;$i<9;$i++) $sum += intval($b[$i]) * (11 - $i);
	$sum += $d1 * 2;
	$rest = ($sum * 10) % 11; if ($rest == 10) $rest = 0;
	$d2 = $rest;
	return $b . $d1 . $d2;
}

$testCpf = cpf_from_base('123456789');

// Insert a temporary beneficiary (cod_unidade = 999 to avoid colliding with real units) — adjust if FK prevents it
try {
	$pdo->beginTransaction();
	$ins = $pdo->prepare("INSERT INTO beneficiario.beneficiario (nis, cpf, nome, cod_bairro, localidade, cod_usuario, dt_cadastro, cod_unidade, cpf_responsavel, vch_responsavel, cod_tipo, cep, endereco, complemento, telefone, situacao, cod_categoria) VALUES (:nis, :cpf, :nome, :cod_bairro, :localidade, :cod_usuario, :dt_cadastro, :cod_unidade, :cpf_responsavel, :vch_responsavel, :cod_tipo, :cep, :endereco, :complemento, :telefone, :situacao, :cod_categoria)");
	$ins->execute([
		':nis' => null,
		':cpf' => $testCpf,
		':nome' => 'TEMP TEST DUP',
		':cod_bairro' => 1,
		':localidade' => null,
		':cod_usuario' => 1,
		':dt_cadastro' => date('Y-m-d H:i:s'),
		':cod_unidade' => 999,
		':cpf_responsavel' => null,
		':vch_responsavel' => null,
		':cod_tipo' => 1,
		':cep' => null,
		':endereco' => 'Rua Temp',
		':complemento' => null,
		':telefone' => null,
		':situacao' => 1,
		':cod_categoria' => null
	]);
	$tempId = $pdo->lastInsertId();
	$pdo->commit();
} catch (Exception $e) {
	try { $pdo->rollBack(); } catch(Exception $e){}
	echo "Failed to insert temp row: " . $e->getMessage() . "\n";
	exit(1);
}

// Now prepare POST to try to add the same CPF but claiming cod_unidade = 1
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [];
$_POST['cpf'] = $testCpf;
$_POST['nis'] = '';
$_POST['nome'] = 'Tester Duplicate';
$_POST['cod_bairro'] = 1;
$_POST['endereco'] = 'Rua Teste';
$_POST['cod_tipo'] = 1;
$_POST['cod_unidade'] = 1; // different than inserted 999
$_POST['cod_usuario'] = 1;

// Run the script and capture output
ob_start();
require __DIR__ . '/processar_beneficiario.php';
$out = ob_get_clean();
// Print the response for the tester
echo "===== SERVER RESPONSE BEGIN =====\n";
echo $out . "\n";
echo "===== SERVER RESPONSE END =====\n";

// Clean up the temporary row we created
try {
	if (!empty($tempId)) {
		$del = $pdo->prepare("DELETE FROM beneficiario.beneficiario WHERE cod_beneficiario = :id");
		$del->execute([':id' => $tempId]);
		echo "Temporary row (id={$tempId}) removed.\n";
	}
} catch (Exception $e) {
	echo "Failed to remove temp row: " . $e->getMessage() . "\n";
}
