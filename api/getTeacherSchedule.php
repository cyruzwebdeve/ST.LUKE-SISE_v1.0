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
    // Get teacher's schedule
    $schedules = dbQuery("
        SELECT sch.schedule_id, sch.day_time, sch.room_number,
               sub.subject_name, sec.section_name, sec.grade_level
        FROM schedule sch
        INNER JOIN subject sub ON sch.subject_code = sub.subject_code
        INNER JOIN section sec ON sch.section_id = sec.section_id
        WHERE sch.teacher_id = ?
        ORDER BY sch.day_time
    ", [$teacherId]);
    
    echo json_encode([
        'success' => true,
        'data' => $schedules
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>