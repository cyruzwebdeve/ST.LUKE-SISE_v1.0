<?php
session_start();
require_once '../db_connection.php';
header('Content-Type: application/json');

if (!isLoggedIn('teacher')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$sectionId = $_GET['section_id'] ?? '';
$subjectCode = $_GET['subject_code'] ?? '';

if (!$sectionId || !$subjectCode) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    // Get all students in section with their grades for all quarters
    $students = dbQuery("
        SELECT s.student_id, s.student_name
        FROM student s
        INNER JOIN enrollment e ON s.student_id = e.student_id
        WHERE e.section_id = ? AND e.enrollment_status = 'enrolled'
        ORDER BY s.student_name
    ", [$sectionId]);
    
    $result = [];
    
    foreach ($students as $student) {
        $studentData = [
            'student_id' => $student['student_id'],
            'student_name' => $student['student_name'],
            'grades' => [
                '1st' => null,
                '2nd' => null,
                '3rd' => null,
                '4th' => null
            ]
        ];
        
        // Get grades for all quarters
        $grades = dbQuery("
            SELECT grading_period, grade_score
            FROM grade
            WHERE student_id = ? AND subject_code = ?
        ", [$student['student_id'], $subjectCode]);
        
        foreach ($grades as $grade) {
            $studentData['grades'][$grade['grading_period']] = $grade['grade_score'];
        }
        
        // Calculate average
        $gradeValues = array_filter($studentData['grades'], function($val) { return $val !== null; });
        $studentData['average'] = count($gradeValues) > 0 ? 
            number_format(array_sum($gradeValues) / count($gradeValues), 2) : '-';
        
        $result[] = $studentData;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>