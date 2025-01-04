<?php
include('functions.php');
if (isset($_GET['id'])) {
    $usuario_id = $_GET['id'];
    alterarTipoUsuario($conn, $usuario_id);
    header("Location: adminDashboard.php");
}
?>
