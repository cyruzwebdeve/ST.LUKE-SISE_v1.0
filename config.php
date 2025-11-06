<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'enrollment_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Function to get PDO connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, 
            DB_USER, 
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Function to check login and redirect
function requireLogin($role, $redirectPage) {
    $roleKey = $role . '_id';
    if (!isset($_SESSION[$roleKey])) {
        header('Location: ' . $redirectPage);
        exit();
    }
}

// Function to log activity
function logActivity($username, $role, $activityType = 'login', $description = '') {
    try {
        $pdo = getDBConnection();
        
        // Get user_id
        $stmt = $pdo->prepare("SELECT user_id FROM user_account WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $user ? $user['user_id'] : null;
        
        // Get IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        // Get user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Insert activity log
        $stmt = $pdo->prepare("
            INSERT INTO activity_log 
            (user_id, username, role, activity_type, activity_description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $username,
            $role,
            $activityType,
            $description,
            $ipAddress,
            $userAgent
        ]);
        
        return true;
    } catch(PDOException $e) {
        // Silent fail - don't interrupt the login process
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}
?>