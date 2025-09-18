<?php
 include_once 'baseurl.php';
?>
<!-- Sidebar -->
<div class="bg-light border-right" id="sidebar-wrapper">
    <div class="list-group list-group-flush">
        <!-- <li data-toggle="collapse" data-target="#service" class="collapsed" style="list-style-type:none;">
            <a href="#" class="list-group-item list-group-item-action bg-light">
                <b>Geral</b> 
            </a>
        </li>

        <ul class="collapse" id="service" style="margin-bottom: 0px;">
            <li style="list-style-type:none; width: 150px; margin-left: -40px; border-radius: none;">
                <a href="<?php echo $url ?>/index.php" class="list-group-item list-group-item-action bg-light">Pagina Inicial </a>
            </li>
        </ul> -->

        <!--menu2-->
        <a href="#" class="list-group-item list-group-item-action bg-light" rel="nofollow">Contato</a>

        <!-- Relatorio -->
        <a href="relatorio.php" class="list-group-item list-group-item-action bg-light" rel="nofollow">Relatório</a>
    </div>
</div>

<!-- /#sidebar-wrapper -->

<!-- Page Content -->
<div id="page-content-wrapper">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom">
        <button class="btn btn-light" id="menu-toggle">Menu</button>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto mt-2 mt-lg-0 float-right">
                <li class="nav-item active">
                    <a class="nav-link" href="#">
                        <span class="sr-only">(current)</span>
                    </a>
                </li>
             
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="#" onClick="openFullscreen()">
                            <i class="glyphicon glyphicon-fullscreen text-white"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" onClick="closeFullscreen()">
                            <i class="glyphicon glyphicon-resize-small text-white"></i>
                        </a>
                    </li>

                    <li>
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="">
                            <span class="glyphicon glyphicon-user" style="color: #ffcd00;"></span> 
                            <span style="color: #ffcd00;"> <?php echo $_SESSION['usuarioNome']; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $url ?>usuarios/logout.php">
                            <span class="glyphicon glyphicon-log-in" style="color: #ffcd00;"></span> 
                            <span style="color: #ffcd00;">Sair</span>
                        </a>
                    </li>
                </ul>

             
            </ul>
        </div>
    </nav>

    <script>
        /* Get the documentElement (<html>) to display the page in fullscreen */
        var elem = document.documentElement;

        /* View in fullscreen */
        function openFullscreen() {
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.mozRequestFullScreen) {
                /* Firefox */
                elem.mozRequestFullScreen();
            } else if (elem.webkitRequestFullscreen) {
                /* Chrome, Safari and Opera */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                /* IE/Edge */
                elem.msRequestFullscreen();
            }
        }

        /* Close fullscreen */
        function closeFullscreen() {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                /* Firefox */
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                /* Chrome, Safari and Opera */
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                /* IE/Edge */
                document.msExitFullscreen();
            }
        }
    </script>
