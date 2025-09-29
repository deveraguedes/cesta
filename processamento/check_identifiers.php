<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../classes/conexao.class.php';

try {
    $db = Database::conexao();
} catch (Exception $e) {
    echo json_encode(['error' => 'db_connection', 'message' => $e->getMessage()]);
    exit;
}

$cpf = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
$nis = isset($_POST['nis']) ? trim($_POST['nis']) : '';

// normalize: remove non-digits
$cpf_clean = preg_replace('/\D/', '', $cpf);
$nis_clean = preg_replace('/\D/', '', $nis);

// If both identifiers are missing, nothing to check
if ($cpf_clean === '' && $nis_clean === '') {
    echo json_encode(['error' => 'missing_fields', 'message' => 'CPF or NIS must be provided']);
    exit;
}

$found = [];

try {
    // --- Check CPF in beneficiario.beneficiario ---
    if ($cpf_clean !== '') {
        $stmt = $db->prepare("SELECT b.cod_beneficiario AS id, b.cpf, b.nis, b.cod_unidade,
        (SELECT u.vch_unidade FROM beneficiario.unidade u WHERE u.cod_unidade = b.cod_unidade LIMIT 1) AS unidade_nome
        FROM beneficiario.beneficiario b
        WHERE regexp_replace(CAST(b.cpf AS text), '[^0-9]', '', 'g') = :cpf
        LIMIT 1");
        $stmt->execute([':cpf' => $cpf_clean]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codu = isset($row['cod_unidade']) ? $row['cod_unidade'] : null;
            $unidadename = isset($row['unidade_nome']) ? $row['unidade_nome'] : null;
            $msg = $unidadename ? ('CPF ja esta na lista na unidade ' . $unidadename) : 'CPF ja esta na lista';
            $found[] = [
                'table' => 'beneficiario.beneficiario',
                'field' => 'cpf',
                'id' => $row['id'],
                'cod_unidade' => $codu,
                'unidade' => $unidadename,
                'message' => $msg
            ];
        }

        // --- Check CPF in beneficiario.folha ---
        $stmt = $db->prepare("SELECT NULL AS id, cpf, nis
        FROM beneficiario.folha
        WHERE regexp_replace(CAST(cpf AS text), '[^0-9]', '', 'g') = :cpf
        LIMIT 1");
        $stmt->execute([':cpf' => $cpf_clean]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $found[] = [
                'table' => 'beneficiario.folha',
                'field' => 'cpf',
                'id' => $row['id'],
                'message' => 'CPF ja esta na folha de pagamento'
            ];
        }
    }

    // --- Check NIS in beneficiario.beneficiario ---
    if ($nis_clean !== '') {
        $stmt = $db->prepare("SELECT b.cod_beneficiario AS id, b.cpf, b.nis, b.cod_unidade,
        (SELECT u.vch_unidade FROM beneficiario.unidade u WHERE u.cod_unidade = b.cod_unidade LIMIT 1) AS unidade_nome
        FROM beneficiario.beneficiario b
        WHERE regexp_replace(CAST(b.nis AS text), '[^0-9]', '', 'g') = :nis
        LIMIT 1");
        $stmt->execute([':nis' => $nis_clean]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $codu = isset($row['cod_unidade']) ? $row['cod_unidade'] : null;
            $unidadename = isset($row['unidade_nome']) ? $row['unidade_nome'] : null;
            $msg = $unidadename ? ('NIS ja esta na lista na unidade ' . $unidadename) : 'NIS ja esta na lista';
            $found[] = [
                'table' => 'beneficiario.beneficiario',
                'field' => 'nis',
                'id' => $row['id'],
                'cod_unidade' => $codu,
                'unidade' => $unidadename,
                'message' => $msg
            ];
        }

        // --- Check NIS in beneficiario.folha ---
        $stmt = $db->prepare("SELECT NULL AS id, cpf, nis
        FROM beneficiario.folha
        WHERE regexp_replace(CAST(nis AS text), '[^0-9]', '', 'g') = :nis
        LIMIT 1");
        $stmt->execute([':nis' => $nis_clean]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $found[] = [
                'table' => 'beneficiario.folha',
                'field' => 'nis',
                'id' => $row['id'],
                'message' => 'NIS ja esta na folha de pagamento'
            ];
        }
    }

    if (!empty($found)) {
        echo json_encode([
            'exists'  => true,
            'details' => $found
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'query_error', 'message' => $e->getMessage()]);
}
exit;
