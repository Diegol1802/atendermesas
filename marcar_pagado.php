<?php
include 'config.php';
$id = intval($_GET['id']);
$conn->query("UPDATE pedidos SET pagado = 1 WHERE id = $id");
header("Location: index.php");
exit;
?>
