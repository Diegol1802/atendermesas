<?php

date_default_timezone_set('America/Santiago');

$host = 'localhost';
$db   = 'c2830289_pena';
$user = 'c2830289_pena';
$pass = 'ragiwaZO00';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}
?>
