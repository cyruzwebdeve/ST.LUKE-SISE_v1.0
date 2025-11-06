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
    
    // GET - Fetch all schedules
    if ($action === 'getAll') {
        $stmt = $pdo->query("
            SELECT s.schedule_id, s.day_time, s.room_number,
                   t.teacher_id, t.teacher_name,
                   subj.subject_code, subj.subject_name,
                   sec.section_id, sec.section_name, sec.grade_level
            FROM schedule s
            INNER JOIN teacher t ON s.teacher_id = t.teacher_id
            INNER JOIN subject subj ON s.subject_code = subj.subject_code
            INNER JOIN section sec ON s.section_id = sec.section_id
            ORDER BY s.day_time
        ");
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $schedules]);
    }
    
    // GET - Fetch sections
    elseif ($action === 'getSections') {
        $gradeLevel = $_GET['grade_level'] ?? '';
        
        if ($gradeLevel) {
            $stmt = $pdo->prepare("SELECT * FROM section WHERE grade_level = ? ORDER BY section_name");
            $stmt->execute([$gradeLevel]);
        } else {
            $stmt = $pdo->query("SELECT * FROM section ORDER BY grade_level, section_name");
        }
        
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $sections]);
    }
    
    // GET - Fetch subjects
    elseif ($action === 'getSubjects') {
        $stmt = $pdo->query("SELECT * FROM subject ORDER BY subject_name");
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $subjects]);
    }
    
    // ADD - Create new schedule
    elseif ($action === 'add') {
        $teacherId = $_POST['teacher_id'];
        $subjectCode = $_POST['subject_code'];
        $sectionId = $_POST['section_id'];
        $dayTime = $_POST['day_time']; // Format: "2024-11-04 08:00:00"
        $roomNumber = $_POST['room_number'];
        
        $stmt = $pdo->prepare("
            INSERT INTO schedule (day_time, room_number, subject_code, section_id, teacher_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$dayTime, $roomNumber, $subjectCode, $sectionId, $teacherId]);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin created schedule for $subjectCode in section $sectionId"
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Schedule created successfully']);
    }
    
    // UPDATE - Edit schedule
    elseif ($action === 'update') {
        $scheduleId = $_POST['schedule_id'];
        $teacherId = $_POST['teacher_id'];
        $subjectCode = $_POST['subject_code'];
        $sectionId = $_POST['section_id'];
        $dayTime = $_POST['day_time'];
        $roomNumber = $_POST['room_number'];
        
        $stmt = $pdo->prepare("
            UPDATE schedule 
            SET day_time = ?, room_number = ?, subject_code = ?, 
                section_id = ?, teacher_id = ?
            WHERE schedule_id = ?
        ");
        $stmt->execute([$dayTime, $roomNumber, $subjectCode, $sectionId, $teacherId, $scheduleId]);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin updated schedule ID: $scheduleId"
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Schedule updated successfully']);
    }
    
    // DELETE - Remove schedule
    elseif ($action === 'delete') {
        $scheduleId = $_POST['schedule_id'];
        
        $stmt = $pdo->prepare("DELETE FROM schedule WHERE schedule_id = ?");
        $stmt->execute([$scheduleId]);
        
        // Log activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_type, user_id, action) 
            VALUES ('admin', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_username'],
            "Admin deleted schedule ID: $scheduleId"
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>