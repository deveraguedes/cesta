<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include_once(__DIR__ . '/../../classes/beneficiario.class.php');
include_once(__DIR__ . '/../../classes/usuarios.class.php');
include_once(__DIR__ . '/../../classes/categoria.class.php');
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
    // Helpers para normalização de entradas
    $nullIfEmpty = function($v) {
        if (!isset($v)) return null;
        if (is_string($v)) {
            $t = trim($v);
            return $t === '' ? null : $t;
        }
        return $v === '' ? null : $v;
    };
    $intOrNull = function($v) {
        if (!isset($v)) return null;
        if (is_string($v)) {
            $t = trim($v);
            if ($t === '' || !is_numeric($t)) return null;
            return (int)$t;
        }
        return is_numeric($v) ? (int)$v : null;
    };

    // Determinar se é atualização (alterar) ou inserção (inserir)
    $isUpdate = false;
    $currentId = null;
    if (isset($_POST['MM_action']) && (string)$_POST['MM_action'] === '2') {
        $isUpdate = true;
    }
    if (isset($_POST['cod_beneficiario']) && $_POST['cod_beneficiario'] !== '') {
        $currentId = (int) $_POST['cod_beneficiario'];
        if ($currentId > 0) $isUpdate = true;
    }

    // Basic server-side validation — require at least one identifier (CPF or NIS)
    $cpf_raw = $_POST['cpf'] ?? '';
    $nis_raw = $_POST['nis'] ?? '';
    $cpf_digits = preg_replace('/\D/', '', (string)$cpf_raw);
    $nis_digits = preg_replace('/\D/', '', (string)$nis_raw);

    if ($cpf_digits === '' && $nis_digits === '') {
        echo json_encode(['success' => false, 'message' => 'Informe NIS ou CPF. Pelo menos um é obrigatório.']);
        exit;
    }

    // If CPF provided, it must be valid
    if ($cpf_digits !== '' && !validate_cpf($cpf_digits)) {
        echo json_encode(['success' => false, 'message' => 'CPF inválido']);
        exit;
    }

    // If NIS looks like CPF, reject to avoid swapped fields
    if ($nis_digits !== '' && validate_cpf($nis_digits)) {
        echo json_encode(['success' => false, 'message' => 'NIS parece ser um CPF — verifique se os campos não estão trocados']);
        exit;
    }

    // If NIS provided, it must be valid as PIS
    if ($nis_digits !== '' && !validate_pis($nis_digits)) {
        echo json_encode(['success' => false, 'message' => 'NIS com formato inválido']);
        exit;
    }

    // --- Duplicate checks (beneficiario list and folha) ---
    // Use digit-only comparisons on DB side via regexp_replace to be robust against formatting
    $pdo = Database::conexao();
    // Definir unidade com base no nível do usuário: nível 1 pode escolher, outros usam a unidade da sessão
    $user_level = $_SESSION['int_level'] ?? ($_SESSION['int_nivel'] ?? null);
    if ($user_level == 1) {
        $submitted_unidade = isset($_POST['cod_unidade']) && $_POST['cod_unidade'] !== ''
            ? (int) $_POST['cod_unidade']
            : ($_SESSION['cod_unidade'] ?? null);
    } else {
        $submitted_unidade = $_SESSION['cod_unidade'] ?? null;
    }

    // Helper to fetch unidade name
    $getUnidadeName = function($cod_unidade) use ($pdo) {
        if (empty($cod_unidade)) return null;
        $s = $pdo->prepare("SELECT vch_unidade FROM beneficiario.unidade WHERE cod_unidade = :cod_unidade LIMIT 1");
        $s->bindValue(':cod_unidade', $cod_unidade, PDO::PARAM_INT);
        $s->execute();
        $r = $s->fetch(PDO::FETCH_ASSOC);
        return $r ? $r['vch_unidade'] : null;
    };

    // Check CPF duplicates (exclui o próprio registro quando for atualização)
    if ($cpf_digits !== '') {
        // Check in folha first
    $sf = $pdo->prepare("SELECT 1 FROM beneficiario.folha WHERE regexp_replace(CAST(cpf AS text), '\\D', '', 'g') = :cpf LIMIT 1");
        $sf->bindValue(':cpf', $cpf_digits);
        $sf->execute();
        if ($sf->fetch()) {
            echo json_encode(['success' => false, 'message' => 'CPF já está na folha de pagamento']);
            exit;
        }

        // Check in beneficiario table
        $sqlCpf = "SELECT cod_beneficiario, cod_unidade
                    FROM beneficiario.beneficiario
                   WHERE regexp_replace(CAST(cpf AS text), '\\D', '', 'g') = :cpf" . ($isUpdate ? " AND cod_beneficiario <> :id" : "") . "
                   LIMIT 1";
        $sb = $pdo->prepare($sqlCpf);
        $sb->bindValue(':cpf', $cpf_digits);
        if ($isUpdate && $currentId) {
            $sb->bindValue(':id', $currentId, PDO::PARAM_INT);
        }
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

    // Check NIS duplicates (exclui o próprio registro quando for atualização)
    if ($nis_digits !== '') {
        // Check in folha first
    $sf2 = $pdo->prepare("SELECT 1 FROM beneficiario.folha WHERE regexp_replace(CAST(nis AS text), '\\D', '', 'g') = :nis LIMIT 1");
        $sf2->bindValue(':nis', $nis_digits);
        $sf2->execute();
        if ($sf2->fetch()) {
            echo json_encode(['success' => false, 'message' => 'NIS já está na folha de pagamento']);
            exit;
        }

        // Check in beneficiario table
        $sqlNis = "SELECT cod_beneficiario, cod_unidade
                    FROM beneficiario.beneficiario
                   WHERE regexp_replace(CAST(COALESCE(nis::text, '') AS text), '\\D', '', 'g') = :nis" . ($isUpdate ? " AND cod_beneficiario <> :id" : "") . "
                   LIMIT 1";
        $sb2 = $pdo->prepare($sqlNis);
        $sb2->bindValue(':nis', $nis_digits);
        if ($isUpdate && $currentId) {
            $sb2->bindValue(':id', $currentId, PDO::PARAM_INT);
        }
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

    // Normalizar e atribuir valores do formulário
    $beneficiario->setNis($nis_digits !== '' ? $nis_digits : null);
    $beneficiario->setCpf($cpf_digits !== '' ? $cpf_digits : null);
    $beneficiario->setNome(trim($_POST['nome'] ?? ''));
    $beneficiario->setCod_bairro($intOrNull($_POST['cod_bairro'] ?? null));
    $beneficiario->setLocalidade($nullIfEmpty($_POST['localidade'] ?? null));
    $beneficiario->setEndereco(trim($_POST['endereco'] ?? ''));
    $beneficiario->setComplemento($nullIfEmpty($_POST['complemento'] ?? null));
    $beneficiario->setTelefone($nullIfEmpty($_POST['telefone'] ?? null));
    $beneficiario->setCod_tipo($intOrNull($_POST['cod_tipo'] ?? null));
    $beneficiario->setCod_usuario($intOrNull($_POST['cod_usuario'] ?? ($_SESSION['user_id'] ?? null)));
    $beneficiario->setCod_unidade($intOrNull($submitted_unidade)); // Definir a unidade do usuário
    $beneficiario->setSituacao(1); // Ativo por padrão

    // Controle de nível: apenas nível 1 define categoria
    if ($user_level == 1) {
        $beneficiario->setCategoria($intOrNull($_POST['cod_categoria'] ?? null));
    }

    // Validações servidor para campos obrigatórios da inclusão/alteração
    if (empty($beneficiario->getNome())) {
        echo json_encode(['success' => false, 'message' => "Nome do beneficiário é obrigatório."]);
        exit;
    }
    if ($beneficiario->getCod_bairro() === null) {
        echo json_encode(['success' => false, 'message' => "Bairro é obrigatório."]);
        exit;
    }
    if ($beneficiario->getCod_tipo() === null) {
        echo json_encode(['success' => false, 'message' => "Tipo de beneficiário é obrigatório."]);
        exit;
    }
    if (empty($_POST['endereco'] ?? '')) {
        echo json_encode(['success' => false, 'message' => "Endereço é obrigatório."]);
        exit;
    }

    // Inserir ou Alterar beneficiário conforme a ação
    if ($isUpdate) {
        // Monta array de dados para atualização
        $dados = [
            'cod_beneficiario' => $currentId,
            'nis'             => ($nis_digits !== '' ? $nis_digits : null),
            'cpf'             => ($cpf_digits !== '' ? $cpf_digits : null),
            'nome'            => trim($_POST['nome'] ?? ''),
            'cod_bairro'      => $intOrNull($_POST['cod_bairro'] ?? null),
            'localidade'      => $nullIfEmpty($_POST['localidade'] ?? null),
            'cod_usuario'     => $intOrNull($_POST['cod_usuario'] ?? ($_SESSION['user_id'] ?? null)),
            'dt_cadastro'     => date('Y-m-d'),
            'cod_unidade'     => $intOrNull($submitted_unidade),
            'cpf_responsavel' => $nullIfEmpty($_POST['cpf_responsavel'] ?? null),
            'vch_responsavel' => $nullIfEmpty($_POST['vch_responsavel'] ?? null),
            'cod_tipo'        => $intOrNull($_POST['cod_tipo'] ?? null),
            'cep'             => $nullIfEmpty($_POST['cep'] ?? null),
            'endereco'        => trim($_POST['endereco'] ?? ''),
            'complemento'     => $nullIfEmpty($_POST['complemento'] ?? null),
            'telefone'        => $nullIfEmpty($_POST['telefone'] ?? null),
            'cod_categoria'   => ($user_level == 1 ? $intOrNull($_POST['cod_categoria'] ?? null) : null),
            'situacao'        => 1,
        ];

        if ($beneficiario->alterarBeneficiario($dados, (int)$user_level)) {
            echo json_encode(['success' => true, 'message' => 'Beneficiário atualizado com sucesso']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar beneficiário', 'dados' => $dados]);
            exit;
        }
    } else {
        // Inserção
        if ($beneficiario->inserirBeneficiario()) {
            echo json_encode(['success' => true, 'message' => 'Beneficiário cadastrado com sucesso']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar beneficiário']);
            exit;
        }
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
    exit;
}
