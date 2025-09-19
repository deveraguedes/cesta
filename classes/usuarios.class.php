
<?php
// Block direct access to this class file
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    exit('Acesso negado.');
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

        public function listarPaginada(int $codUnidade = 0, int $page = 1, int $perPage = 6): array
        {
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
        SELECT cod_usuario
             , vch_nome
             , vch_login
             , cod_unidade
             , data_cadastro
          FROM beneficiario.usuario"
                . ($codUnidade > 0 ? " WHERE cod_unidade = :un" : "")
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
