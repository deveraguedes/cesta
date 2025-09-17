  <?php
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
          if ($this->existsUnidade()) {
              throw new InvalidArgumentException("unidade_duplicada");
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

      private function existsUnidade(): bool
      {
          $stmt = $this->pdo->prepare(
            "SELECT 1
              FROM beneficiario.usuario
              WHERE cod_unidade = :unidade"
          );
          $stmt->execute([':unidade' => $this->codUnidade]);
          return (bool) $stmt->fetchColumn();
      }

      private function generateSenhaHash(string $password): string
      {
          $uni = iconv('UTF-8','UTF-16LE',$password);
          return strtoupper(bin2hex(hash('md5',$uni,true)));
      }

      /**
       * Expondo o código da unidade para o handler de erros
       */
      public function getCodUnidade(): int
      {
          return $this->codUnidade;
      }
  }
    