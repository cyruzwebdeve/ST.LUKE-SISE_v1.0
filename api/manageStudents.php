<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$host = 'localhost';
$dbname = 'enrollment_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // GET - Fetch all students
    if ($action === 'getAll') {
        $stmt = $pdo->query("
            SELECT s.student_id, s.student_name, s.grade_level, s.gender,
                   e.enrollment_status, e.section_id, sec.section_name,
                   u.username
            FROM student s
            LEFT JOIN enrollment e ON s.student_id = e.student_id
            LEFT JOIN section sec ON e.section_id = sec.section_id
            LEFT JOIN user_account u ON s.student_id = u.student_id
            ORDER BY s.student_name
        ");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $students]);
    }
    
    // GET - Fetch pending enrollments
    elseif ($action === 'getPending') {
        $stmt = $pdo->query("
            SELECT e.enrollment_id, s.student_id, s.student_name, s.grade_level,
                   e.enrollment_status, e.date_enrolled, e.section_id
            FROM enrollment e
            INNER JOIN student s ON e.student_id = s.student_id
            WHERE e.enrollment_status = 'pending'
            ORDER BY e.date_enrolled DESC
        ");
        $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $pending]);
    }
    
    // APPROVE - Approve enrollment
    elseif ($action === 'approve') {
        $enrollmentId = $_POST['enrollment_id'];
        $sectionId = $_POST['section_id'];
        
        $stmt = $pdo->prepare("
            UPDATE enrollment 
            SET enrollment_status = 'enrolled', section_id = ? 
            WHERE enrollment_id = ?
        ");
        $stmt->execute([$sectionId, $enrollmentId]);
        
        // Get student name for logging
        $stmt = $pdo->prepare("
            SELECT s.student_name, s.student_id 
            FROM enrollment e
            INNER JOIN student s ON e.student_id = s.student_id
            WHERE e.enrollment_id = ?
        ");
        $stmt->execute([$enrollmentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin approved enrollment for {$student['student_name']} ({$student['student_id']})"
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Enrollment approved']);
    }
    
    // REJECT - Reject enrollment
    elseif ($action === 'reject') {
        $enrollmentId = $_POST['enrollment_id'];
        
        // Get student info
        $stmt = $pdo->prepare("
            SELECT s.student_name, s.student_id 
            FROM enrollment e
            INNER JOIN student s ON e.student_id = s.student_id
            WHERE e.enrollment_id = ?
        ");
        $stmt->execute([$enrollmentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("
            UPDATE enrollment 
            SET enrollment_status = 'rejected' 
            WHERE enrollment_id = ?
        ");
        $stmt->execute([$enrollmentId]);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin rejected enrollment for {$student['student_name']} ({$student['student_id']})"
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Enrollment rejected']);
    }
    
    // UPDATE SECTION - Change student section
    elseif ($action === 'updateSection') {
        $enrollmentId = $_POST['enrollment_id'];
        $sectionId = $_POST['section_id'];
        
        $stmt = $pdo->prepare("
            UPDATE enrollment 
            SET section_id = ? 
            WHERE enrollment_id = ?
        ");
        $stmt->execute([$sectionId, $enrollmentId]);
        
        echo json_encode(['success' => true, 'message' => 'Section updated']);
    }
    
    // DELETE - Delete student
    elseif ($action === 'delete') {
        $studentId = $_POST['student_id'];
        
        // Get student name
        $stmt = $pdo->prepare("SELECT student_name FROM student WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete user account
        $stmt = $pdo->prepare("DELETE FROM user_account WHERE student_id = ?");
        $stmt->execute([$studentId]);
        
        // Delete enrollment records
        $stmt = $pdo->prepare("DELETE FROM enrollment WHERE student_id = ?");
        $stmt->execute([$studentId]);
        
        // Delete grades
        $stmt = $pdo->prepare("DELETE FROM grade WHERE student_id = ?");
        $stmt->execute([$studentId]);
        
        // Delete student
        $stmt = $pdo->prepare("DELETE FROM student WHERE student_id = ?");
        $stmt->execute([$studentId]);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin deleted student: {$student['student_name']} ($studentId)"
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Student deleted']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>