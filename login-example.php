<?php
require_once 'config.php';

// If already logged in, redirect
if (isset($_SESSION['student_id'])) {
    header('Location: student-dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $pdo = getDBConnection();
            
            // Get user account
            $stmt = $pdo->prepare("
                SELECT u.*, s.student_name 
                FROM user_account u
                LEFT JOIN student s ON u.student_id = s.student_id
                WHERE u.username = ? AND u.role = 'student'
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['student_name'] = $user['student_name'];
                
                // Log the activity
                logActivity($username, 'student', 'login', 'Student logged in successfully');
                
                // Redirect to dashboard
                header('Location: student-dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password';
                
                // Log failed login attempt
                if ($user) {
                    logActivity($username, 'student', 'failed_login', 'Failed login attempt - incorrect password');
                }
            }
        } catch(PDOException $e) {
            $error = 'Database error occurred';
            error_log($e->getMessage());
        }
    } else {
        $error = 'Please enter both username and password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - SIES</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <span class="text-3xl font-bold text-white">SL</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Student Login</h1>
            <p class="text-gray-600 mt-2">School Information Enrollment System</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" required
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter your student ID">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter your password">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Login
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Don't have an account? <a href="enrollment.php" class="text-blue-600 hover:text-blue-800">Enroll here</a></p>
        </div>
    </div>
</body>
</html>