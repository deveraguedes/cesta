<?php
include_once('conexao.class.php');

class Categoria {

    private $db;

    public function __construct() {
        $this->db = Database::conexao();
    }

    // Retorna todas as categorias
    public function listarCategorias() {
        $sql = "SELECT cod_categoria, vch_categoria 
                  FROM beneficiario.categoria 
              ORDER BY vch_categoria ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cria categorias padrão caso não existam
    public function criarCategoriasPadrao() {
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
                        SELECT 1 FROM beneficiario.categoria WHERE LOWER(vch_categoria) = LOWER(:nome2)
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome1', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':nome2', $nome, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    // Adiciona uma nova categoria (com verificação de duplicata)
    public function adicionarCategoria($nome) {
        if (empty(trim($nome))) {
            throw new Exception("O nome da categoria não pode estar vazio.");
        }

        $nome = trim($nome);

        // Verifica se já existe categoria com mesmo nome (ignora maiúsculas/minúsculas)
        $sql = "SELECT COUNT(*) FROM beneficiario.categoria WHERE LOWER(vch_categoria) = LOWER(:nome)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("A categoria '{$nome}' já está cadastrada.");
        }

        // Insere se não existir
        $sql = "INSERT INTO beneficiario.categoria (vch_categoria) VALUES (:nome)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();

        return true;
    }

    // Exclui uma categoria (se não estiver em uso)
    public function excluirCategoria($id) {
        // Verifica se algum beneficiário usa essa categoria
        $sql = "SELECT COUNT(*) FROM beneficiario.beneficiario WHERE cod_categoria = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $qtd = $stmt->fetchColumn();

        if ($qtd > 0) {
            throw new Exception("Não é possível excluir: categoria em uso por beneficiários.");
        }

        $sql = "DELETE FROM beneficiario.categoria WHERE cod_categoria = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    }

    // Busca categoria por ID
    public function buscarPorId($id) {
        if (!$id) return null;

        try {
            $sql = "SELECT cod_categoria, vch_categoria 
                      FROM beneficiario.categoria 
                     WHERE cod_categoria = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[Categoria::buscarPorId] Erro: " . $e->getMessage());
            return null;
        }
    }
}
