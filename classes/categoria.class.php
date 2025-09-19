<?php
// Inclui o arquivo de conexão com o banco de dados.
require_once 'conexao.class.php';

// Inicia a sessão apenas se necessário.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Categoria
{
    private $db;

    public function __construct()
    {
        // Conecta ao banco de dados ao instanciar a classe.
        $this->db = Database::conexao();
    }

    /**
     * Lista todas as categorias cadastradas no banco de dados.
     *
     * @return array Array associativo contendo as categorias (cod_categoria, vch_categoria),
     *               ou array vazio em caso de erro.
     */
    public function listarCategorias(): array
    {
        try {
            $sql = "SELECT id_categoria, vch_categoria 
                      FROM beneficiario.categoria 
                  ORDER BY vch_categoria ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("[Categoria::listarCategorias] Erro: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma categoria pelo ID.
     *
     * @param int $id
     * @return array|null Retorna os dados da categoria ou null se não encontrada.
     */
    public function buscarPorId(int $id): ?array
    {
        try {
            $sql = "SELECT id_categoria, vch_categoria 
                      FROM beneficiario.categoria 
                     WHERE cod_categoria = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            return $categoria ?: null;
        } catch (PDOException $e) {
            error_log("[Categoria::buscarPorId] Erro: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Adiciona uma nova categoria.
     *
     * @param string $nome
     * @return bool Verdadeiro se inserido com sucesso, falso caso contrário.
     */
    public function adicionar(string $nome): bool
    {
        try {
            $sql = "INSERT INTO beneficiario.categoria (vch_categoria) 
                    VALUES (:nome)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[Categoria::adicionar] Erro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove uma categoria pelo ID.
     *
     * @param int $id
     * @return bool Verdadeiro se excluído com sucesso, falso caso contrário.
     */
    public function remover(int $id): bool
    {
        try {
            $sql = "DELETE FROM beneficiario.categoria 
                     WHERE id_categoria = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[Categoria::remover] Erro: " . $e->getMessage());
            return false;
        }
    }
}
