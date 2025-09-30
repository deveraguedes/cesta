<?php
include_once(__DIR__ . '/../../classes/beneficiario.class.php');
include_once(__DIR__ . '/../../classes/usuarios.class.php');
include_once(__DIR__ . '/../../classes/categoria.class.php');
include_once('conexao.class.php');

$b = new Beneficiario();
$c = new Categoria();

// Pegar cod_usuario pela URL
$cod_usuario = $_GET["cod_usuario"] ?? null;

if (!$cod_usuario) {
   die("Erro: parâmetro cod_usuario não informado.");
}

$u = new Usuarios();
$result_usuario = $u->exibirUsuarioCod($cod_usuario);
$row_usuario = $result_usuario->fetch(PDO::FETCH_ASSOC);

if (!$row_usuario) {
   die("Erro: usuário não encontrado.");
}

$cod_unidade = $row_usuario["cod_unidade"];
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="../css/bootstrap-combobox.css">

<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
   $('.combobox').combobox();

   // Validações AJAX
   $("#nis").blur(function(){
      if ($('#nis').val() !== ""){
         $.post("verifica_nis.php", { nis: $('#nis').val() }, function(data){
            if (data == 1){
               alert ("Esse NIS já foi cadastrado para receber a cesta.");
               $('#nis').val("").focus();                  
            }
            if (data == 2){
               alert ("Esse NIS faz parte da composição familiar de um beneficiário da cesta de Páscoa.");
               $('#nis').val("").focus();                  
            }
         });
      }
   });

   $("#cpf").blur(function(){
      if ($('#cpf').val() !== ""){
         $.post("verifica_cpf.php", { cpf: $('#cpf').val() }, function(data){
            var partes = data.split('#'); 
            var status = partes[0]; 
            var unidade = partes[1]; 
            if (status == 1){
               alert ("Esse CPF já está cadastrado (Folha de Pagamento do Bolsa Família).");
               $('#cpf').val("").focus();                  
            }
            if (status == 2){
               alert ("Esse CPF faz parte da composição familiar de um beneficiário da cesta de Páscoa.");
               $('#cpf').val("").focus();                  
            }
            if (status == 3){
               alert ("Esse CPF já é beneficiário da cesta de Páscoa pela unidade " + unidade);
               $('#cpf').val("").focus();                  
            }
         });
      }
   });

   $("#cpf_responsavel").blur(function(){
      if ($('#cpf_responsavel').val() !== ""){
         $.post("verifica_cpf_responsavel.php", { cpf: $('#cpf_responsavel').val() }, function(data){
            if (data == 1){
               alert ("Esse CPF já foi cadastrado para receber a cesta.");
               $('#cpf_responsavel').val("").focus();                  
            }
            if (data == 2){
               alert ("Esse CPF faz parte da composição familiar de um beneficiário da cesta de Páscoa.");
               $('#cpf_responsavel').val("").focus();                  
            }
         });
      }
   });
});

function somenteNumeros(e) {
   var charCode = e.charCode ? e.charCode : e.keyCode;
   if (charCode != 8 && charCode != 9) {
      if (charCode < 48 || charCode > 57) {
         return false;
      }
   }
}
//]]>
</script>

<!-- Modal content-->
<div class="modal-content">
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h4 class="modal-title">Inserir Beneficiário</h4>
   </div>
   <div class="modal-body">
      <form method="POST" name="form" action="processamento/processar_beneficiario.php" data-toggle="validator" role="form">
         <input type="hidden" id="cod_usuario" name="cod_usuario" value="<?php echo htmlspecialchars($cod_usuario); ?>">
         <input type="hidden" id="cod_unidade" name="cod_unidade" value="<?php echo htmlspecialchars($cod_unidade); ?>">

         <div class="form-group">
            <label for="nis">NIS</label>
            <input type="text" id="nis" class="form-control" maxlength="15" name="nis"
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
            <select name="cod_bairro" id="cod_bairro" class="combobox input-large form-control" required>
               <?php 
               $data = $b->exibirBairro();    
               while($row_metodo = $data->fetch(PDO::FETCH_ASSOC)){
                  echo "<option value='".$row_metodo['cod_bairro']."'>". $row_metodo['vch_bairro'] . "</option>";
               }
               ?>
            </select>
         </div>

         <div class="form-group">
            <label for="localidade">Localidade</label>
            <input type="text" id="localidade" class="form-control" name="localidade">
         </div>

         <div class="form-group">
            <label for="cep">CEP</label>
            <input type="text" id="cep" class="form-control" name="cep">
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
            <label for="cpf_responsavel">CPF Responsável</label>
            <input type="text" id="cpf_responsavel" class="form-control" name="cpf_responsavel" maxlength="11" onkeypress="return somenteNumeros(event)">
         </div>

         <div class="form-group">
            <label for="vch_responsavel">Nome Responsável</label>
            <input type="text" id="vch_responsavel" class="form-control" name="vch_responsavel">
         </div>

         <div class="form-group">
            <label for="cod_tipo">Tipo Beneficiário</label>
            <select name="cod_tipo" id="cod_tipo" class="combobox input-large form-control" required>
               <?php 
               $data = $b->exibirTipo();    
               while($row_metodo = $data->fetch(PDO::FETCH_ASSOC)){
                   echo "<option value='".$row_metodo['cod_tipo']."'>". $row_metodo['vch_tipo'] . "</option>";
               }
               ?>
            </select>
         </div>

         <div class="form-group">
            <label for="cod_categoria">Categoria</label>
            <select name="cod_categoria" id="cod_categoria" class="combobox input-large form-control" required>
               <?php 
               $categorias = $c->listarCategorias();
               foreach ($categorias as $cat) {
                   echo "<option value='".$cat['cod_categoria']."'>".$cat['vch_categoria']."</option>";
               }
               ?>
            </select>
         </div>

         <input type="button" value="Salvar Dados" onclick="verifica();" class="btn btn-success">
         <input type="hidden" name="MM_action" value="1">
      </form>
   </div>
   <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
   </div>
</div>

<script type="text/javascript">
function verifica(){
   if ((document.getElementById("nis").value == "") && (document.getElementById("cpf").value == "")){
        alert("NIS ou CPF deve ser informado!");
        document.getElementById("nis").focus();
        return;
   }
   if (document.getElementById("nome").value == ""){
        alert("Nome do beneficiário deve ser informado!");
        document.getElementById("nome").focus();
        return;
   }
   if (document.getElementById("endereco").value == ""){
        alert("O endereço deve ser informado!");
        document.getElementById("endereco").focus();
        return;
   }
   if (document.getElementById("cod_bairro").value == 0){
        alert("Escolha um bairro para o beneficiário!");
        document.getElementById("cod_bairro").focus();
        return;
   }
   if (document.getElementById("cod_tipo").value == 0){
        alert("Escolha um tipo de benefício!");
        document.getElementById("cod_tipo").focus();
        return;
   }
   if (document.getElementById("cod_categoria").value == 0){
        alert("Escolha uma categoria!");
        document.getElementById("cod_categoria").focus();
        return;
   }
   if (!TestaCPF(document.getElementById("cpf").value)){
      alert("CPF não é válido!");
      document.getElementById("cpf").focus();
      return;
   }
   document.form.submit();
}

function TestaCPF(strCPF) {
   var Soma;
   var Resto;
   Soma = 0;
   if (strCPF == "") return true;
   if (strCPF == "00000000000") return false;
   for (i=1; i<=9; i++) Soma += parseInt(strCPF.substring(i-1, i)) * (11 - i);
   Resto = (Soma * 10) % 11;
   if ((Resto == 10) || (Resto == 11))  Resto = 0;
   if (Resto != parseInt(strCPF.substring(9, 10))) return false;
   Soma = 0;
   for (i = 1; i <= 10; i++) Soma += parseInt(strCPF.substring(i-1, i)) * (12 - i);
   Resto = (Soma * 10) % 11;
   if ((Resto == 10) || (Resto == 11))  Resto = 0;
   if (Resto != parseInt(strCPF.substring(10, 11))) return false;
   return true;
}
</script>
