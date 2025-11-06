<?php
// reset_passwords.php
// Run this file once to reset all student passwords to "12345"

$host = 'localhost';
$dbname = 'enrollment_system';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Hash the new password
    $newPassword = '12345';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update all student passwords
    $stmt = $pdo->prepare("UPDATE user_account SET password = ? WHERE role = 'student'");
    $stmt->execute([$hashedPassword]);

    echo "<h2>✅ All student passwords have been reset successfully.</h2>";
    echo "<p>New default password: <strong>$newPassword</strong></p>";
    echo "<p>Total affected accounts: " . $stmt->rowCount() . "</p>";

} catch (PDOException $e) {
    echo "<h2>❌ Error resetting passwords:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
