<?php
/**
 * Run this script once to create the admin account
 * Then delete this file for security
 */

$host = 'localhost';
$dbname = 'enrollment_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if admin account already exists
    $stmt = $pdo->prepare("SELECT * FROM user_account WHERE username = 'admin' AND role = 'admin'");
    $stmt->execute();
    $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingAdmin) {
        echo "✓ Admin account already exists!<br>";
        echo "<strong>Username:</strong> admin<br>";
        echo "<strong>Password:</strong> admin123<br><br>";
        
        // Update password in case it was changed
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE user_account SET password = ? WHERE username = 'admin' AND role = 'admin'");
        $stmt->execute([$hashedPassword]);
        echo "✓ Password reset to: admin123<br>";
    } else {
        echo "Creating admin account...<br>";
        
        // Create admin account
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO user_account (username, password, role, student_id, teacher_id) 
            VALUES ('admin', ?, 'admin', NULL, NULL)
        ");
        $stmt->execute([$hashedPassword]);
        
        echo "<br><strong style='color: green;'>✓ SUCCESS! Admin account created!</strong><br><br>";
        echo "<strong>Username:</strong> admin<br>";
        echo "<strong>Password:</strong> admin123<br>";
    }
    
    echo "<br><hr><br>";
    echo "<h3>Login Information:</h3>";
    echo "<strong>Admin Login URL:</strong> <a href='admin-login.php'>admin-login.php</a><br>";
    echo "<strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin123<br><br>";
    
    echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file (create_admin.php) after creating the admin account for security!</p>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>