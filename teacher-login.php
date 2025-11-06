<?php
session_start();
require_once 'db_connection.php';

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    // Get user account
    $account = dbQueryOne(
        "SELECT * FROM user_account WHERE username = ? AND role = 'teacher'",
        [$username]
    );
    
    if ($account && password_verify($password, $account['password'])) {
        // Get teacher details
        $teacher = dbQueryOne(
            "SELECT * FROM teacher WHERE teacher_id = ?",
            [$account['teacher_id']]
        );
        
        if ($teacher) {
            $_SESSION['teacher_id'] = $teacher['teacher_id'];
            $_SESSION['teacher_name'] = $teacher['teacher_name'];
            $_SESSION['user_id'] = $account['user_id'];
            
            // Log activity
            logActivity('teacher', $teacher['teacher_id'], 'Teacher logged in');
            
            header('Location: teacher-dashboard.php');
            exit();
        }
    }
    
    $error = 'Invalid username or password';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login - SIES</title>
    <link rel="shortcut icon" href="photo/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
        
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-white-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <img src="photo/logo.png" alt="logo">
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Teacher Portal</h1>
            <p class="text-gray-600">ST. LUKE CHRISTIAN SCHOOL & LEARNING CENTER</p>
        </div>

        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Username
                </label>
                <input type="text" name="username" required 
                       placeholder="Enter your username"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Password
                </label>
                <input type="password" name="password" required 
                       placeholder="Enter your password"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition">
            </div>

            <button type="submit" 
                    class="w-full bg-orange-500 text-white font-bold py-3 rounded-lg hover:bg-orange-600 transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                Login as Teacher
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600 text-sm">
                <a href="admin-login.php" class="text-blue-600 font-semibold hover:underline">Admin Login</a>
            </p>
        </div>
    </div>
</body>
</html>