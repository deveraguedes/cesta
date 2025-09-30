<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../classes/beneficiario.class.php';
require_once '../classes/categoria.class.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// --- Server-side validators ---
function validate_cpf($cpf) {
    $cpf = preg_replace('/\D/', '', (string)$cpf);
    if (strlen($cpf) !== 11) return false;
    if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;

    // first digit
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += intval($cpf[$i]) * (10 - $i);
    }
    $rest = ($sum * 10) % 11;
    if ($rest === 10 || $rest === 11) $rest = 0;
    if ($rest !== intval($cpf[9])) return false;

    // second digit
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += intval($cpf[$i]) * (11 - $i);
    }
    $rest = ($sum * 10) % 11;
    if ($rest === 10 || $rest === 11) $rest = 0;
    if ($rest !== intval($cpf[10])) return false;

    return true;
}

function validate_pis($pis) {
    $pis = preg_replace('/\D/', '', (string)$pis);
    if (strlen($pis) !== 11) return false;
    $weights = [3,2,9,8,7,6,5,4,3,2];
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += intval($pis[$i]) * $weights[$i];
    }
    $rest = $sum % 11;
    $dig = 11 - $rest;
    if ($dig === 10 || $dig === 11) $dig = 0;
    return $dig === intval($pis[10]);
}

try {
    // Basic server-side validation
    $cpf_raw = $_POST['cpf'] ?? '';
    $nis_raw = $_POST['nis'] ?? '';

    // Validate CPF
    if (!validate_cpf($cpf_raw)) {
        echo json_encode(['success' => false, 'message' => 'CPF inválido']);
        exit;
    }

    // If NIS looks like CPF, reject to avoid swapped fields
    $nis_digits = preg_replace('/\D/', '', (string)$nis_raw);
    if (validate_cpf($nis_digits)) {
        echo json_encode(['success' => false, 'message' => 'NIS parece ser um CPF — verifique se os campos não estão trocados']);
        exit;
    }

    // If NIS is present but does not validate as PIS, warn/reject depending on policy — here we reject invalid format
    if ($nis_digits !== '' && !validate_pis($nis_digits)) {
        echo json_encode(['success' => false, 'message' => 'NIS com formato inválido']);
        exit;
    }

    // --- Duplicate checks (beneficiario list and folha) ---
    // Use digit-only comparisons on DB side via regexp_replace to be robust against formatting
    $pdo = Database::conexao();
    $submitted_unidade = isset($_POST['cod_unidade']) && $_POST['cod_unidade'] !== '' ? (int) $_POST['cod_unidade'] : ($_SESSION['cod_unidade'] ?? null);

    // Helper to fetch unidade name
    $getUnidadeName = function($cod_unidade) use ($pdo) {
        if (empty($cod_unidade)) return null;
        $s = $pdo->prepare("SELECT vch_unidade FROM beneficiario.unidade WHERE cod_unidade = :cod_unidade LIMIT 1");
        $s->bindValue(':cod_unidade', $cod_unidade, PDO::PARAM_INT);
        $s->execute();
        $r = $s->fetch(PDO::FETCH_ASSOC);
        return $r ? $r['vch_unidade'] : null;
    };

    // Check CPF duplicates
    $cpf_digits = preg_replace('/\D/', '', (string)$cpf_raw);
    if ($cpf_digits !== '') {
        // Check in folha_p_2023 first
    $sf = $pdo->prepare("SELECT 1 FROM beneficiario.folha_p_2023 WHERE regexp_replace(CAST(cpf AS text), '\\D', '', 'g') = :cpf LIMIT 1");
        $sf->bindValue(':cpf', $cpf_digits);
        $sf->execute();
        if ($sf->fetch()) {
            echo json_encode(['success' => false, 'message' => 'CPF já está na folha de pagamento']);
            exit;
        }

        // Check in beneficiario table
    $sb = $pdo->prepare("SELECT cod_unidade FROM beneficiario.beneficiario WHERE regexp_replace(CAST(cpf AS text), '\\D', '', 'g') = :cpf LIMIT 1");
        $sb->bindValue(':cpf', $cpf_digits);
        $sb->execute();
        if ($row = $sb->fetch(PDO::FETCH_ASSOC)) {
            $found_unidade = isset($row['cod_unidade']) ? (int)$row['cod_unidade'] : null;
            $unitName = $getUnidadeName($found_unidade);
            // Prefer showing the unidade name or code when available so the user knows where the
            // existing record lives. This is helpful even when it belongs to the same unidade.
            if ($unitName || $found_unidade) {
                $displayUnit = $unitName ?: $found_unidade;
                $msg = 'CPF já existe na lista na unidade ' . $displayUnit;
            } else {
                $msg = 'CPF já existe na lista';
            }
            echo json_encode(['success' => false, 'message' => $msg, 'unidade_cod' => $found_unidade, 'unidade' => $unitName]);
            exit;
        }
    }

    // Check NIS duplicates
    $nis_digits = preg_replace('/\D/', '', (string)$nis_raw);
    if ($nis_digits !== '') {
        // Check in folha_p_2023 first
    $sf2 = $pdo->prepare("SELECT 1 FROM beneficiario.folha_p_2023 WHERE regexp_replace(CAST(nis AS text), '\\D', '', 'g') = :nis LIMIT 1");
        $sf2->bindValue(':nis', $nis_digits);
        $sf2->execute();
        if ($sf2->fetch()) {
            echo json_encode(['success' => false, 'message' => 'NIS já está na folha de pagamento']);
            exit;
        }

        // Check in beneficiario table
    $sb2 = $pdo->prepare("SELECT cod_unidade FROM beneficiario.beneficiario WHERE regexp_replace(CAST(COALESCE(nis::text, '') AS text), '\\D', '', 'g') = :nis LIMIT 1");
        $sb2->bindValue(':nis', $nis_digits);
        $sb2->execute();
        if ($row = $sb2->fetch(PDO::FETCH_ASSOC)) {
            $found_unidade = isset($row['cod_unidade']) ? (int)$row['cod_unidade'] : null;
            $unitName = $getUnidadeName($found_unidade);
            if ($unitName || $found_unidade) {
                $displayUnit = $unitName ?: $found_unidade;
                $msg = 'NIS já existe na lista na unidade ' . $displayUnit;
            } else {
                $msg = 'NIS já existe na lista';
            }
            echo json_encode(['success' => false, 'message' => $msg, 'unidade_cod' => $found_unidade, 'unidade' => $unitName]);
            exit;
        }
    }

    $beneficiario = new Beneficiario();

    // Atribuir valores do formulário
    $beneficiario->setNis($_POST['nis'] ?? null);
    $beneficiario->setCpf($_POST['cpf'] ?? null);
    $beneficiario->setNome($_POST['nome'] ?? null);
    $beneficiario->setCod_bairro($_POST['cod_bairro'] ?? null);
    $beneficiario->setEndereco($_POST['endereco'] ?? null);
    $beneficiario->setComplemento($_POST['complemento'] ?? null);
    $beneficiario->setTelefone($_POST['telefone'] ?? null);
    $beneficiario->setCod_tipo($_POST['cod_tipo'] ?? null);
    $beneficiario->setCod_usuario($_POST['cod_usuario'] ?? ($_SESSION['user_id'] ?? null));
    $beneficiario->setCod_unidade($submitted_unidade); // Definir a unidade do usuário
    $beneficiario->setSituacao(1); // Ativo por padrão

    // Se for admin e enviou categoria
    if (!empty($_POST['cod_categoria']) && ($_SESSION['int_level'] ?? 0) == 1) {
        $categoria = new Categoria();
        $categoriaObj = $categoria->buscarPorId($_POST['cod_categoria']);
        if ($categoriaObj) {
            $beneficiario->setCategoria($categoriaObj);
        }
    }

    // Inserir beneficiário
    if ($beneficiario->inserirBeneficiario()) {
        echo json_encode(['success' => true, 'message' => 'Beneficiário cadastrado com sucesso']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar beneficiário']);
        exit;
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
