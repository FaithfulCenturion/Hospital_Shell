<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // default XAMPP password is empty
$db   = 'hospital_shell';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
