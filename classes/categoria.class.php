<?php
include_once('conexao.class.php');
class Categoria {

    // Retorna todas as categorias
    public function listarCategorias() {
        $pdo = Database::conexao();
        $sql = "SELECT cod_categoria, vch_categoria 
                  FROM beneficiario.categoria 
              ORDER BY vch_categoria ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cria categorias padrão caso não existam
    public function criarCategoriasPadrao() {
        $pdo = Database::conexao();
        $categoriasPadrao = [
            'Músico',
            'Pai ou Mãe de Criança Atípica',
            'Bolsa Família',
            'Idoso',
            'PCD'
        ];
        foreach ($categoriasPadrao as $nome) {
            $sql = "INSERT INTO beneficiario.categoria (vch_categoria)
                    SELECT :nome1
                    WHERE NOT EXISTS (
                        SELECT 1 FROM beneficiario.categoria WHERE vch_categoria = :nome2
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nome1', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':nome2', $nome, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    // Adiciona uma nova categoria
    public function adicionarCategoria($nome) {
        if (empty(trim($nome))) {
            return false;
        }
        $pdo = Database::conexao();
        $sql = "INSERT INTO beneficiario.categoria (vch_categoria) VALUES (:nome)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Exclui uma categoria (se não estiver em uso)
    public function excluirCategoria($id) {
        $pdo = Database::conexao();

        // Verifica se algum beneficiário usa essa categoria
        $sql = "SELECT COUNT(*) FROM beneficiario.beneficiario WHERE cod_categoria = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $qtd = $stmt->fetchColumn();

        if ($qtd > 0) {
            return false; // não pode excluir categoria em uso
        }

        $sql = "DELETE FROM beneficiario.categoria WHERE cod_categoria = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
