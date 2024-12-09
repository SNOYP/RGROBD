<?php
$db_hostname = "localhost";
$db_database = "arh_pc"; 
$db_username = "root";
$db_password = "";

$conn = new mysqli($db_hostname, $db_username, $db_password, $db_database);

if ($conn->connect_error) {
    die("Підключення не вдалося: " . $conn->connect_error);
}
?>