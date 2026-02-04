<?php
session_start();
if(!isset($_SESSION['usuario_id'])){
    header('Location: login.php');
    exit;
}

?>
<h1>Hola</h1>