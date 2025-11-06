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
    
    // GET - Fetch all teachers
    if ($action === 'getAll') {
        $stmt = $pdo->query("
            SELECT t.teacher_id, t.teacher_name, 
                   u.username, u.role as account_role
            FROM teacher t
            LEFT JOIN user_account u ON t.teacher_id = u.teacher_id
            ORDER BY t.teacher_name
        ");
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $teachers]);
    }
    
    // ADD - Create new teacher
    elseif ($action === 'add') {
        $teacherName = $_POST['teacher_name'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'] ?? 'Subject Teacher';
        
        // Generate teacher ID
        $stmt = $pdo->query("SELECT teacher_id FROM teacher ORDER BY teacher_id DESC LIMIT 1");
        $lastTeacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastTeacher) {
            $lastNumber = intval(substr($lastTeacher['teacher_id'], 1));
            $newNumber = $lastNumber + 1;
            $teacherId = 'T' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        } else {
            $teacherId = 'T001';
        }
        
        // Insert into teacher table
        $stmt = $pdo->prepare("INSERT INTO teacher (teacher_id, teacher_name) VALUES (?, ?)");
        $stmt->execute([$teacherId, $teacherName]);
        
        // Insert into user_account table
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO user_account (username, password, role, teacher_id) 
            VALUES (?, ?, 'teacher', ?)
        ");
        $stmt->execute([$username, $hashedPassword, $teacherId]);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin created teacher account: $teacherName ($teacherId)"
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Teacher account created successfully',
            'teacherId' => $teacherId
        ]);
    }
    
    // UPDATE - Edit teacher
    elseif ($action === 'update') {
        $teacherId = $_POST['teacher_id'];
        $teacherName = $_POST['teacher_name'];
        $username = $_POST['username'];
        
        // Update teacher table
        $stmt = $pdo->prepare("UPDATE teacher SET teacher_name = ? WHERE teacher_id = ?");
        $stmt->execute([$teacherName, $teacherId]);
        
        // Update user_account table
        $stmt = $pdo->prepare("UPDATE user_account SET username = ? WHERE teacher_id = ?");
        $stmt->execute([$username, $teacherId]);
        
        // If password is provided, update it
        if (!empty($_POST['password'])) {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE user_account SET password = ? WHERE teacher_id = ?");
            $stmt->execute([$hashedPassword, $teacherId]);
        }
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin updated teacher account: $teacherName ($teacherId)"
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Teacher updated successfully']);
    }
    
    // DELETE - Remove teacher
    elseif ($action === 'delete') {
        $teacherId = $_POST['teacher_id'];
        
        // Get teacher name for logging
        $stmt = $pdo->prepare("SELECT teacher_name FROM teacher WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete from user_account first (due to foreign key)
        $stmt = $pdo->prepare("DELETE FROM user_account WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        
        // Delete from teacher table
        $stmt = $pdo->prepare("DELETE FROM teacher WHERE teacher_id = ?");
        $stmt->execute([$teacherId]);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin deleted teacher account: {$teacher['teacher_name']} ($teacherId)"
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Teacher deleted successfully']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>