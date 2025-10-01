<?php
require_once "../classes/conexao.class.php";

session_start(); // garante que a sessão esteja ativa

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $tmpName = $_FILES['csvfile']['tmp_name'];

    if (is_uploaded_file($tmpName)) {
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
            // pula cabeçalho
            fgetcsv($handle, 0, ";");

            $periodo = date("m/Y");

            // Pré-carrega CPFs já existentes no período para reduzir verificações por linha
            $cpfExistentes = [];
            $stmtCpfs = $pdo->prepare("SELECT cpf FROM beneficiario.folha WHERE periodo = :periodo");
            $stmtCpfs->execute([':periodo' => $periodo]);
            while ($row = $stmtCpfs->fetch(PDO::FETCH_ASSOC)) {
                // normaliza para dígitos
                $cpfExistentes[preg_replace('/\D/', '', (string)$row['cpf'])] = true;
            }

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

            while (($data = fgetcsv($handle, 0, ";")) !== false) {
                $linhas++;
                $cpf = preg_replace('/\D/', '', $data[1]);

                // pula se CPF já existe no período ou já foi visto neste arquivo
                if ($cpf === '' || isset($cpfExistentes[$cpf]) || isset($cpfArquivo[$cpf])) {
                    continue;
                }

                $stmtInsert->execute([
                    ':cod_familiar' => trim($data[0]),
                    ':cpf'          => $cpf,
                    ':nis'          => trim($data[2]),
                    ':nome'         => trim($data[3]),
                    ':endereco'     => trim($data[5]),
                    ':bairro'       => trim($data[6]),
                    ':cep'          => trim($data[7]),
                    ':tel1'         => trim($data[8]),
                    ':tel2'         => trim($data[9]),
                    ':periodo'      => $periodo
                ]);

                if ($stmtInsert->rowCount() > 0) {
                    $inseridos++;
                    $cpfArquivo[$cpf] = true; // marca como inserido neste arquivo
                    $desdeUltimoCommit++;
                    // commit parcial para aliviar longas transações em uploads enormes
                    if ($desdeUltimoCommit >= $batchSize) {
                        $pdo->commit();
                        $pdo->beginTransaction();
                        $desdeUltimoCommit = 0;
                    }
                }
            }

            $pdo->commit();
            fclose($handle);

            $duplicados = $linhas - $inseridos;
            $_SESSION['import_msg'] = "Importação concluída: $inseridos inseridos, $duplicados duplicados ignorados.";
        }
    }
}

// redireciona de volta
header("Location: ../beneficiario.php");
exit;
