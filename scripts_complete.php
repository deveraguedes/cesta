<!-- Optional JavaScript -->
<!-- jQuery first, then Bootstrap JS -->
<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/bootstrap-combobox.js"></script>
<script src="../js/validator.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
<script>
	
// Condicional para loading
$('form').validator().on('submit', function (e) {
  if (e.isDefaultPrevented()) {
  } else {
 $("#loading").show();
 
  }
})
</script>

<script type="text/javascript">
// Combos auto preenchidos
      //<![CDATA[
        $(document).ready(function(){
          $('.combobox').combobox()
        });
      //]]>
	  
    </script>
    <script>
 //Evitar que a janela salve as informações.
$(document).modal("hide").on("hidden.bs.modal", function () {      
		$("#myModal").removeData();
});
 </script>
 <script src='https://www.google.com/recaptcha/api.js'></script>
 <script>
function verifica_senha(){
	senha = document.getElementById("password").value;
	forca = 0;
	mostra = document.getElementById("mostra");
	if((senha.length >= 4) && (senha.length <= 7)){
		forca += 10;
	}else if(senha.length>7){
		forca += 25;
	}
	if(senha.match(/[a-z]+/)){
		forca += 10;
	}
	if(senha.match(/[A-Z]+/)){
		forca += 20;
	}
	if(senha.match(/d+/)){
		forca += 20;
	}
	if(senha.match(/W+/)){
		forca += 25;
	}
	return mostra_res();
}
function mostra_res(){
	if(forca < 30){
		mostra.innerHTML = '<div class="progress"><div class="progress-bar progress-bar-danger"role="progressbar" aria-valuenow="'+forca+'" aria-valuemin="0" aria-valuemax="85" style="width:'+forca+'%"> '+forca+'% (Fraca)</div></div>';
	}else if((forca >= 30) && (forca < 60)){
		mostra.innerHTML = '<div class="progress"><div class="progress-bar progress-bar-warning"role="progressbar" aria-valuenow="'+forca+'" aria-valuemin="0" aria-valuemax="85" style="width:'+forca+'%"> '+forca+'% (Justa)</div></div>';
	}else if((forca >= 60) && (forca < 85)){
		mostra.innerHTML = '<div class="progress"><div class="progress-bar progress-bar-success"role="progressbar" aria-valuenow="'+forca+'" aria-valuemin="0" aria-valuemax="85" style="width:100%">100% (Forte)</div></div>';
	}else{
		mostra.innerHTML = '<div class="progress"><div class="progress-bar progress-bar-success"role="progressbar" aria-valuenow="'+forca+'" aria-valuemin="0" aria-valuemax="85" style="width:'+forca+'%">'+forca+'% (Forte2)</div></div>';
	}
}
</script>