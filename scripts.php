<!-- jQuery first, then Bootstrap JS -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-combobox.js"></script>
<script src="js/validator.min.js"></script>




<script>
<!-- Optional JavaScript -->

// Condicional para loading
$('form').validator().on('submit', function (e) {
  if (e.isDefaultPrevented()) {
  } else {
 $("#loading").show();
 
  }
})
</script>

    <script>
 //Evitar que a janela salve as informações.
$(document).modal("hide").on("hidden.bs.modal", function () {      
		$("#myModal").removeData();
});

//Esconder Alerts
$().ready(function() {
	setTimeout(function () {
		$('.alert').fadeOut(); // "foo" é o id do elemento que seja manipular.
		
	}, 2500); // O valor é representado em milisegundos.
});
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 
});
 </script>
 <script type="text/javascript">
      //<![CDATA[
        $(document).ready(function(){
          $('.combobox').combobox()
        });
      //]]>
	  
    </script>
 
