<?php 
    session_start();
    unset($_SESSION['logat_concedii']);
    unset($_SESSION['user_concedii']);
    unset($_SESSION['user_id_concedii']);
    session_destroy();
    header("Location:index.php");
 ?>