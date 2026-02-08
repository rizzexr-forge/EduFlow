<?php
// db.php
session_start();
$db_server = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "university_schedule";

// Check if we are in an API context (simple check)
$is_api = (strpos($_SERVER['REQUEST_URI'] ?? '', 'api.php') !== false);

try {
    // Suppress warnings to prevent HTML output breaking JSON
    $conn = @mysqli_connect(
        $db_server, 
        $db_user, 
        $db_pass, 
        $db_name);
        
    if ($conn) {
        mysqli_set_charset($conn, "utf8mb4");
    } else {
        throw new Exception("Ошибка подключения: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    if ($is_api) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    } else {
        die($e->getMessage());
    }
}
?>
