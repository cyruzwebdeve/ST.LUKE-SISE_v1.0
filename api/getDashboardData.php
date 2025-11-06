<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
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
    
    // Get total teachers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM teacher");
    $totalTeachers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total students
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM student");
    $totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total schedules
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM schedule");
    $totalSchedules = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get pending enrollments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM enrollment WHERE enrollment_status = 'pending'");
    $pendingEnrollments = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get recent activity logs
    $stmt = $pdo->query("SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 10");
    $activityLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'totalTeachers' => $totalTeachers,
            'totalStudents' => $totalStudents,
            'totalSchedules' => $totalSchedules,
            'pendingEnrollments' => $pendingEnrollments,
            'activityLogs' => $activityLogs
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>