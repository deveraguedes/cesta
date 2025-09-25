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

            // prepara INSERT
            $sqlInsert = "INSERT INTO beneficiario.folha 
                            (cod_familiar, cpf, nis, nome, endereco, bairro, cep, tel1, tel2, periodo)
                          VALUES 
                            (:cod_familiar, :cpf, :nis, :nome, :endereco, :bairro, :cep, :tel1, :tel2, :periodo)";
            $stmtInsert = $pdo->prepare($sqlInsert);

            // prepara SELECT para checar duplicados
            $sqlCheck = "SELECT 1 FROM beneficiario.folha WHERE cpf = :cpf AND periodo = :periodo LIMIT 1";
            $stmtCheck = $pdo->prepare($sqlCheck);

            $periodo = date("m/Y");
            $inseridos = 0;
            $duplicados = 0;

            while (($data = fgetcsv($handle, 0, ";")) !== false) {
                $cpf = preg_replace('/\D/', '', $data[1]);

                // checa se já existe esse CPF no mesmo período
                $stmtCheck->execute([
                    ':cpf' => $cpf,
                    ':periodo' => $periodo
                ]);

                if ($stmtCheck->fetch()) {
                    $duplicados++;
                    continue; // pula duplicado
                }

                // insere se não existir
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
                $inseridos++;
            }
            fclose($handle);

            $_SESSION['import_msg'] = "Importação concluída: $inseridos inseridos, $duplicados duplicados ignorados.";
        }
    }
}

// redireciona de volta
header("Location: ../beneficiario.php");
exit;
