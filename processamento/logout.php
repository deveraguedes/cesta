<?php
session_start();
session_unset();
session_destroy();
header('Location: ../index.php'); // ou para onde quiser redirecionar
exit;
