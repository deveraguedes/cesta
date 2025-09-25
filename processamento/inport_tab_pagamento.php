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

            // prepara INSERT com ON CONFLICT (cpf, periodo) DO NOTHING
            $sqlInsert = "INSERT INTO beneficiario.folha 
                            (cod_familiar, cpf, nis, nome, endereco, bairro, cep, tel1, tel2, periodo)
                          VALUES 
                            (:cod_familiar, :cpf, :nis, :nome, :endereco, :bairro, :cep, :tel1, :tel2, :periodo)
                          ON CONFLICT (cpf, periodo) DO NOTHING";
            $stmtInsert = $pdo->prepare($sqlInsert);

            $periodo = date("m/Y");
            $inseridos = 0;
            $linhas = 0;

            // inicia transação para acelerar
            $pdo->beginTransaction();

            while (($data = fgetcsv($handle, 0, ";")) !== false) {
                $linhas++;
                $cpf = preg_replace('/\D/', '', $data[1]);

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

                // conta apenas os realmente inseridos
                if ($stmtInsert->rowCount() > 0) {
                    $inseridos++;
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
