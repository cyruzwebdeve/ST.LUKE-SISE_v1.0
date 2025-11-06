<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'enrollment_system';
$username = 'root';
$password = '';

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get user account with password
        $stmt = $pdo->prepare("SELECT * FROM user_account WHERE username = ? AND role = 'student'");
        $stmt->execute([$_POST['student_id']]);
        $userAccount = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userAccount && password_verify($_POST['password'],$userAccount['password'])) {
            // Get student details from student table
            $stmt = $pdo->prepare("SELECT * FROM student WHERE student_id = ?");
            $stmt->execute([$userAccount['student_id']]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $_SESSION['student_id'] = $userAccount['student_id'];
            $_SESSION['student_name'] = $student['student_name'];
            header('Location: student-dashboard.php');
            exit();
        } else {
            $error = 'Invalid Student ID or Password';
        }
        
    } catch(PDOException $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="photo/logo.png" type="image/x-icon">
    <title>Student Login - SIES</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
        
        <div class="text-center mb-8">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
               <img src="photo/logo.png" alt="logo">
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Student Portal</h1>
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
                    Student ID
                </label>
                <input type="text" name="student_id" required 
                       placeholder="e.g., 2024-0001"
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
                Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">Don't have an account? 
                <a href="enroll.php" class="text-blue-600 font-semibold hover:underline">Enroll now</a>
            </p>
        </div>
    </div>
</body>
</html>