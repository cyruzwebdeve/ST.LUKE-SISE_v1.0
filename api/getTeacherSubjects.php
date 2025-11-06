<?php
session_start();
require_once '../db_connection.php';
header('Content-Type: application/json');

if (!isLoggedIn('teacher')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$teacherId = $_SESSION['teacher_id'];

try {
    // Get subjects this teacher teaches
    $subjects = dbQuery("
        SELECT DISTINCT sub.subject_code, sub.subject_name
        FROM subject sub
        INNER JOIN schedule sch ON sub.subject_code = sch.subject_code
        WHERE sch.teacher_id = ?
        ORDER BY sub.subject_name
    ", [$teacherId]);
    
    echo json_encode([
        'success' => true,
        'data' => $subjects
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>