<?php
include_once('conexao.class.php');
include_once "classes/categoria.class.php";

class Beneficiario
{
   
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

    public function inserirBeneficiario()
    {
        try {
            $pdo = Database::conexao();

            $this->dt_cadastro = date("d/m/Y");

            $consulta = $pdo->prepare("INSERT into beneficiario.beneficiario( nis, cpf, nome, cod_bairro, localidade, cod_usuario, dt_cadastro, cod_unidade, 
                                cpf_responsavel, vch_responsavel, cod_tipo, cep, endereco, complemento, telefone, situacao) 
                                values ( :nis, :cpf, :nome, :cod_bairro, :localidade, :cod_usuario, 
                                :dt_cadastro, :cod_unidade, :cpf_responsavel, :vch_responsavel, :cod_tipo, :cep, :endereco, :complemento, :telefone, :situacao) ;");

            if ($this->nis == "") {
                $this->nis = null;
            }
            $consulta->bindParam(':nis', $this->nis);

            if ($this->cpf == "") {
                $this->cpf = null;
            }
            $consulta->bindParam(':cpf', $this->cpf);
            $consulta->bindParam(':nome', $this->nome);
            $consulta->bindParam(':cod_bairro', $this->cod_bairro);
            $consulta->bindParam(':localidade', $this->localidade);
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':dt_cadastro', $this->dt_cadastro);
            $consulta->bindParam(':cod_unidade', $this->cod_unidade);
            $consulta->bindParam(':cpf_responsavel', $this->cpf_responsavel);
            $consulta->bindParam(':vch_responsavel', $this->vch_responsavel);
            $consulta->bindParam(':cod_tipo', $this->cod_tipo);
            $consulta->bindParam(':cep', $this->cep);
            $consulta->bindParam(':endereco', $this->endereco);
            $consulta->bindParam(':complemento', $this->complemento);
            $consulta->bindParam(':telefone', $this->telefone);
            $consulta->bindParam(':situacao', $this->situacao);
            $consulta->execute();

            $consulta_saldo = $pdo->prepare("update beneficiario.saldo_unidade set saldo = saldo - 1 WHERE cod_unidade = :cod_unidade;");
            $consulta_saldo->bindParam(':cod_unidade', $this->cod_unidade);

            $consulta_saldo->execute();


            header('Location: ../beneficiario.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }

    public function alterarBeneficiario()
    {
        $pdo = Database::conexao();

        try {
            $consulta = $pdo->prepare("UPDATE  beneficiario.beneficiario SET nis = :nis, 
                                 cpf = :cpf, nome = :nome, cod_bairro = :cod_bairro, localidade = :localidade, cod_usuario = :cod_usuario, 
                                 dt_cadastro = :dt_cadastro, cod_unidade = :cod_unidade, cpf_responsavel = :cpf_responsavel, 
                                 vch_responsavel = :vch_responsavel, cod_tipo = :cod_tipo, cep = :cep, endereco = :endereco, 
                                 complemento = :complemento, telefone = :telefone, situacao = :situacao WHERE cod_beneficiario = :cod_beneficiario;");
            $consulta->bindParam(':cod_beneficiario', $this->cod_beneficiario);

            if ($this->nis == "") {
                $this->nis = null;
            }
            $consulta->bindParam(':nis', $this->nis);

            if ($this->cpf == "") {
                $this->cpf = null;
            }
            $consulta->bindParam(':cpf', $this->cpf);
            $consulta->bindParam(':nome', $this->nome);
            $consulta->bindParam(':cod_bairro', $this->cod_bairro);
            $consulta->bindParam(':localidade', $this->localidade);
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':dt_cadastro', $this->dt_cadastro);
            $consulta->bindParam(':cod_unidade', $this->cod_unidade);
            $consulta->bindParam(':cpf_responsavel', $this->cpf_responsavel);
            $consulta->bindParam(':vch_responsavel', $this->vch_responsavel);
            $consulta->bindParam(':cod_tipo', $this->cod_tipo);
            $consulta->bindParam(':cep', $this->cep);
            $consulta->bindParam(':endereco', $this->endereco);
            $consulta->bindParam(':complemento', $this->complemento);
            $consulta->bindParam(':telefone', $this->telefone);
            $consulta->bindParam(':situacao', $this->situacao);

            $consulta->execute();
            header('Location: ../beneficiario.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }


    //atualiza a situacao do beneficiario na cesta para 0, ou seja, ele est excluido da cesta
   public function excluirBeneficiario()
{
    $pdo = Database::conexao();
    try {
        $consulta = $pdo->prepare("UPDATE beneficiario.beneficiario 
            SET situacao = :situacao, cod_usuario = :cod_usuario 
            WHERE cod_beneficiario = :cod_beneficiario;");
        $consulta->bindParam(':cod_beneficiario', $this->cod_beneficiario);
        $consulta->bindParam(':cod_usuario', $this->cod_usuario);
        $consulta->bindParam(':situacao', $this->situacao);

        $consulta_saldo = $pdo->prepare("UPDATE beneficiario.saldo_unidade 
            SET saldo = saldo + 1 WHERE cod_unidade = :cod_unidade;");
        $consulta_saldo->bindParam(':cod_unidade', $this->cod_unidade);

        $consulta_saldo->execute();
        $consulta->execute();

        return true;  
    } catch (PDOException $e) {
        return false; 
    }
}

    //atualiza a situacao do beneficiario na cesta para 1, ou seja, ele est incluido
    public function incluirBeneficiario()
    {
        $pdo = Database::conexao();
        try {

            $consulta = $pdo->prepare("update beneficiario.beneficiario set situacao = :situacao, cod_usuario= :cod_usuario WHERE cod_beneficiario = :cod_beneficiario;");
            $consulta->bindParam(':cod_beneficiario', $this->cod_beneficiario);
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':situacao', $this->situacao);

            $consulta_saldo = $pdo->prepare("update beneficiario.saldo_unidade set saldo = saldo - 1 WHERE cod_unidade = :cod_unidade;");
            $consulta_saldo->bindParam(':cod_unidade', $this->cod_unidade);

            $consulta_saldo->execute();


            $consulta->execute();
            header('Location: ../beneficiario.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
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
            
            $sql_base = "SELECT cod_beneficiario, nis, cpf, nome, b.cod_bairro, vch_bairro, localidade, endereco, 
                            telefone, b.cod_tipo, vch_tipo, cod_usuario, situacao 
                        FROM beneficiario.beneficiario b
                        INNER JOIN beneficiario.bairro ba ON b.cod_bairro = ba.cod_bairro
                        INNER JOIN beneficiario.tipo_beneficiario tb ON b.cod_tipo = tb.cod_tipo
                        WHERE ";
            
            if ($int_nivel == "1") {
                $sql = $sql_base . $where . " AND situacao < 2 AND b.cpf NOT IN (SELECT f.cpf FROM beneficiario.folha f WHERE f.cpf = b.cpf) AND b.nis NOT IN (SELECT f.nis FROM beneficiario.folha f WHERE f.nis = b.nis AND f.nis IS NOT NULL) ORDER BY situacao ASC, nome ASC LIMIT :limite OFFSET :inicio";
            } else {
                $sql = $sql_base . $where . " AND b.cod_unidade = :cod_unidade AND situacao < 2 AND b.cpf NOT IN (SELECT f.cpf FROM beneficiario.folha f WHERE f.cpf = b.cpf) AND b.nis NOT IN (SELECT f.nis FROM beneficiario.folha f WHERE f.nis = b.nis AND f.nis IS NOT NULL) ORDER BY situacao ASC, nome ASC LIMIT :limite OFFSET :inicio";
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

    public function exibirBeneficiario($cod_unidade, $int_nivel)
    {
        try {
            $pdo = Database::conexao();

            if ($int_nivel == "1") {
                $sql = "SELECT cod_beneficiario, nis, cpf, nome, b.cod_bairro, vch_bairro, localidade, endereco, 
                            telefone, b.cod_tipo, vch_tipo, cod_usuario, situacao 
                        FROM beneficiario.beneficiario b
                        INNER JOIN beneficiario.bairro ba ON b.cod_bairro = ba.cod_bairro
                        INNER JOIN beneficiario.tipo_beneficiario tb ON b.cod_tipo = tb.cod_tipo
                        WHERE (situacao = 0 OR situacao = 1) 
                        AND b.cpf NOT IN (SELECT f.cpf FROM beneficiario.folha f WHERE f.cpf = b.cpf)
                        ORDER BY situacao ASC, nome ASC 
                        LIMIT :limite OFFSET :inicio"; 
            } else {
                $sql = "SELECT cod_beneficiario, nis, cpf, nome, b.cod_bairro, vch_bairro, localidade, endereco, 
                            telefone, b.cod_tipo, vch_tipo, cod_usuario, situacao 
                        FROM beneficiario.beneficiario b
                        INNER JOIN beneficiario.bairro ba ON b.cod_bairro = ba.cod_bairro
                        INNER JOIN beneficiario.tipo_beneficiario tb ON b.cod_tipo = tb.cod_tipo
                        WHERE b.cod_unidade = :cod_unidade AND (situacao = 0 OR situacao = 1) 
                        AND b.cpf NOT IN (SELECT f.cpf FROM beneficiario.folha f WHERE f.cpf = b.cpf)
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


    public function exibirbeneficiarioOrder()
    {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT p.*, op.* FROM beneficiario.beneficiario p 
                        inner join vch_responsavel op on p.vch_responsavel=op.vch_responsavel 
                        order by nome DESC";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOexception $error_sql) {
            echo 'Erro ao retornar os Dados.' . $error_sql->getMessage();
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
                                from folha f inner join beneficiario.cad ca on f.nis = ca.nis
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
                                 from folha f inner join beneficiario.cad ca on f.cpf = ca.cpf
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