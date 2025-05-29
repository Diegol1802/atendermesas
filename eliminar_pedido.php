<?php
include 'config.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM pedido_detalle WHERE pedido_id = $id");
$conn->query("DELETE FROM pedidos WHERE id = $id");
header("Location: index.php");
exit;
?>
