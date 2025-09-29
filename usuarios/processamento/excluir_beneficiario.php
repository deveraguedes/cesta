<?php include_once(__DIR__ . '/../../classes/beneficiario.class.php');
include_once('conexao.class.php');

$b = new Beneficiario();

$cod_usuario = $_GET["cod_usuario"];
$cod_unidade = $_GET["cod_unidade"];
//$cod_beneficiario = $_GET["cod_beneficiario"];

$result_alterar = $b->exibirBeneficiarioCod($_GET['cod_beneficiario']);
$row_beneficiario = $result_alterar->fetch(PDO::FETCH_ASSOC); ?>

?>
<script type="text/javascript">
      //<![CDATA[
        $(document).ready(function(){
          $('.combobox').combobox()
        });
      //]]>
	  
    </script>
    <link rel="stylesheet" href="../css/bootstrap-combobox.css">
<!-- Modal content-->
    <div class="modal-content">
       <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Excluir Beneficiário</h4>
      </div>
       <div class="modal-body">
       <div class="alert alert-danger"> <strong>Perigo!</strong> Você está prestes a apagar o registro.</div>
        <form method="POST" name="form" action="processamento/processar_beneficiario.php" data-toggle="validator" role="form" >
         <input type="hidden" id="cod_beneficiario" name="cod_beneficiario" value="<?php echo $row_beneficiario["cod_beneficiario"]; ?>"  >
         <input type="hidden" id="cod_usuario" name="cod_usuario" value="<?php echo $cod_usuario; ?>"  >

	<div class="form-group">
       <label for="nis">NIS</label>
       <input type="text" id="nis" class="form-control" name="nis" value="<?php echo $row_beneficiario["nis"]; ?>" >
    </div>
	<div class="form-group">
       <label for="vch_rg">CPF</label>
       <input type="text" id="cpf" class="form-control" name="cpf" value="<?php echo $row_beneficiario["cpf"]; ?>" >
    </div>
	<div class="form-group">
       <label for="vch_cpf">Nome</label>
       <input type="text" id="nome" class="form-control" name="nome" required value="<?php echo $row_beneficiario["nome"]; ?>">
    </div>
	<div class="form-group">
       <label for="vch_telefone">Bairro</label>
       <select name="cod_bairro" id="cod_bairro" class="combobox input-large form-control"  required>
			<?php 
                    
            $data = $b->exibirBairro();	
            while($row_metodo = $data->fetch(PDO::FETCH_ASSOC)){
              if ($row_metodo['cod_bairro'] == $row_beneficiario["cod_bairro"]) 
                  echo "<option value='".$row_metodo['cod_bairro']."' selected>". $row_metodo['vch_bairro'] . "</option>";
              else     
                  echo "<option value='".$row_metodo['cod_bairro']."'>". $row_metodo['vch_bairro'] . "</option>";

            }
			?>
			      </select>


    </div>
	<div class="form-group">
       <label for="vch_cnh">Localidade</label>
       <input type="text" id="localidade" class="form-control" name="localidade" value="<?php echo $row_beneficiario["localidade"]; ?>" >
    </div>
    <div class="form-group">
       <label for="vch_cnh">Unidade</label>
       <input type="text" id="cod_unidade" class="form-control" name="cod_unidade" value="<?php echo $cod_unidade; ?>" >
    </div>
	<div class="form-group">
       <label for="sdt_validade_cnh">CPF Responsavel</label>
       <input type="text" id="cpf_responsavel" class="form-control" name="cpf_responsavel" value="<?php echo $row_beneficiario["cpf_responsavel"]; ?>" >
    </div>
    <div class="form-group">
       <label for="sdt_validade_cnh">Nome Responsavel</label>
       <input type="text" id="vch_responsavel" class="form-control" name="vch_responsavel" value="<?php echo $row_beneficiario["vch_responsavel"]; ?>" >
    </div>
    <div class="form-group">
       <label for="sdt_validade_cnh">Tipo Beneficiário</label>
       <select name="cod_tipo" id="cod_tipo" class="combobox input-large form-control"  required>
			<?php 
                    
            $data = $b->exibirTipo();	
            while($row_tipo = $data->fetch(PDO::FETCH_ASSOC)){
                if ($row_tipo['cod_tipo'] == $row_beneficiario["cod_tipo"]) 
                  echo "<option value='".$row_tipo['cod_tipo']."' selected>". $row_tipo['vch_tipo'] . "</option>";
                else   
                  echo "<option value='".$row_tipo['cod_tipo']."'>". $row_tipo['vch_tipo'] . "</option>";

      }
      ?>
    </select>

    </div>
    <input type="submit"  class="btn btn-danger" value="Apagar"  >
           <input type="hidden" name="MM_action" value="3">
         </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
     </div>
     
     </script>