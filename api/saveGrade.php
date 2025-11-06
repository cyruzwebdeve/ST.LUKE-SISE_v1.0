<?php
session_start();
require_once '../db_connection.php';
header('Content-Type: application/json');

if (!isLoggedIn('teacher')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$studentId = $_POST['student_id'] ?? '';
$subjectCode = $_POST['subject_code'] ?? '';
$gradingPeriod = $_POST['grading_period'] ?? '';
$gradeScore = $_POST['grade_score'] ?? '';
$teacherId = $_SESSION['teacher_id'];

// Validate inputs
if (!$studentId || !$subjectCode || !$gradingPeriod || $gradeScore === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

if ($gradeScore < 0 || $gradeScore > 100) {
    echo json_encode(['success' => false, 'message' => 'Grade must be between 0 and 100']);
    exit();
}

try {
    // Check if grade already exists
    $existingGrade = dbQueryOne("
        SELECT grade_id FROM grade 
        WHERE student_id = ? AND subject_code = ? AND grading_period = ?
    ", [$studentId, $subjectCode, $gradingPeriod]);
    
    if ($existingGrade) {
        // Update existing grade
        $result = dbExecute("
            UPDATE grade 
            SET grade_score = ? 
            WHERE grade_id = ?
        ", [$gradeScore, $existingGrade['grade_id']]);
        
        $action = 'updated';
    } else {
        // Insert new grade
        $result = dbExecute("
            INSERT INTO grade (student_id, subject_code, grading_period, grade_score)
            VALUES (?, ?, ?, ?)
        ", [$studentId, $subjectCode, $gradingPeriod, $gradeScore]);
        
        $action = 'added';
    }
    
    if ($result !== false) {
        // Get student name for logging
        $student = dbQueryOne("SELECT student_name FROM student WHERE student_id = ?", [$studentId]);
        
        // Log activity
        logActivity(
            'teacher', 
            $teacherId, 
            "Teacher $action grade for {$student['student_name']} ($studentId) in $subjectCode - $gradingPeriod: $gradeScore"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Grade saved successfully',
            'action' => $action
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save grade']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>