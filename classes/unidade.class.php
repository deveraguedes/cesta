<?php
include_once('conexao.class.php');

class Unidade
{
  private $db;
  private $cod_unidade;
  private $vch_unidade;

  public function __construct()
  {
    $this->db = Database::conexao();
  }

  public function setCod_unidade($cod_unidade)
  {
    $this->cod_unidade = $cod_unidade;
  }

  public function setVch_unidade($vch_unidade)
  {
    $this->vch_unidade = $vch_unidade;
  }

  public function getCod_unidade()
  {
    return $this->cod_unidade;
  }

  public function getVch_unidade()
  {
    return $this->vch_unidade;
  }

  public function inserirUnidade()
  {
    try {
      $sql = "INSERT INTO beneficiario.unidade (cod_unidade, vch_unidade) VALUES (:cod_unidade, :vch_unidade)";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':cod_unidade', $this->cod_unidade);
      $stmt->bindParam(':vch_unidade', $this->vch_unidade);
      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Erro ao inserir unidade: " . $e->getMessage());
      return false;
    }
  }

  public function alterarUnidade()
  {
    try {
      $sql = "UPDATE beneficiario.unidade SET vch_unidade = :vch_unidade WHERE cod_unidade = :cod_unidade";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':cod_unidade', $this->cod_unidade);
      $stmt->bindParam(':vch_unidade', $this->vch_unidade);
      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Erro ao alterar unidade: " . $e->getMessage());
      return false;
    }
  }

  public function excluirUnidade()
  {
    try {
      $sql = "DELETE FROM beneficiario.unidade WHERE cod_unidade = :cod_unidade";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':cod_unidade', $this->cod_unidade);
      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Erro ao excluir unidade: " . $e->getMessage());
      return false;
    }
  }

  public function exibirUnidade()
  {
    try {
      $sql = "SELECT * FROM beneficiario.unidade ORDER BY vch_unidade ASC";
      $stmt = $this->db->prepare($sql);
      $stmt->execute();
      return $stmt;
    } catch (PDOException $e) {
      error_log("Erro ao exibir unidades: " . $e->getMessage());
      return false;
    }
  }

  public function exibirUnidadeCod($cod)
  {
    try {
      $sql = "SELECT * FROM beneficiario.unidade WHERE cod_unidade = :cod";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':cod', $cod);
      $stmt->execute();
      return $stmt;
    } catch (PDOException $e) {
      error_log("Erro ao buscar unidade por código: " . $e->getMessage());
      return false;
    }
  }

  public function localizarUnidade($nome)
  {
    try {
      $sql = "SELECT * FROM beneficiario.unidade WHERE vch_unidade ILIKE :nome";
      $stmt = $this->db->prepare($sql);
      $nomeParam = '%' . $nome . '%';
      $stmt->bindParam(':nome', $nomeParam);
      $stmt->execute();
      return $stmt;
    } catch (PDOException $e) {
      error_log("Erro ao localizar unidade: " . $e->getMessage());
      return false;
    }
  }
}
