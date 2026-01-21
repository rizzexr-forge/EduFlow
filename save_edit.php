<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0); // ← ВАЖНО: отключаем вывод ошибок в браузер!

include("database.php");
session_start();

if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$date = $input['date'] ?? '';
$lesson = (int)($input['lesson'] ?? 0);
$subject = trim($input['subject'] ?? '');

if (!$date || !$lesson || !$subject || $lesson < 1 || $lesson > 6) {
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO edit_schedule (lesson_date, lesson_number, subject) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE subject = VALUES(subject)");
mysqli_stmt_bind_param($stmt, "sis", $date, $lesson, $subject);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'SQL error']);
}