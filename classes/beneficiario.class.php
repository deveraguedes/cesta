<?php
include_once('conexao.class.php');
include_once "categoria.class.php";

class Beneficiario
{
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




    public function setCod_beneficiario($cod_beneficiario)
    {
        $this->cod_beneficiario = $cod_beneficiario;
    }

    public function setNis($nis)
    {
        $this->nis = $nis;
    }

    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function setCod_bairro($cod_bairro)
    {
        $this->cod_bairro = $cod_bairro;
    }

    public function setLocalidade($localidade)
    {
        $this->localidade = $localidade;
    }

    public function setCod_usuario($cod_usuario)
    {
        $this->cod_usuario = $cod_usuario;
    }

    public function setDt_cadastro($dt_cadastro)
    {
        $this->dt_cadastro = $dt_cadastro;
    }

    public function setCod_unidade($cod_unidade)
    {
        $this->cod_unidade = $cod_unidade;
    }

    public function setCpf_responsavel($cpf_responsavel)
    {
        $this->cpf_responsavel = $cpf_responsavel;
    }

    public function setVch_responsavel($vch_responsavel)
    {
        $this->vch_responsavel = $vch_responsavel;
    }

    public function setCod_tipo($cod_tipo)
    {
        $this->cod_tipo = $cod_tipo;
    }

    public function setCep($cep)
    {
        $this->cep = $cep;
    }
    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;
    }
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;
    }

    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }

    public function setSituacao($situacao)
    {
        $this->situacao = $situacao;
    }

    public function setInicio($inicio)
    {
        $this->inicio = $inicio;
    }

    public function setLimite($limite)
    {
        $this->limite = $limite;
    }


    public function getCod_beneficiario()
    {
        return $this->cod_beneficiario;
    }

    public function getNis()
    {
        return $this->nis;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getCod_bairro()
    {
        return $this->cod_bairro;
    }

    public function getLocalidade()
    {
        return $this->localidade;
    }

    public function getCod_usuario()
    {
        return $this->cod_usuario;
    }

    public function getDt_cadastro()
    {
        return $this->dt_cadastro;
    }

    public function getCod_unidade()
    {
        return $this->cod_unidade;
    }

    public function getCpf_responsavel()
    {
        return $this->cpf_responsavel;
    }

    public function getVch_responsavel()
    {
        return $this->vch_responsavel;
    }
    public function getLimite()
    {
        return $this->limite;
    }

    public function getCod_tipo()
    {
        return $this->cod_tipo;
    }
   public function setCategoria($categoria) {
    if (is_array($categoria)) {
        // quando vier array (ex: do fetchAll)
        $this->cod_categoria = $categoria['cod_categoria'] ?? null;
    } elseif ($categoria instanceof Categoria) {
        // quando vier um objeto Categoria, você já espera receber o ID
        // por exemplo: $b->setCategoria(new Categoria())->buscarPorId(3)
        $dados = $categoria->buscarPorId($this->cod_categoria ?? 0);
        $this->cod_categoria = $dados['cod_categoria'] ?? null;
    } elseif (is_numeric($categoria)) {
        // aceita ID diretamente
        $this->cod_categoria = (int)$categoria;
    } else {
        $this->cod_categoria = null;
    }

}

    public function getCategoria()
    {
        return $this->categoria;
    }
    public function __construct()
    {
        $this->db = Database::conexao();
    }





public function inserirBeneficiario() {
    try {
        //  Validação básica
        if (empty($this->nome)) {
            throw new Exception("O campo 'nome' é obrigatório.");
        }
        if (empty($this->cod_bairro)) {
            throw new Exception("O campo 'bairro' é obrigatório.");
        }
        if (empty($this->cod_tipo)) {
            throw new Exception("O campo 'tipo' é obrigatório.");
        }
        
        // Verificar se há saldo disponível quando situacao = 1 (incluído na cesta)
        if ($this->situacao == 1 && !empty($this->cod_unidade)) {
            $stmt = $this->db->prepare("SELECT saldo FROM beneficiario.saldo_unidade WHERE cod_unidade = :cod_unidade");
            $stmt->bindValue(':cod_unidade', $this->cod_unidade, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || $result['saldo'] <= 0) {
                throw new Exception("Não há vagas disponíveis para esta unidade.");
            }
        }

        // Query de inserção
        $sql = "INSERT INTO beneficiario.beneficiario 
                (nis, cpf, nome, cod_bairro, localidade, cod_usuario, dt_cadastro, 
                 cod_unidade, cpf_responsavel, vch_responsavel, cod_tipo, cep, 
                 endereco, complemento, telefone, situacao, cod_categoria)
                VALUES 
                (:nis, :cpf, :nome, :cod_bairro, :localidade, :cod_usuario, :dt_cadastro,
                 :cod_unidade, :cpf_responsavel, :vch_responsavel, :cod_tipo, :cep, 
                 :endereco, :complemento, :telefone, :situacao, :cod_categoria)";
        
        $stmt = $this->db->prepare($sql);

        // ✅  Bind dos parâmetros
        $stmt->bindValue(':nis', $this->nis);
        $stmt->bindValue(':cpf', $this->cpf);
        $stmt->bindValue(':nome', $this->nome);
        $stmt->bindValue(':cod_bairro', $this->cod_bairro);
        $stmt->bindValue(':localidade', $this->localidade);
        $stmt->bindValue(':cod_usuario', $this->cod_usuario);
        $stmt->bindValue(':dt_cadastro', $this->dt_cadastro ?? date('Y-m-d H:i:s'));
        $stmt->bindValue(':cod_unidade', $this->cod_unidade);
        $stmt->bindValue(':cpf_responsavel', $this->cpf_responsavel);
        $stmt->bindValue(':vch_responsavel', $this->vch_responsavel);
        $stmt->bindValue(':cod_tipo', $this->cod_tipo);
        $stmt->bindValue(':cep', $this->cep);
        $stmt->bindValue(':endereco', $this->endereco);
        $stmt->bindValue(':complemento', $this->complemento);
        $stmt->bindValue(':telefone', $this->telefone);
        $stmt->bindValue(':situacao', $this->situacao ?? 1);
        $stmt->bindValue(':cod_categoria', $this->cod_categoria);

        // 
        error_log("[Beneficiario::inserirBeneficiario] Dados: " . json_encode([
            'nis' => $this->nis,
            'cpf' => $this->cpf,
            'nome' => $this->nome,
            'cod_bairro' => $this->cod_bairro,
            'localidade' => $this->localidade,
            'cod_usuario' => $this->cod_usuario,
            'dt_cadastro' => $this->dt_cadastro,
            'cod_unidade' => $this->cod_unidade,
            'cpf_responsavel' => $this->cpf_responsavel,
            'vch_responsavel' => $this->vch_responsavel,
            'cod_tipo' => $this->cod_tipo,
            'cep' => $this->cep,
            'endereco' => $this->endereco,
            'complemento' => $this->complemento,
            'telefone' => $this->telefone,
            'situacao' => $this->situacao,
            'cod_categoria' => $this->cod_categoria,
        ]));

        //  Executar inserção
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Falha ao inserir beneficiário. Erro PDO: " . $errorInfo[2]);
        }

        // Atualizar saldo da unidade (decrementa 1 vaga ao incluir)
        if (!empty($this->cod_unidade)) {
            $sqlSaldo = "UPDATE beneficiario.saldo_unidade SET saldo = saldo - 1 WHERE cod_unidade = :cod_unidade";
            $stmtSaldo = $this->db->prepare($sqlSaldo);
            $stmtSaldo->bindValue(':cod_unidade', $this->cod_unidade, PDO::PARAM_INT);

            if (!$stmtSaldo->execute()) {
                $errorInfo = $stmtSaldo->errorInfo();
                throw new Exception("Beneficiário inserido, mas erro ao atualizar saldo: " . $errorInfo[2]);
            }
        }

        return true;

    } catch (PDOException $e) {
        throw new Exception("Erro PDO ao inserir beneficiário: " . $e->getMessage());
    } catch (Exception $e) {
        throw $e; // repassa para ser tratado no processar_beneficiario.php
    }
}
    public function alterarBeneficiario(array $dados, int $nivelUsuario): bool
    {
        try {
            // Só define cod_categoria se usuário for nível 1
            $cod_categoria = null;
            if ($nivelUsuario == 1 && !empty($dados['cod_categoria'])) {
                $cod_categoria = (int) $dados['cod_categoria'];
            }

            $sql = "UPDATE beneficiario.beneficiario 
                       SET nis             = :nis,
                           cpf             = :cpf,
                           nome            = :nome,
                           cod_bairro      = :cod_bairro,
                           localidade      = :localidade,
                           cod_usuario     = :cod_usuario,
                           dt_cadastro     = :dt_cadastro,
                           cod_unidade     = :cod_unidade,
                           cpf_responsavel = :cpf_responsavel,
                           vch_responsavel = :vch_responsavel,
                           cod_tipo        = :cod_tipo,
                           cep             = :cep,
                           endereco        = :endereco,
                           complemento     = :complemento,
                           telefone        = :telefone,
                           cod_categoria   = :cod_categoria,
                           situacao        = :situacao
                     WHERE cod_beneficiario = :cod_beneficiario";

            $consulta = $this->db->prepare($sql);

            $consulta->bindParam(':nis',             $dados['nis']);
            $consulta->bindParam(':cpf',             $dados['cpf']);
            $consulta->bindParam(':nome',            $dados['nome']);
            $consulta->bindParam(':cod_bairro',      $dados['cod_bairro']);
            $consulta->bindParam(':localidade',      $dados['localidade']);
            $consulta->bindParam(':cod_usuario',     $dados['cod_usuario']);
            $consulta->bindParam(':dt_cadastro',     $dados['dt_cadastro']);
            $consulta->bindParam(':cod_unidade',     $dados['cod_unidade']);
            $consulta->bindParam(':cpf_responsavel', $dados['cpf_responsavel']);
            $consulta->bindParam(':vch_responsavel', $dados['vch_responsavel']);
            $consulta->bindParam(':cod_tipo',        $dados['cod_tipo']);
            $consulta->bindParam(':cep',             $dados['cep']);
            $consulta->bindParam(':endereco',        $dados['endereco']);
            $consulta->bindParam(':complemento',     $dados['complemento']);
            $consulta->bindParam(':telefone',        $dados['telefone']);
            $consulta->bindParam(':situacao',        $dados['situacao']);
            $consulta->bindParam(':cod_beneficiario', $dados['cod_beneficiario'], PDO::PARAM_INT);

            // Categoria controlada pelo nível
            if ($cod_categoria !== null) {
                $consulta->bindParam(':cod_categoria', $cod_categoria, PDO::PARAM_INT);
            } else {
                $consulta->bindValue(':cod_categoria', null, PDO::PARAM_NULL);
            }

            return $consulta->execute();
        } catch (PDOException $e) {
            error_log("[Beneficiario::alterarBeneficiario] Erro: " . $e->getMessage());
            return false;
        }
    }



// Atualiza a situação do beneficiário para 0 (fora da cesta)
public function excluirBeneficiario()
{
    $pdo = Database::conexao();
    try {
        $consulta = $pdo->prepare("
            UPDATE beneficiario.beneficiario 
            SET situacao = :situacao, cod_usuario = :cod_usuario 
            WHERE cod_beneficiario = :cod_beneficiario;
        ");
        $consulta->bindParam(':cod_beneficiario', $this->cod_beneficiario);
        $consulta->bindParam(':cod_usuario', $this->cod_usuario);
        $this->situacao = 0;
        $consulta->bindParam(':situacao', $this->situacao);

        $consulta_saldo = $pdo->prepare("
            UPDATE beneficiario.saldo_unidade 
            SET saldo = saldo + 1 
            WHERE cod_unidade = :cod_unidade;
        ");
        $consulta_saldo->bindParam(':cod_unidade', $this->cod_unidade);

        $consulta_saldo->execute();
        return $consulta->execute(); // retorna true/false
    } catch (PDOException $e) {
        return false;
    }
}

// Atualiza a situação do beneficiário para 1 (dentro da cesta)
public function incluirBeneficiario()
{
    $pdo = Database::conexao();
    try {
        // Verificar saldo antes de inserir na cesta
        if (!empty($this->cod_unidade)) {
            $ver = $pdo->prepare("SELECT saldo FROM beneficiario.saldo_unidade WHERE cod_unidade = :cod_unidade");
            $ver->bindParam(':cod_unidade', $this->cod_unidade, PDO::PARAM_INT);
            $ver->execute();
            $res = $ver->fetch(PDO::FETCH_ASSOC);
            if (!$res || (int)$res['saldo'] <= 0) {
                throw new Exception('Não há vagas disponíveis para esta unidade.');
            }
        }
        $consulta = $pdo->prepare("
            UPDATE beneficiario.beneficiario 
            SET situacao = :situacao, cod_usuario = :cod_usuario 
            WHERE cod_beneficiario = :cod_beneficiario;
        ");
        $consulta->bindParam(':cod_beneficiario', $this->cod_beneficiario);
        $consulta->bindParam(':cod_usuario', $this->cod_usuario);
        $this->situacao = 1;
        $consulta->bindParam(':situacao', $this->situacao);

        $consulta_saldo = $pdo->prepare("
            UPDATE beneficiario.saldo_unidade 
            SET saldo = saldo - 1 
            WHERE cod_unidade = :cod_unidade;
        ");
        $consulta_saldo->bindParam(':cod_unidade', $this->cod_unidade);

        $consulta_saldo->execute();
        return $consulta->execute(); // retorna true/false
    } catch (PDOException $e) {
        throw new Exception('Erro ao atualizar situação: ' . $e->getMessage());
    }
}


    public function gerarRelatorio2()
    {
        try {
            $pdo = Database::conexao();
            $sql =  "SELECT SUM(entregue_cupom), cupom, dt_entrega_cupom
            FROM beneficiario.cesta AS cp
            WHERE entregue_cupom = 1 AND CAST(dt_entrega_cupom AS date) BETWEEN CAST('01/01/2023' AS date) AND CAST('24/03/2023' AS date)
            GROUP BY cupom, dt_entrega_cupom
            order by cupom, dt_entrega_cupom";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $e) {
            die("Erro ao gerar os relatórios");
        }
    }

    public function gerarRelatorio($data_inicial, $data_final)
    {
        $data_obj = new DateTime($data_inicial);
        // Formata a data no padrão brasileiro (d/m/Y)
        $data_inicial_formatada = $data_obj->format('d/m/Y');

        $data_obj2 = new DateTime($data_final);
        // Formata a data no padrão brasileiro (d/m/Y)
        $data_final_formatada = $data_obj2->format('d/m/Y');

        try {
            $pdo = Database::conexao();

            $sql =  "SELECT SUM(entregue_cupom) AS entregue_cupom, cupom, dt_entrega_cupom
                     FROM beneficiario.cesta
                     WHERE entregue_cupom = 1 
                     AND CAST(dt_entrega_cupom AS DATE) BETWEEN CAST(:data_inicial AS DATE) AND CAST(:data_final AS DATE)
                     GROUP BY cupom, dt_entrega_cupom
                     ORDER BY cupom, dt_entrega_cupom";

            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':data_inicial', $data_inicial_formatada);
            $consulta->bindValue(':data_final', $data_final_formatada);
            $consulta->execute();

            return $consulta;
        } catch (PDOexception $e) {
            die("Erro ao gerar os relatórios");
        }
    }

    // RELATORIO
    // TOTAL DE CESTAS ENTREGUES POR DIA
    public function entregasTotalDia($data_inicial, $data_final)
    {
        $data_obj = new DateTime($data_inicial);
        // Formata a data no padrão brasileiro (d/m/Y)
        $data_inicial_formatada = $data_obj->format('d/m/Y');

        $data_obj2 = new DateTime($data_final);
        // Formata a data no padrão brasileiro (d/m/Y)
        $data_final_formatada = $data_obj2->format('d/m/Y');

        try {
            $pdo = Database::conexao();

            $sql =  "SELECT CAST(dt_entrega_cupom AS date) AS data_entrega, SUM(entregue_cupom) AS total
                     FROM beneficiario.cesta AS cp
                     WHERE entregue_cupom = 1 
                     AND CAST(dt_entrega_cupom AS date) BETWEEN CAST(:data_inicial AS date) AND CAST(:data_final AS date)
                     GROUP BY CAST(dt_entrega_cupom AS date)
                     ORDER BY CAST(dt_entrega_cupom AS date)";

            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':data_inicial', $data_inicial_formatada);
            $consulta->bindValue(':data_final', $data_final_formatada);
            $consulta->execute();

            return $consulta;
        } catch (PDOexception $e) {
            die("Erro ao gerar os relatórios");
        }
    }

    // CONSULTA QUE TRAZ A TOTALIZAÇÃO DAS CESTAS DE TODOS OS DIAS
    public function entregasTotal($data_inicial, $data_final)
    {
        $data_obj = new DateTime($data_inicial);
        // Formata a data no padrão brasileiro (d/m/Y)
        $data_inicial_formatada = $data_obj->format('d/m/Y');

        $data_obj2 = new DateTime($data_final);
        // Formata a data no padrão brasileiro (d/m/Y)
        $data_final_formatada = $data_obj2->format('d/m/Y');

        try {
            $pdo = Database::conexao();

            $sql =  "SELECT SUM(entregue_cupom) AS total_entregues
                     FROM beneficiario.cesta AS cp
                     WHERE entregue_cupom = 1 
                     AND CAST(dt_entrega_cupom AS date) BETWEEN CAST(:data_inicial AS date) AND CAST(:data_final AS date)";

            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':data_inicial', $data_inicial_formatada);
            $consulta->bindValue(':data_final', $data_final_formatada);
            $consulta->execute();

            return $consulta;
        } catch (PDOexception $e) {
            die("Erro ao gerar os relatórios");
        }
    }

    public function diasEntregues()
    {
        try {
            $pdo = Database::conexao();

            $sql =  "SELECT DISTINCT cp.dt_entrega_cupom
                         FROM beneficiario.cesta AS cp
                         ORDER BY cp.dt_entrega_cupom ASC";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $e) {
            die("Erro ao gerar os relatórios");
        }
    }

    public function locaisEntregues()
    {
        try {
            $pdo = Database::conexao();

            $sql =  "SELECT DISTINCT cp.cupom
                         FROM beneficiario.cesta AS cp
                         ORDER BY cp.cupom ASC";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $e) {
            die("Erro ao gerar os relatórios");
        }
    }

    public function exibirBeneficiarioPesquisa($cod_unidade, $int_nivel, $where)
    {
        try {
            $pdo = Database::conexao();

            $sql_base = "SELECT b.cod_beneficiario, b.nis, b.cpf, b.nome, b.cod_bairro, ba.vch_bairro, 
                            b.localidade, b.endereco, b.telefone, b.cod_tipo, tb.vch_tipo, 
                            b.cod_usuario, b.situacao,
                            c.vch_categoria AS categoria
                       FROM beneficiario.beneficiario b
                 INNER JOIN beneficiario.bairro ba ON b.cod_bairro = ba.cod_bairro
                 INNER JOIN beneficiario.tipo_beneficiario tb ON b.cod_tipo = tb.cod_tipo
                  LEFT JOIN beneficiario.categoria c ON b.cod_categoria = c.cod_categoria
                      WHERE ";

            if ($int_nivel == "1") {
                $sql = $sql_base . $where . " 
                   AND situacao < 2 
                   AND NOT EXISTS (
                       SELECT 1 FROM beneficiario.folha f WHERE f.cpf = b.cpf
                   ) 
                   AND NOT EXISTS (
                       SELECT 1 FROM beneficiario.folha f 
                        WHERE f.nis = b.nis AND f.nis IS NOT NULL
                   )
                   ORDER BY situacao ASC, nome ASC 
                   LIMIT :limite OFFSET :inicio";
            } else {
                $sql = $sql_base . $where . " 
                   AND b.cod_unidade = :cod_unidade 
                   AND situacao < 2 
                   AND NOT EXISTS (
                       SELECT 1 FROM beneficiario.folha f WHERE f.cpf = b.cpf
                   ) 
                   AND NOT EXISTS (
                       SELECT 1 FROM beneficiario.folha f 
                        WHERE f.nis = b.nis AND f.nis IS NOT NULL
                   )
                   ORDER BY situacao ASC, nome ASC 
                   LIMIT :limite OFFSET :inicio";
            }

            $consulta = $pdo->prepare($sql);
            $consulta->bindParam(':limite', $this->limite, PDO::PARAM_INT);
            $consulta->bindParam(':inicio', $this->inicio, PDO::PARAM_INT);

            if ($int_nivel != "1") {
                $consulta->bindParam(':cod_unidade', $cod_unidade, PDO::PARAM_INT);
            }

            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function exibirBeneficiario(int $cod_unidade, int $int_nivel, int $page = 1, int $perPage = 50): array
    {
        try {
            $pdo    = Database::conexao();
            $offset = ($page - 1) * $perPage;

            // Base SELECT (com categoria) com anti-join na folha para melhor performance
            $select = "SELECT b.cod_beneficiario, b.nis, b.cpf, b.nome, b.cod_bairro, ba.vch_bairro, 
                          b.localidade, b.endereco, b.telefone, b.cod_tipo, tb.vch_tipo, 
                          b.cod_usuario, b.situacao,
                          c.vch_categoria AS categoria
                     FROM beneficiario.beneficiario b
               INNER JOIN beneficiario.bairro ba ON b.cod_bairro = ba.cod_bairro
               INNER JOIN beneficiario.tipo_beneficiario tb ON b.cod_tipo = tb.cod_tipo
                LEFT JOIN beneficiario.categoria c ON b.cod_categoria = c.cod_categoria
                LEFT JOIN beneficiario.folha f ON f.cpf = b.cpf
                    WHERE (situacao = 0 OR situacao = 1)
                      AND f.cpf IS NULL";

            // Base COUNT (sem joins desnecessários) com anti-join
            $count = "SELECT COUNT(*) 
                    FROM beneficiario.beneficiario b
               LEFT JOIN beneficiario.folha f ON f.cpf = b.cpf
                   WHERE (situacao = 0 OR situacao = 1)
                     AND f.cpf IS NULL";

            // Restrição por unidade se não for nível 1
            $params = [];
            if ($int_nivel != 1 && $cod_unidade > 0) {
                $select .= " AND b.cod_unidade = :cod_unidade";
                $count  .= " AND b.cod_unidade = :cod_unidade";
                $params[':cod_unidade'] = $cod_unidade;
            }

            // Finaliza SELECT com ordenação e paginação
            $select .= " ORDER BY situacao ASC, nome ASC LIMIT :limite OFFSET :inicio";

            // Executa COUNT
            $stmtCount = $pdo->prepare($count);
            if (isset($params[':cod_unidade'])) {
                $stmtCount->bindValue(':cod_unidade', $params[':cod_unidade'], PDO::PARAM_INT);
            }
            $stmtCount->execute();
            $total = (int) $stmtCount->fetchColumn();

            // Executa SELECT paginado
            $stmt = $pdo->prepare($select);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limite', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':inicio', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data'     => $data,
                'total'    => $total,
                'page'     => $page,
                'perPage'  => $perPage
            ];
        } catch (PDOException $e) {
            error_log("Erro ao retornar beneficiários: " . $e->getMessage());
            return [
                'data'     => [],
                'total'    => 0,
                'page'     => $page,
                'perPage'  => $perPage,
                'error'    => $e->getMessage()
            ];
        }
    }


    public function buscarUnid()
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT cod_unidade, vch_unidade FROM beneficiario.unidade where cod_unidade in (1, 2, 4, 5, 6, 7, 55)";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function saldoUnidade($cod_unidade)
    {
        $pdo = Database::conexao();
        try {
            $sql = "SELECT saldo FROM beneficiario.saldo_unidade where cod_unidade = $cod_unidade ";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }


    public function retornaAtualizados($cod_unidade)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT count(1) as total
                     FROM beneficiario.beneficiario
                     WHERE cod_unidade = $cod_unidade and situacao = 1 ";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function totalRegistros($cod_unidade)
    {
        $pdo = Database::conexao();
        $sql = "SELECT count(1) as total
                 FROM beneficiario.beneficiario 
                 WHERE cod_unidade = $cod_unidade AND (situacao = 0 or situacao = 1) ";
        $consulta = $pdo->prepare($sql);
        $consulta->execute();
        return $consulta;
    }


    public function verificaNisBeneficiarios($nis)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.beneficiario 
                     WHERE nis = $nis ";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function verificaCpfBeneficiario($cpf)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.beneficiario 
                     WHERE cpf = '$cpf' ";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function verificaCpfBeneficiariosResponsavel($cpf)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.beneficiario 
                     WHERE cpf = $cpf ";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function verificaFamiliaFolhaNis($nis)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT c.nis
                     FROM beneficiario.cad c 
                     WHERE c.nis = $nis 
                     and exists (select f.nis 
                                from beneficiario.folha f inner join beneficiario.cad ca on f.nis = ca.nis
                                where ca.cod_fam = c.cod_fam ) ";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function verificaFamiliaFolhaCpf($cpf)
    {
        try {
            $pdo = Database::conexao();
            $sql = "select c.cpf
                 from beneficiario.cad c 
                 where c.cpf = '$cpf' 
                 and exists (select f.cpf 
                                 from beneficiario.folha f inner join beneficiario.cad ca on f.cpf = CAST(ca.cpf AS bigint)
                                 where ca.cod_fam = c.cod_fam ) ";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function verificaFamiliaFolhaCpfResponsavel($cpf)
    {
        try {
            $pdo = Database::conexao();
            $sql = "select c.cpf
                 from beneficiario.cad c 
                 where c.cpf = $cpf 
                 and exists (select f.cpf 
                                 from beneficiario.folha f inner join beneficiario.cad ca on f.cpf = ca.cpf
                                 where ca.cod_fam = c.cod_fam ) ";

            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function verificaNisFolha($nis)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.folha where nis = $nis ";

            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }


    public function verificaCpfBeneficiarios($cpf)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.beneficiario where cpf = $cpf ";

            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function verificaCpfFolha($cpf)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.folha where cpf = '$cpf' ";

            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function verificaCpfFolhaResponsavel($cpf)
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.folha where cpf = $cpf ";

            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
    }

    public function exibirBairro()
    {
        $pdo = Database::conexao();
        $sql = "SELECT cod_bairro, vch_bairro FROM beneficiario.bairro order by vch_bairro";
        $consulta = $pdo->prepare($sql);
        $consulta->execute();
        return $consulta;
    }

    public function exibirTipo()
    {
        $pdo = Database::conexao();
        $sql = "SELECT cod_tipo, vch_tipo FROM beneficiario.tipo_beneficiario order by vch_tipo";
        $consulta = $pdo->prepare($sql);
        $consulta->execute();
        return $consulta;
    }

    public function exibirBeneficiarioCod($cod)
    {
        $pdo = Database::conexao();
        $sql = "SELECT * FROM beneficiario.beneficiario b 
        where cod_beneficiario = '$cod'";
        $consulta = $pdo->prepare($sql);
        $consulta->execute();
        return $consulta;
    }

    public function localizarbeneficiario($nome)
    {
        $pdo = Database::conexao();
        $sql = "SELECT * FROM beneficiario.beneficiario b where b.nome like '%$nome%'";
        $consulta = $pdo->prepare($sql);
        $consulta->execute();
        return $consulta;
    }

    public function exibir_beneficiario_pag()
    {
        $pdo = Database::conexao();
        $sql = "SELECT * FROM beneficiario.beneficiario b order by nome DESC LIMIT " . $this->inicio . "," . $this->limite;
        try {
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
        }
        return $consulta;
    }
    
}
