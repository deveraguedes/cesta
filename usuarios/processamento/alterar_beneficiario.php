<?php 
include_once(__DIR__ . '/../../classes/beneficiario.class.php');
include_once(__DIR__ . '/../../classes/usuarios.class.php');
include_once(__DIR__ . '/../../classes/categoria.class.php');
include_once(__DIR__ . '/../../classes/conexao.class.php');

$b = new Beneficiario();

$result_alterar = $b->exibirBeneficiarioCod($_GET['cod_beneficiario']);
$row_beneficiario = $result_alterar->fetch(PDO::FETCH_ASSOC);

$cod_usuario = $_GET["cod_usuario"];

$u = new usuarios();
$result_usuario = $u->exibirUsuarioCod($cod_usuario);
$row_usuario = $result_usuario->fetch(PDO::FETCH_ASSOC);

$cod_unidade = $row_usuario["cod_unidade"];

// Carregar categorias
$c = new Categoria();
$categorias = $c->listarCategorias();
?>
<link rel="stylesheet" href="../../css/bootstrap-combobox.css">

<!-- Modal content-->
<div class="modal-content">
   <div class="modal-header">
      <h4 class="modal-title">Alterar Beneficiário</h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
   </div>

   <div class="modal-body">
      <form method="POST" name="form" action="usuarios/processamento/processar_beneficiario.php" data-toggle="validator" role="form">
         <input type="hidden" id="cod_beneficiario" name="cod_beneficiario" value="<?php echo $row_beneficiario["cod_beneficiario"]; ?>">
         <input type="hidden" id="cod_usuario" name="cod_usuario" value="<?php echo $cod_usuario; ?>">
         <input type="hidden" id="cod_unidade" name="cod_unidade" value="<?php echo $cod_unidade; ?>">

         <div class="form-group">
            <label for="nis">NIS</label>
            <input type="text" id="nis" class="form-control" name="nis" value="<?php echo $row_beneficiario["nis"]; ?>">
            <small class="form-text text-muted">Informe NIS ou CPF. Pelo menos um é obrigatório.</small>
         </div>

         <div class="form-group">
            <label for="vch_rg">CPF</label>
            <input type="text" id="cpf" class="form-control" name="cpf" value="<?php echo $row_beneficiario["cpf"]; ?>">
            <small class="form-text text-muted">Informe NIS ou CPF. Pelo menos um é obrigatório.</small>
         </div>

         <div class="form-group">
            <label for="vch_cpf">Nome</label>
            <input type="text" id="nome" class="form-control" name="nome" required value="<?php echo $row_beneficiario["nome"]; ?>">
         </div>

         <div class="form-group">
            <label for="vch_telefone">Bairro</label>
            <select name="cod_bairro" id="cod_bairro" class="combobox input-large form-control" required>
               <?php
               $data = $b->exibirBairro();
               while ($row_metodo = $data->fetch(PDO::FETCH_ASSOC)) {
                  $selected = $row_metodo['cod_bairro'] == $row_beneficiario["cod_bairro"] ? "selected" : "";
                  echo "<option value='{$row_metodo['cod_bairro']}' {$selected}>{$row_metodo['vch_bairro']}</option>";
               }
               ?>
            </select>
         </div>

         <div class="form-group">
            <label for="localidade">Localidade</label>
            <input type="text" id="localidade" class="form-control" name="localidade" value="<?php echo $row_beneficiario["localidade"]; ?>">
         </div>

         <div class="form-group">
            <label for="cep">CEP</label>
            <input type="text" id="cep" class="form-control" name="cep" value="<?php echo $row_beneficiario["cep"]; ?>">
         </div>

         <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" class="form-control" name="endereco" value="<?php echo $row_beneficiario["endereco"]; ?>" required>
         </div>

         <div class="form-group">
            <label for="complemento">Complemento</label>
            <input type="text" id="complemento" class="form-control" name="complemento" value="<?php echo $row_beneficiario["complemento"]; ?>">
         </div>

         <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" class="form-control" name="telefone" value="<?php echo $row_beneficiario["telefone"]; ?>">
         </div>

         <div class="form-group">
            <label for="cpf_responsavel">CPF Responsável</label>
            <input type="text" id="cpf_responsavel" class="form-control" name="cpf_responsavel" value="<?php echo $row_beneficiario["cpf_responsavel"]; ?>">
         </div>

         <div class="form-group">
            <label for="vch_responsavel">Nome Responsável</label>
            <input type="text" id="vch_responsavel" class="form-control" name="vch_responsavel" value="<?php echo $row_beneficiario["vch_responsavel"]; ?>">
         </div>

         <div class="form-group">
            <label for="cod_tipo">Tipo Beneficiário</label>
            <select name="cod_tipo" id="cod_tipo" class="combobox input-large form-control" required>
               <?php
               $data = $b->exibirTipo();
               while ($row_tipo = $data->fetch(PDO::FETCH_ASSOC)) {
                  $selected = $row_tipo['cod_tipo'] == $row_beneficiario["cod_tipo"] ? "selected" : "";
                  echo "<option value='{$row_tipo['cod_tipo']}' {$selected}>{$row_tipo['vch_tipo']}</option>";
               }
               ?>
            </select>
         </div>

         <!-- Campo de Categoria -->
         <div class="form-group">
            <label for="cod_categoria">Categoria</label>
            <select name="cod_categoria" id="cod_categoria" class="combobox input-large form-control">
               <option value="">-- Selecione --</option>
               <?php foreach ($categorias as $cat): 
                  $selected = $cat['cod_categoria'] == $row_beneficiario["cod_categoria"] ? "selected" : "";
                  echo "<option value='{$cat['cod_categoria']}' {$selected}>{$cat['vch_categoria']}</option>";
               endforeach; ?>
            </select>
         </div>

         <button type="submit" class="btn btn-success">Salvar Dados</button>
         <input type="hidden" name="MM_action" value="2">
      </form>
   </div>

   <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
   </div>
</div>
