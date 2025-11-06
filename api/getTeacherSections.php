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
    // Get sections where this teacher has schedules
    $sections = dbQuery("
        SELECT DISTINCT sec.section_id, sec.section_name, sec.grade_level
        FROM schedule sch
        INNER JOIN section sec ON sch.section_id = sec.section_id
        WHERE sch.teacher_id = ?
        ORDER BY sec.grade_level, sec.section_name
    ", [$teacherId]);
    
    echo json_encode([
        'success' => true,
        'data' => $sections
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>