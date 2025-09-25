<?php
require_once "../classes/conexao.class.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $tmpName = $_FILES['csvfile']['tmp_name'];

    if (is_uploaded_file($tmpName)) {
        $pdo = Database::conexao();

        $handle = fopen($tmpName, "r");
        if ($handle !== false) {
            // skip header
            fgetcsv($handle, 0, ";");

            $sql = "INSERT INTO beneficiario.folha 
                      (cod_familiar, cpf, nis, nome, endereco, bairro, cep, tel1, tel2, periodo)
                    VALUES 
                      (:cod_familiar, :cpf, :nis, :nome, :endereco, :bairro, :cep, :tel1, :tel2, :periodo)";
            $stmt = $pdo->prepare($sql);

            $periodo = date("m/Y");

            while (($data = fgetcsv($handle, 0, ";")) !== false) {
                $stmt->execute([
                    ':cod_familiar' => trim($data[0]),
                    ':cpf'          => preg_replace('/\D/', '', $data[1]),
                    ':nis'          => trim($data[2]),
                    ':nome'         => trim($data[3]),
                    ':endereco'     => trim($data[5]),
                    ':bairro'       => trim($data[6]),
                    ':cep'          => trim($data[7]),
                    ':tel1'    => trim($data[8]),
                    ':tel2'    => trim($data[9]),
                    ':periodo'      => $periodo
                ]);
            }
            fclose($handle);
        }
    }
}

// After finishing, redirect back
header("Location: ../beneficiario.php");
exit;
