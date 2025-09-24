<?php
require_once('conexao.class.php');
require_once('categoria.class.php');

class Beneficiario {
    private $db;
    private $categoria;
    private $cod_categoria;
    public $cod_beneficiario;
    public $nis;
    public $cpf;
    public $nome;
    public $cod_bairro;
    public $localidade;
    public $cod_usuario;
    public $dt_cadastro;
    public $cod_unidade;
    public $cpf_responsavel;
    public $vch_responsavel;
    public $inicio;
    public $limite;
    public $cod_tipo;
    public $cep;
    public $endereco;
    public $complemento;
    public $telefone;
    public $situacao;

    public function __construct() {
        $this->db = Database::conexao();
    }

    // Getters e Setters existentes...

    public function setCategoria($categoria) {
        if (!empty($categoria['cod_categoria'])) {
            $this->cod_categoria = $categoria['cod_categoria'];
        }
    }

    public function inserirBeneficiario() {
        try {
            $this->dt_cadastro = date("Y-m-d");

            $sql = "INSERT INTO beneficiario.beneficiario (
                nis, cpf, nome, cod_bairro, localidade, cod_usuario, 
                dt_cadastro, cod_unidade, cpf_responsavel, vch_responsavel,
                cod_tipo, cep, endereco, complemento, telefone, situacao,
                cod_categoria
            ) VALUES (
                :nis, :cpf, :nome, :cod_bairro, :localidade, :cod_usuario,
                :dt_cadastro, :cod_unidade, :cpf_responsavel, :vch_responsavel,
                :cod_tipo, :cep, :endereco, :complemento, :telefone, :situacao,
                :cod_categoria
            )";

            $stmt = $this->db->prepare($sql);
            
            // Bind dos parâmetros
            $stmt->bindValue(':nis', $this->nis ?: null);
            $stmt->bindValue(':cpf', $this->cpf ?: null);
            $stmt->bindValue(':nome', $this->nome);
            $stmt->bindValue(':cod_bairro', $this->cod_bairro);
            $stmt->bindValue(':localidade', $this->localidade);
            $stmt->bindValue(':cod_usuario', $this->cod_usuario);
            $stmt->bindValue(':dt_cadastro', $this->dt_cadastro);
            $stmt->bindValue(':cod_unidade', $this->cod_unidade);
            $stmt->bindValue(':cpf_responsavel', $this->cpf_responsavel);
            $stmt->bindValue(':vch_responsavel', $this->vch_responsavel);
            $stmt->bindValue(':cod_tipo', $this->cod_tipo);
            $stmt->bindValue(':cep', $this->cep);
            $stmt->bindValue(':endereco', $this->endereco);
            $stmt->bindValue(':complemento', $this->complemento);
            $stmt->bindValue(':telefone', $this->telefone);
            $stmt->bindValue(':situacao', $this->situacao);
            $stmt->bindValue(':cod_categoria', $this->cod_categoria ?: null);

            if ($stmt->execute()) {
                // Atualiza saldo da unidade
                $sql_saldo = "UPDATE beneficiario.saldo_unidade 
                             SET saldo = saldo - 1 
                             WHERE cod_unidade = :cod_unidade";
                             
                $stmt_saldo = $this->db->prepare($sql_saldo);
                $stmt_saldo->bindValue(':cod_unidade', $this->cod_unidade);
                $stmt_saldo->execute();
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("[Beneficiario::inserirBeneficiario] Erro: " . $e->getMessage());
            throw new Exception("Erro ao inserir beneficiário");
        }
    }

    // ... outros métodos existentes ...
}