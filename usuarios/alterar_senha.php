<?php
session_start();

include_once "../classes/usuarios.class.php";
include_once "../classes/login.class.php";
include_once "../baseurl.php";

$l = new LoginUsuario();
if (!$l->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$firstName = explode(" ", $_SESSION['usuarioNome'])[0];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Alterar Senha</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/cesta_custom.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        
        .container-alterar-senha {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn-voltar {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 600;
        }
        
        .alert {
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container-alterar-senha">
        <a href="../beneficiario.php" class="btn btn-secondary btn-voltar">
            <i class="glyphicon glyphicon-arrow-left"></i> Voltar
        </a>
        
        <h2 class="text-center mb-4">Alterar Senha</h2>
        <p class="text-center text-muted">Olá, <?= htmlspecialchars($firstName) ?>! Altere sua senha abaixo:</p>
        
        <form id="formAlterarSenha" method="POST" action="processamento/alterar_senha_processar.php">
            <div class="form-group">
                <label for="senha_atual">Senha Atual *</label>
                <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
            </div>
            
            <div class="form-group">
                <label for="senha_nova">Nova Senha *</label>
                <input type="password" class="form-control" id="senha_nova" name="senha_nova" required minlength="6">
                <small class="form-text text-muted">Mínimo de 6 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="senha_confirmar">Confirmar Nova Senha *</label>
                <input type="password" class="form-control" id="senha_confirmar" name="senha_confirmar" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Alterar Senha</button>
        </form>
        
        <?php
        if (isset($_GET['response'])) {
            $response = $_GET['response'];
            $messages = [
                1 => ['success', 'Sucesso!', 'Senha alterada com sucesso!'],
                2 => ['danger', 'Erro!', 'Senha atual incorreta.'],
                3 => ['danger', 'Erro!', 'As novas senhas não coincidem.'],
                4 => ['danger', 'Erro!', 'Preencha todos os campos.'],
                5 => ['danger', 'Erro!', 'A nova senha deve ter no mínimo 6 caracteres.'],
                6 => ['danger', 'Erro!', 'Erro interno. Tente novamente.']
            ];

            if (array_key_exists($response, $messages)) {
                list($type, $title, $text) = $messages[$response];
                echo "<div class='alert alert-$type alert-dismissible'>
                        <strong>$title</strong> $text
                      </div>";
            }
        }
        ?>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
        // Validação do formulário
        document.getElementById('formAlterarSenha').addEventListener('submit', function(e) {
            var senhaAtual = document.getElementById('senha_atual').value;
            var senhaNova = document.getElementById('senha_nova').value;
            var senhaConfirmar = document.getElementById('senha_confirmar').value;
            
            if (!senhaAtual || !senhaNova || !senhaConfirmar) {
                e.preventDefault();
                alert('Preencha todos os campos.');
                return false;
            }
            
            if (senhaNova.length < 6) {
                e.preventDefault();
                alert('A nova senha deve ter no mínimo 6 caracteres.');
                return false;
            }
            
            if (senhaNova !== senhaConfirmar) {
                e.preventDefault();
                alert('As novas senhas não coincidem.');
                return false;
            }
            
            return true;
        });
    </script>
</body>

</html>