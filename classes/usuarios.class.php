<?php

include_once('conexao.class.php');

class Usuarios {

    public $cod_usuario;
    public $vch_nome;
    public $vch_login;
    public $vch_senha;
    public $int_nivel;
    public $vch_email;
    public $date_cadastro;
    public $date_alteracao;
    public $cod_categoria_usuario;
    public $vch_status;
    public $cod_funcionario;
    public $cod_unidade;

    public $token;

    public function setCod_usuario($cod_usuario) {
        $this->cod_usuario = $cod_usuario;
    }

    public function setVch_nome($vch_nome) {
        $this->vch_nome = $vch_nome;
    }

    public function setVch_login($vch_login) {
        $this->vch_login = $vch_login;
    }

    public function setVch_senha($vch_senha) {
        $this->vch_senha = $vch_senha;
    }

    public function setInt_nivel($int_nivel) {
        $this->int_nivel = $int_nivel;
    }

    public function setDate_cadastro($data_cadastro) {
        $this->date_cadastro = $data_cadastro;
    }

    public function setCod_unidade($cod_unidade) {
        $this->cod_unidade = $cod_unidade;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function getCod_usuario() {
        return $this->cod_usuario;
    }

    public function getVch_nome() {
        return $this->vch_nome;
    }

    public function getVch_login() {
        return $this->vch_login;
    }

    public function getVch_senha() {
        return $this->vch_senha;
    }

    public function getInt_nivel() {
        return $this->int_nivel;
    }

    public function getVch_email() {
        return $this->vch_email;
    }

    public function getDate_cadastro() {
        return $this->date_cadastro;
    }

    public function getDate_alteracao() {
        return $this->date_alteracao;
    }

    public function getCod_categoria_usuario() {
        return $this->cod_categoria_usuario;
    }

    public function getVch_status() {
        return $this->vch_status;
    }

    public function getCod_funcionario() {
        return $this->cod_funcionario;
    }

    public function getCod_empresa() {
        return $this->cod_unidade;
    }

    public function getToken() {
        return $this->token;
    }

    /*
    public function inserirUsuarios() {
        try {
            $pdo = Database::conexao();

            $consulta = $pdo->prepare("INSERT into beneficiario.usuario( vch_nome, vch_login, cod_unidade, vch_senha, int_nivel, data_cadastro)
                   values ( :vch_nome, :vch_login, :cod_unidade, :vch_senha, :int_nivel, :data_cadastro);");

            //$consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':vch_nome', $this->vch_nome);
            $consulta->bindParam(':vch_login', $this->vch_login);
            $consulta->bindParam(':vch_senha', $this->vch_senha);
            $consulta->bindParam(':int_nivel', $this->int_nivel);
            $consulta->bindParam(':cod_unidade', $this->cod_unidade);
            $consulta->bindParam(':data_cadastro', $this->data_cadastro);

            $consulta->execute();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }
*/

    public function inserirUsuarios() {
        try {
            $pdo = Database::conexao();
            $pdo->beginTransaction();
            $this->vch_login = $this->gerar_login();
            $vch_senha_plain = $this->gerar_senha();
            $resultado = $this->consultarUsuarioLogin($this->vch_login);

            while(count($resultado) != 0){
                $this->vch_login= $this->gerar_login();
                $vch_senha_plain = $this->gerar_senha();
                $resultado = $this->consultarUsuarioLogin($this->vch_login);
            }
            $this->vch_senha = md5($vch_senha_plain);
            $consulta = $pdo->prepare("INSERT into beneficiario.usuario( vch_nome, vch_login, cod_unidade, vch_senha, int_nivel, data_cadastro)
                   values ( :vch_nome, :vch_login, :cod_unidade, :vch_senha, :int_nivel, :data_cadastro);");
            //$consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':vch_nome', $this->vch_nome);
            $consulta->bindParam(':vch_login', $this->vch_login);
            $consulta->bindParam(':vch_senha', $this->vch_senha);
            $consulta->bindParam(':int_nivel', $this->int_nivel);
            $consulta->bindParam(':cod_unidade', $this->cod_unidade);
            $consulta->bindParam(':data_cadastro', $this->date_cadastro);
            $consulta->execute();
            
            $consulta = $pdo->prepare("INSERT into beneficiario.usuario_temporario (vch_senha, cod_usuario)
            values(:vch_senha_plain, :cod_usuario)");
    
            $this->cod_usuario = $pdo->lastInsertId();
            $consulta->bindParam(':vch_senha_plain', $vch_senha_plain);
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->execute();
            $pdo->commit();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            die(var_dump($e));
            echo "Ocorreu um erro: $e";
            $pdo->rollBack();
        }
    }



    public function alterarUsuarios() {
        $pdo = Database::conexao();
        try {
            $consulta = $pdo->prepare("UPDATE beneficiario.usuario SET vch_nome = :vch_nome, vch_login = :vch_login, 
              vch_senha = :vch_senha, int_nivel = :int_nivel WHERE cod_usuario = :cod_usuario;");
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);
            $consulta->bindParam(':vch_nome', $this->vch_nome);
            $consulta->bindParam(':vch_login', $this->vch_login);
            $consulta->bindParam(':vch_senha', $this->vch_senha);
            $consulta->bindParam(':int_nivel', $this->int_nivel);

            $consulta->execute();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            echo "Ocorreu um erro: $e";
        }
    }

    public function excluirUsuarios() {
        $pdo = Database::conexao();
        try {
            $consulta = $pdo->prepare("DELETE FROM beneficiario.usuario WHERE cod_usuario = :cod_usuario;");
            $consulta->bindParam(':cod_usuario', $this->cod_usuario);


            $consulta->execute();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            throw new Exception("Ocorreu um erro: $e");
       }
    }

    public function deletarUsuarios($cod_usuario) {
        $pdo = Database::conexao();
        try {
            $consulta = $pdo->prepare("DELETE FROM beneficiario.usuario WHERE cod_usuario = :cod_usuario;");
            $consulta->bindParam(':cod_usuario', $cod_usuario);


            $consulta->execute();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            throw new Exception("Ocorreu um erro: $e");
        }
    }



    public function exibirUsuarios($cod_unidade = null, $int_nivel = null) {
        $pdo = Database::conexao();
        
        // SQL base
        $sql = "SELECT u.*, un.vch_unidade FROM beneficiario.usuario u 
                LEFT JOIN beneficiario.unidade un ON u.cod_unidade = un.cod_unidade";
        
        // Adiciona filtro por unidade se o usuário for nível 2
        if ($int_nivel == 2 && $cod_unidade) {
            $sql .= " WHERE u.cod_unidade = :cod_unidade";
        }
        
        $sql .= " ORDER BY u.cod_usuario DESC";
        
        $consulta = $pdo->prepare($sql);
        
        // Bind do parâmetro se necessário
        if ($int_nivel == 2 && $cod_unidade) {
            $consulta->bindParam(':cod_unidade', $cod_unidade, PDO::PARAM_INT);
        }
        
        $consulta->execute();
        return $consulta;
    }

    public function exibirUsuarioCod($cod) {
        try {
            $pdo = Database::conexao();
            $sql = "SELECT * FROM beneficiario.usuario where cod_usuario = $cod";
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOException $e) {
            throw new Exception("Ocorreu um erro: $e");
        }
    }

    public function localizarUsuarios($nome) {
        $pdo = Database::conexao();
        $sql = "SELECT * FROM beneficiario.usuario where  vch_nome like '%$nome%'";
        $consulta = $pdo->prepare($sql);
        $consulta->execute();
        return $consulta;
    }

    public function consultarUsuarioLogin($login){
        try{
            $pdo = Database::conexao();
            $sql="SELECT * FROM beneficiario.usuario where vch_login like '$login'";
            $consulta = $pdo->query($sql)->fetchAll();
            return $consulta;
        }catch(PDOException $e){
            throw new Exception("Ocorreu um erro: $e");
        }
    }



    public function gerar_login(){
        $letras = "abcdefghijklmnopqrstuvwxyz";
        $login = "";
          for($i = 0; $i<4; $i++){
            $login .= $letras[random_int(0,25)]; 
          }
           return $login;
        }
    
      public function gerar_senha(){
        $numeros = "123456789";
        $senha = "";
          for($i = 0; $i<4; $i++){
            $senha .=  $numeros[random_int(0,8)]; 
          }
          return $senha;
        }



        public function dadosTemporarios(){
            $pdo = Database::conexao();
            $sql = "SELECT usuario.vch_nome, usuario.vch_login, usuario_temporario.vch_senha, unidade.vch_unidade, usuario.cod_usuario
                        FROM beneficiario.usuario
                        INNER JOIN beneficiario.usuario_temporario
                        on usuario_temporario.cod_usuario = usuario.cod_usuario
                        INNER JOIN beneficiario.unidade
                        on unidade.cod_unidade = usuario.cod_unidade
                        order by usuario.vch_nome";
           
            $consulta = $pdo->prepare($sql);
            $consulta->execute();
            return $consulta;
        }
        
  

}

include_once __DIR__ . '/conexao.class.php';

class Usuario
{
    private PDO    $pdo;
    private string $vchName;
    private string $vchLogin;
    private string $vchSenha;
    private int $codUnidade = 0;
    private int    $intNivel     = 2;
    private string $dataCadastro;

    public function __construct()
    {
        $this->pdo = Database::conexao();
    }

    /**
     * Valida e popula propriedades a partir de $_POST
     *
     * @throws InvalidArgumentException
     */
    public function setData(array $data): void
    {
        $required = [
            'vch_nome',
            'usuario',
            'cod_unidade',
            'password',
            'password2',
            'data_cadastro'
        ];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                throw new InvalidArgumentException("campo_obrigatorio");
            }
        }
        if ($data['password'] !== $data['password2']) {
            throw new InvalidArgumentException("senha_mismatch");
        }

        $this->vchName      = trim($data['vch_nome']);
        $this->vchLogin     = trim($data['usuario']);
        $this->codUnidade   = (int) $data['cod_unidade'];
        $this->dataCadastro = $data['data_cadastro'];
        $this->vchSenha     = $this->generateSenhaHash($data['password']);

        // Set intNivel from form if present, else default to 2
        // Allow levels: 1 (admin), 2 (usuário), 3 (sedes)
        if (isset($data['int_nivel']) && in_array((int)$data['int_nivel'], [1,2,3], true)) {
            $this->intNivel = (int)$data['int_nivel'];
        } else {
            $this->intNivel = 2;
        }
    }

    /**
     * Insere no banco, lançando se já existir login/nome/unidade
     *
     * @return int último ID inserido
     * @throws InvalidArgumentException
     * @throws PDOException
     */
    public function cadastrar(): int
    {
        if ($this->existsLogin()) {
            throw new InvalidArgumentException("login_duplicado");
        }
        if ($this->existsName()) {
            throw new InvalidArgumentException("nome_duplicado");
        }

        $sql = "
            INSERT INTO beneficiario.usuario
              (vch_nome, vch_login, vch_senha,
              cod_unidade, int_nivel, data_cadastro)
            VALUES
              (:name, :login, :senha,
              :unidade, :nivel, :cadastro)
          ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name'     => $this->vchName,
            ':login'    => $this->vchLogin,
            ':senha'    => $this->vchSenha,
            ':unidade'  => $this->codUnidade,
            ':nivel'    => $this->intNivel,
            ':cadastro' => $this->dataCadastro,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function existsLogin(): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1
              FROM beneficiario.usuario
              WHERE vch_login = :login"
        );
        $stmt->execute([':login' => $this->vchLogin]);
        return (bool) $stmt->fetchColumn();
    }

    private function existsName(): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1
              FROM beneficiario.usuario
              WHERE vch_nome = :name"
        );
        $stmt->execute([':name' => $this->vchName]);
        return (bool) $stmt->fetchColumn();
    }

    private function generateSenhaHash(string $password): string
    {
        $uni = iconv('UTF-8', 'UTF-16LE', $password);
        return strtoupper(bin2hex(hash('md5', $uni, true)));
    }

    /**
     * Expondo o código da unidade para o handler de erros
     */
    public function getCodUnidade(): int
    {
        return $this->codUnidade;
    }

    public function listarPaginada(int $codUnidade = 0, int $page = 1, int $perPage = 6, int $nivelUsuario = 1): array
    {
        // Se for nível 2 e não tiver unidade especificada, não deve mostrar nada
        if ($nivelUsuario == 2 && $codUnidade <= 0) {
            return [
                'data' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage
            ];
        }
        
        // 1) Conta total de registros
        $countSql = "SELECT COUNT(*) FROM beneficiario.usuario"
            . ($codUnidade > 0 ? " WHERE cod_unidade = :un" : "");
        $stmt = $this->pdo->prepare($countSql);
        if ($codUnidade > 0) {
            $stmt->bindValue(':un', $codUnidade, PDO::PARAM_INT);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        // 2) Seleciona página com ORDER BY multi‐formato
        $offset = ($page - 1) * $perPage;
        $dataSql = "
        SELECT u.cod_usuario
             , u.vch_nome
             , u.vch_login
             , u.cod_unidade
             , un.vch_unidade AS nome_unidade
             , u.data_cadastro
             , u.int_nivel
          FROM beneficiario.usuario u
          LEFT JOIN beneficiario.unidade un ON u.cod_unidade = un.cod_unidade"
            . ($codUnidade > 0 ? " WHERE u.cod_unidade = :un" : "")
            . " ORDER BY
            CASE
              WHEN data_cadastro ~ '^[0-3][0-9][-/][0-1][0-9][-/][0-9]{4}$'
                THEN to_date(replace(data_cadastro, '/', '-'), 'DD-MM-YYYY')
              WHEN data_cadastro ~ '^[0-9]{8}$'
                THEN to_date(data_cadastro, 'DDMMYYYY')
              ELSE
                NULL
            END DESC
          LIMIT :lim OFFSET :off
    ";
        $stmt = $this->pdo->prepare($dataSql);
        if ($codUnidade > 0) {
            $stmt->bindValue(':un', $codUnidade, PDO::PARAM_INT);
        }
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data'     => $data,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }


    public function deletar(int $codUsuario): bool
    {
        $sql = "
            DELETE
              FROM beneficiario.usuario
             WHERE cod_usuario = :cod
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cod', $codUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function atualizar(int $codUsuario, array $data): bool
    {
        // 1) Valida campos obrigatórios
        foreach (['vch_nome', 'vch_login', 'cod_unidade'] as $f) {
            if (empty($data[$f])) {
                throw new InvalidArgumentException("campo_obrigatorio: $f");
            }
        }

        // 2) Garante que o login não esteja duplicado em outro registro
        $dup = $this->pdo->prepare("
            SELECT 1
              FROM beneficiario.usuario
             WHERE vch_login   = :login
               AND cod_usuario <> :cod
             LIMIT 1
        ");
        $dup->execute([
            ':login' => trim($data['vch_login']),
            ':cod'   => $codUsuario
        ]);
        if ($dup->fetchColumn()) {
            throw new InvalidArgumentException("login_duplicado");
        }

        // 3) Mesma checagem para nome
        $dup = $this->pdo->prepare("
            SELECT 1
              FROM beneficiario.usuario
             WHERE vch_nome    = :nome
               AND cod_usuario <> :cod
             LIMIT 1
        ");
        $dup->execute([
            ':nome' => trim($data['vch_nome']),
            ':cod'  => $codUsuario
        ]);
        if ($dup->fetchColumn()) {
            throw new InvalidArgumentException("nome_duplicado");
        }

        // 4) Permitir múltiplos usuários por unidade: nenhuma checagem

        // 5) Monta lista de campos a atualizar
        $fields = [
            'vch_nome'    => trim($data['vch_nome']),
            'vch_login'   => trim($data['vch_login']),
            'cod_unidade' => (int)$data['cod_unidade']
        ];

        // 6) Se veio senha, gera hash e adiciona ao UPDATE
        if (!empty($data['vch_senha'])) {
            $fields['vch_senha'] = $this->generateSenhaHash($data['vch_senha']);
        }

        // allow updating access level if provided and valid
        if (isset($data['int_nivel']) && in_array((int)$data['int_nivel'], [1,2,3], true)) {
            $fields['int_nivel'] = (int)$data['int_nivel'];
        }

        // 7) Constrói dinamicamente o SET
        $setParts = [];
        foreach ($fields as $col => $val) {
            $setParts[] = "$col = :$col";
        }
        $sql = "
            UPDATE beneficiario.usuario
               SET " . implode(', ', $setParts) . "
             WHERE cod_usuario = :cod
        ";

        // 8) Executa o UPDATE
        $stmt = $this->pdo->prepare($sql);
        foreach ($fields as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        $stmt->bindValue(':cod', $codUsuario, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
