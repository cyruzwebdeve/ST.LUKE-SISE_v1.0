<?php
/**
 * Run this script once to create teacher user accounts
 * Then delete this file for security
 */

require_once 'db_connection.php';

try {
    echo "<h2>Creating Teacher User Accounts</h2>";
    echo "<hr><br>";
    
    // Get all teachers
    $teachers = dbQuery("SELECT teacher_id, teacher_name FROM teacher ORDER BY teacher_id");
    
    if (empty($teachers)) {
        echo "<p style='color: red;'>No teachers found in the database!</p>";
        exit;
    }
    
    foreach ($teachers as $teacher) {
        // Check if user account already exists
        $existingAccount = dbQueryOne(
            "SELECT * FROM user_account WHERE teacher_id = ?",
            [$teacher['teacher_id']]
        );
        
        if ($existingAccount) {
            echo "✓ User account already exists for <strong>{$teacher['teacher_name']}</strong> ({$teacher['teacher_id']})<br>";
            continue;
        }
        
        // Create username from teacher_id (e.g., T001 -> t001)
        $username = strtolower($teacher['teacher_id']);
        
        // Default password is the teacher_id
        $password = $teacher['teacher_id'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user account
        $result = dbExecute(
            "INSERT INTO user_account (username, password, role, teacher_id) 
             VALUES (?, ?, 'teacher', ?)",
            [$username, $hashedPassword, $teacher['teacher_id']]
        );
        
        if ($result) {
            echo "✓ Created account for <strong>{$teacher['teacher_name']}</strong><br>";
            echo "&nbsp;&nbsp;&nbsp;Username: <strong>$username</strong><br>";
            echo "&nbsp;&nbsp;&nbsp;Password: <strong>$password</strong><br><br>";
        } else {
            echo "✗ Failed to create account for {$teacher['teacher_name']}<br><br>";
        }
    }
    
    echo "<hr><br>";
    echo "<h3>All Teacher Login Credentials:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #3b82f6; color: white;'>";
    echo "<th>Teacher ID</th><th>Teacher Name</th><th>Username</th><th>Password</th>";
    echo "</tr>";
    
    foreach ($teachers as $teacher) {
        $username = strtolower($teacher['teacher_id']);
        $password = $teacher['teacher_id'];
        
        echo "<tr>";
        echo "<td>{$teacher['teacher_id']}</td>";
        echo "<td>{$teacher['teacher_name']}</td>";
        echo "<td><strong>$username</strong></td>";
        echo "<td><strong>$password</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<br><br>";
    echo "<p><strong>Teacher Login URL:</strong> <a href='teacher-login.php'>teacher-login.php</a></p>";
    echo "<br>";
    echo "<p style='color: red; font-weight: bold;'>IMPORTANT: Delete this file (create_teacher_accounts.php) after running it for security!</p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>