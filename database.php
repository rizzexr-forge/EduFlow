<?php
session_start();
$db_server = "127.0.0.1";
$db_user = "farwo";
$db_pass = "0410";
$db_name = "farwo_db";

$conn = mysqli_connect(
    $db_server, 
    $db_user, 
    $db_pass, 
    $db_name);
    
mysqli_set_charset($conn, "utf8mb4");

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}
?>