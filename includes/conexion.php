<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "citasmedicas";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexi�n fallida: " . $conn->connect_error);
}
?>
