<?php
session_start();
require_once '../db_connection.php';
header('Content-Type: application/json');

if (!isLoggedIn('teacher')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$gradeLevel = $_GET['grade_level'] ?? '';
$sectionId = $_GET['section_id'] ?? '';
$subjectCode = $_GET['subject_code'] ?? '';
$gradingPeriod = $_GET['grading_period'] ?? '1st';

if (!$gradeLevel || !$sectionId || !$subjectCode) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    // Get students enrolled in this section
    $students = dbQuery("
        SELECT s.student_id, s.student_name, s.grade_level,
               g.grade_score, g.grade_id
        FROM student s
        INNER JOIN enrollment e ON s.student_id = e.student_id
        LEFT JOIN grade g ON s.student_id = g.student_id 
            AND g.subject_code = ? 
            AND g.grading_period = ?
        WHERE e.section_id = ? 
            AND e.enrollment_status = 'enrolled'
            AND s.grade_level = ?
        ORDER BY s.student_name
    ", [$subjectCode, $gradingPeriod, $sectionId, $gradeLevel]);
    
    echo json_encode([
        'success' => true,
        'data' => $students
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>