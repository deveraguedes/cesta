<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

session_start();
include_once('../classes/beneficiario.class.php');
include_once('../classes/usuarios.class.php');
include_once('../classes/categoria.class.php');

if (!isset($_SESSION['user_id'])) {
    exit('Sessão expirada. Por favor, faça login novamente.');
}

$cod_usuario = $_SESSION['user_id'];
$cod_unidade = $_SESSION['cod_unidade'];

// Instanciar classes necessárias
$b = new Beneficiario();
$tipos = $b->exibirTipo();
$bairros = $b->exibirBairro();
?>

<div class="modal-header">
    <h4 class="modal-title">Inserir Beneficiário</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <!-- ALERTA DINÂMICO -->
    <div id="alertBeneficiario" class="alert d-none"></div>

    <form id="formBeneficiario" name="form" method="POST" action="processamento/processar_beneficiario.php" data-toggle="validator" role="form">
        <input type="hidden" id="cod_usuario" name="cod_usuario" value="<?php echo htmlspecialchars($cod_usuario); ?>">
        <input type="hidden" id="cod_unidade" name="cod_unidade" value="<?php echo htmlspecialchars($cod_unidade); ?>">

        <div class="form-group">
            <label for="nis">NIS</label>
            <input type="text" id="nis" class="form-control" maxlength="15" name="nis"
                   onblur="return verificarCPFNIS(this.value)"
                   onkeypress="return somenteNumeros(event)"
                   title="Coloque o NIS sem pontos ou traços">
        </div>

        <div class="form-group">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" class="form-control" name="cpf" maxlength="11"
                   onkeypress="return somenteNumeros(event)"
                   title="Coloque o CPF sem pontos ou traços">
        </div>

        <div class="form-group">
            <label for="nome">Nome</label>
            <input type="text" id="nome" class="form-control" name="nome" required>
        </div>

        <div class="form-group">
            <label for="cod_bairro">Bairro</label>
            <select name="cod_bairro" id="cod_bairro" class="form-control" required>
                <option value="">Selecione o bairro</option>
                <?php while($bairro = $bairros->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $bairro['cod_bairro'] ?>">
                        <?= htmlspecialchars($bairro['vch_bairro']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" class="form-control" name="endereco" required>
        </div>

        <div class="form-group">
            <label for="complemento">Complemento</label>
            <input type="text" id="complemento" class="form-control" name="complemento">
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" class="form-control" name="telefone">
        </div>

        <div class="form-group">
            <label for="cod_tipo">Tipo de Beneficiário</label>
            <select name="cod_tipo" id="cod_tipo" class="form-control" required>
                <option value="">Selecione o tipo</option>
                <?php while($tipo = $tipos->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $tipo['cod_tipo'] ?>">
                        <?= htmlspecialchars($tipo['vch_tipo']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <?php if ($_SESSION['int_level'] == 1): ?>
        <div class="form-group">
            <label for="cod_categoria">Categoria</label>
            <select name="cod_categoria" id="cod_categoria" class="form-control">
                <option value="">Selecione a categoria</option>
                <?php 
                $c = new Categoria();
                $categorias = $c->listarCategorias();
                foreach($categorias as $cat): 
                ?>
                    <option value="<?= $cat['cod_categoria'] ?>">
                        <?= htmlspecialchars($cat['vch_categoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>
