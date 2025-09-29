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
    $stmt = $db->prepare("SELECT NULL AS id, cpf, nis
    FROM beneficiario.beneficiario
    WHERE regexp_replace(CAST(cpf AS text), '[^0-9]', '', 'g') = :cpf
    LIMIT 1");
        $stmt->execute([':cpf' => $cpf_clean]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $found[] = [
                'table' => 'beneficiario.beneficiario',
                'field' => 'cpf',
                'id' => $row['id'],
                'message' => 'CPF ja esta na lista'
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
    $stmt = $db->prepare("SELECT NULL AS id, cpf, nis
    FROM beneficiario.beneficiario
    WHERE regexp_replace(CAST(nis AS text), '[^0-9]', '', 'g') = :nis
    LIMIT 1");
        $stmt->execute([':nis' => $nis_clean]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $found[] = [
                'table' => 'beneficiario.beneficiario',
                'field' => 'nis',
                'id' => $row['id'],
                'message' => 'NIS ja esta na lista'
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
