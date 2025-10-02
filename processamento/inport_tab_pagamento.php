<?php
require_once "../classes/conexao.class.php";

session_start(); // garante que a sessão esteja ativa

// Modo debug desativado para módulo beneficiário
$__DEBUG_IMPORT__ = false;

// Logging helper: write detailed errors for troubleshooting schema/data issues (no-op when debug disabled)
function logImportError($msg, $context = []) {
    global $__DEBUG_IMPORT__;
    if (!$__DEBUG_IMPORT__) return; // desativa escrita de logs em modo não-debug
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/import_folha.log';
    $ts = date('Y-m-d H:i:s');
    $ctx = $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $line = "[$ts] $msg $ctx\n";
    file_put_contents($logFile, $line, FILE_APPEND);
}

// Buffer temporário para exibir logs diretamente na página (no-op when debug disabled)
$__import_debug_buffer = [];
function debugEcho($msg, $context = []) {
    global $__import_debug_buffer, $__DEBUG_IMPORT__;
    if (!$__DEBUG_IMPORT__) return; // desativa saída de diagnóstico
    $ctx = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $line = '[' . date('H:i:s') . '] ' . $msg . $ctx;
    $__import_debug_buffer[] = $line;
    logImportError($msg, $context);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $tmpName = $_FILES['csvfile']['tmp_name'];

    if (is_uploaded_file($tmpName)) {
        debugEcho('Upload recebido', ['tmpName' => $tmpName, 'size' => $_FILES['csvfile']['size'] ?? null]);
        // calcula hash do arquivo
        $hash = md5_file($tmpName);

        // verifica se já foi importado nesta sessão
        if (isset($_SESSION['last_import_hash']) && $_SESSION['last_import_hash'] === $hash) {
            $_SESSION['import_msg'] = "Este arquivo já foi importado nesta sessão.";
            header("Location: ../beneficiario.php");
            exit;
        }

        // salva hash para próxima comparação
        $_SESSION['last_import_hash'] = $hash;

        $pdo = Database::conexao();

        $handle = fopen($tmpName, "r");
        if ($handle !== false) {
            // Detecta delimitador do CSV (tab, ponto-e-vírgula ou vírgula)
            $headerLine = fgets($handle);
            if ($headerLine === false) {
                $_SESSION['import_msg'] = 'Erro: não foi possível ler o cabeçalho do arquivo.';
                debugEcho('Falha ao ler cabeçalho do CSV');
                header("Location: ../beneficiario.php");
                exit;
            }
            $tabCount   = substr_count($headerLine, "\t");
            $semiCount  = substr_count($headerLine, ";");
            $commaCount = substr_count($headerLine, ",");
            if ($tabCount > 0 && $tabCount >= $semiCount && $tabCount >= $commaCount) {
                $delim = "\t";
            } elseif ($semiCount > 0 && $semiCount >= $commaCount) {
                $delim = ";";
            } else {
                $delim = ",";
            }
            debugEcho('Delimitador CSV detectado', ['delim' => $delim, 'header' => trim($headerLine)]);

            $periodo = date("m/Y");

            // Verifica colunas esperadas da tabela para detectar mudanças de estrutura
            try {
                $pdoMeta = Database::conexao();
                $colsStmt = $pdoMeta->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = 'beneficiario' AND table_name = 'folha'");
                $colsStmt->execute();
                $existingCols = [];
                while ($r = $colsStmt->fetch(PDO::FETCH_ASSOC)) { $existingCols[strtolower($r['column_name'])] = true; }
                $required = ['cod_familiar','cpf','nis','nome','endereco','bairro','cep','tel1','tel2','periodo'];
                $missing = [];
                foreach ($required as $c) { if (!isset($existingCols[$c])) $missing[] = $c; }
                if (!empty($missing)) {
                debugEcho('Estrutura da tabela não compatível', ['missing_columns' => $missing]);
                $_SESSION['import_msg'] = 'Erro: estrutura da tabela folha mudou. Colunas ausentes: ' . implode(', ', $missing);
                header("Location: ../beneficiario.php");
                exit;
            }
        } catch (Throwable $metaEx) {
            // Continua mesmo que a introspecção falhe; registra para análise
            debugEcho('Falha ao ler metadados da tabela', ['error' => $metaEx->getMessage()]);
        }

            // Pré-carrega CPFs já existentes no período para reduzir verificações por linha
            $cpfExistentes = [];
            $stmtCpfs = $pdo->prepare("SELECT cpf::text AS cpf FROM beneficiario.folha WHERE periodo = :periodo");
            $stmtCpfs->execute([':periodo' => $periodo]);
            while ($row = $stmtCpfs->fetch(PDO::FETCH_ASSOC)) {
                // normaliza para dígitos e ignora CPFs somente-zeros ou nulos para checagem de duplicidade
                $ncpf = preg_replace('/\D/', '', (string)$row['cpf']);
                if ($ncpf !== '' && !preg_match('/^0+$/', $ncpf)) {
                    $cpfExistentes[$ncpf] = true;
                }
            }
            debugEcho('CPFs existentes pré-carregados', ['count' => count($cpfExistentes), 'periodo' => $periodo]);

            // Track CPFs já vistos neste arquivo para evitar duplicidades dentro do mesmo upload
            $cpfArquivo = [];

            // prepara INSERT simples (sem NOT EXISTS) — checagem acontece em PHP via conjuntos
            $sqlInsert = "INSERT INTO beneficiario.folha 
                            (cod_familiar, cpf, nis, nome, endereco, bairro, cep, tel1, tel2, periodo)
                          VALUES 
                            (:cod_familiar, :cpf, :nis, :nome, :endereco, :bairro, :cep, :tel1, :tel2, :periodo)";
            $stmtInsert = $pdo->prepare($sqlInsert);

            $inseridos = 0;
            $linhas = 0;
            $batchSize = 2000; // commit parcial para grandes volumes
            $desdeUltimoCommit = 0;

            // inicia transação para acelerar
            $pdo->beginTransaction();

            $linhaCsv = 0;
            while (($data = fgetcsv($handle, 0, $delim)) !== false) {
                $linhaCsv++;
                $linhas++;
                // Extrai e normaliza campos
                $codFamiliarRaw = isset($data[0]) ? trim($data[0]) : '';
                $cpfDigits      = isset($data[1]) ? preg_replace('/\D/', '', $data[1]) : '';
                $nisDigits      = isset($data[2]) ? preg_replace('/\D/', '', $data[2]) : '';
                $nome           = isset($data[3]) ? trim($data[3]) : null;
                // índice 4 é SITFAM (não utilizado)
                $endereco       = isset($data[5]) ? trim($data[5]) : null;
                $bairro         = isset($data[6]) ? trim($data[6]) : null;
                $cep            = isset($data[7]) ? trim($data[7]) : null;
                $tel1           = isset($data[8]) ? trim($data[8]) : null;
                $tel2           = isset($data[9]) ? trim($data[9]) : null;
                $cpf = $cpfDigits !== '' ? $cpfDigits : null;
                $nis = $nisDigits !== '' ? $nisDigits : null;
                $codFamiliar = (is_numeric($codFamiliarRaw) ? (int)$codFamiliarRaw : null);

                // pula apenas CPFs duplicados (não considerar nulo ou somente-zeros como duplicado)
                $isZeroLike = ($cpf !== null && preg_match('/^0+$/', $cpf));
                if (!$isZeroLike && $cpf !== null && (isset($cpfExistentes[$cpf]) || isset($cpfArquivo[$cpf]))) {
                    $reason = isset($cpfExistentes[$cpf]) ? 'Duplicado no período' : 'Duplicado no arquivo';
                    debugEcho('Linha ignorada', [
                        'linha_csv' => $linhaCsv,
                        'cpf' => $cpf,
                        'nome' => $nome,
                        'motivo' => $reason
                    ]);
                    continue;
                }
                try {
                    // Bind com tipos corretos para evitar erros após mudanças de schema
                    if ($codFamiliar === null) $stmtInsert->bindValue(':cod_familiar', null, PDO::PARAM_NULL);
                    else $stmtInsert->bindValue(':cod_familiar', $codFamiliar, PDO::PARAM_INT);

                    // cpf/nis são numéricos na base; usar string para preservar dígitos e evitar overflow
                    $stmtInsert->bindValue(':cpf', $cpf, $cpf === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmtInsert->bindValue(':nis', $nis, $nis === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmtInsert->bindValue(':nome', $nome, $nome === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmtInsert->bindValue(':endereco', $endereco, $endereco === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmtInsert->bindValue(':bairro', $bairro, $bairro === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmtInsert->bindValue(':cep', $cep, $cep === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmtInsert->bindValue(':tel1', $tel1, $tel1 === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmtInsert->bindValue(':tel2', $tel2, $tel2 === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmtInsert->bindValue(':periodo', $periodo, PDO::PARAM_STR);

                    $stmtInsert->execute();

                    if ($stmtInsert->rowCount() > 0) {
                        $inseridos++;
                        // só marca para controle de duplicidade se CPF não for nulo ou somente-zeros
                        if ($cpf !== null && !preg_match('/^0+$/', $cpf)) {
                            $cpfArquivo[$cpf] = true;
                        }
                        $desdeUltimoCommit++;
                        // commit parcial para aliviar longas transações em uploads enormes
                        if ($desdeUltimoCommit >= $batchSize) {
                            $pdo->commit();
                            $pdo->beginTransaction();
                            $desdeUltimoCommit = 0;
                            debugEcho('Commit parcial realizado', ['inseridos' => $inseridos, 'linhas_processadas' => $linhas]);
                        }
                    }
                } catch (PDOException $pex) {
                    // registra erro detalhado mas continua o processamento
                    debugEcho('Falha ao inserir linha', [
                        'linha_csv' => $linhaCsv,
                        'error' => $pex->getMessage(),
                        'sqlstate' => isset($pex->errorInfo[0]) ? $pex->errorInfo[0] : null,
                        'driver_code' => isset($pex->errorInfo[1]) ? $pex->errorInfo[1] : null,
                        'driver_msg' => isset($pex->errorInfo[2]) ? $pex->errorInfo[2] : null,
                        'dados' => [
                            'cod_familiar' => $codFamiliar,
                            'cpf' => $cpf,
                            'nis' => $nis,
                            'nome' => $nome,
                            'endereco' => $endereco,
                            'bairro' => $bairro,
                            'cep' => $cep,
                            'tel1' => $tel1,
                            'tel2' => $tel2,
                            'periodo' => $periodo,
                        ]
                    ]);
                    // não incrementa inseridos; segue para próxima linha
                } catch (Throwable $t) {
                    debugEcho('Erro inesperado ao inserir linha', [
                        'linha_csv' => $linhaCsv,
                        'error' => $t->getMessage(),
                    ]);
                }
            }

            try { $pdo->commit(); } catch (Throwable $t) { /* best-effort */ }
            fclose($handle);

            $duplicados = $linhas - $inseridos;
            $_SESSION['import_msg'] = "Importação concluída: $inseridos inseridos, $duplicados ignorados/duplicados. Consulte logs em logs/import_folha.log para detalhes.";
            debugEcho('Importação concluída', ['inseridos' => $inseridos, 'ignorados_ou_duplicados' => $duplicados, 'linhas_lidas' => $linhas]);
            // Em modo não-debug, não armazenar buffer na sessão
            if ($__DEBUG_IMPORT__) {
                $_SESSION['import_debug'] = $__import_debug_buffer;
            } else {
                unset($_SESSION['import_debug']);
            }
        }
    }
}

// redireciona de volta
// Modo debug desativado: não exibir página de debug

header("Location: ../beneficiario.php");
exit;
