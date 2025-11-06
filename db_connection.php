<?php
/**
 * Universal Database Connection File
 * 
 * This file provides a centralized database connection that can be used
 * across all PHP files in the enrollment system.
 * 
 * Usage:
 * require_once 'db_connection.php';
 * $result = $conn->query("SELECT * FROM student");
 * 
 * Or use the helper functions:
 * $pdo = getDBConnection();
 * $stmt = $pdo->prepare("SELECT * FROM student WHERE student_id = ?");
 */

// ========================================
// DATABASE CONFIGURATION
// ========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'enrollment_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ========================================
// PDO CONNECTION (Recommended)
// ========================================
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

// ========================================
// MYSQLI CONNECTION (Alternative)
// ========================================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log("MySQLi Connection Error: " . $conn->connect_error);
    die("Database connection failed. Please contact the administrator.");
}

$conn->set_charset(DB_CHARSET);

// ========================================
// HELPER FUNCTIONS
// ========================================

/**
 * Get PDO Database Connection
 * 
 * @return PDO Database connection object
 */
function getDBConnection() {
    global $pdo;
    return $pdo;
}

/**
 * Get MySQLi Database Connection
 * 
 * @return mysqli Database connection object
 */
function getMySQLiConnection() {
    global $conn;
    return $conn;
}

/**
 * Execute a prepared SELECT query and return results
 * 
 * @param string $query SQL query with placeholders
 * @param array $params Parameters to bind
 * @return array Result set
 */
function dbQuery($query, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Execute a prepared INSERT/UPDATE/DELETE query
 * 
 * @param string $query SQL query with placeholders
 * @param array $params Parameters to bind
 * @return bool|int Returns affected rows or last insert ID on success, false on failure
 */
function dbExecute($query, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        // Return last insert ID for INSERT statements
        if (stripos($query, 'INSERT') === 0) {
            return $pdo->lastInsertId();
        }
        
        // Return affected rows for UPDATE/DELETE
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Execute Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Execute a query and return a single row
 * 
 * @param string $query SQL query with placeholders
 * @param array $params Parameters to bind
 * @return array|false Single row result or false
 */
function dbQueryOne($query, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Query One Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get count of records
 * 
 * @param string $table Table name
 * @param string $where WHERE clause (optional)
 * @param array $params Parameters to bind
 * @return int Count of records
 */
function dbCount($table, $where = '', $params = []) {
    global $pdo;
    try {
        $query = "SELECT COUNT(*) as count FROM " . $table;
        if ($where) {
            $query .= " WHERE " . $where;
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int) $result['count'];
    } catch (PDOException $e) {
        error_log("Count Error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Begin database transaction
 */
function dbBeginTransaction() {
    global $pdo;
    return $pdo->beginTransaction();
}

/**
 * Commit database transaction
 */
function dbCommit() {
    global $pdo;
    return $pdo->commit();
}

/**
 * Rollback database transaction
 */
function dbRollback() {
    global $pdo;
    return $pdo->rollBack();
}

/**
 * Sanitize input to prevent XSS
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if user is logged in
 * 
 * @param string $role User role to check (student, teacher, admin)
 * @return bool True if logged in with correct role
 */
function isLoggedIn($role = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if ($role === 'admin') {
        return isset($_SESSION['admin_id']);
    } elseif ($role === 'teacher') {
        return isset($_SESSION['teacher_id']);
    } elseif ($role === 'student') {
        return isset($_SESSION['student_id']);
    } else {
        return isset($_SESSION['admin_id']) || 
               isset($_SESSION['teacher_id']) || 
               isset($_SESSION['student_id']);
    }
}

/**
 * Require login and redirect if not logged in
 * 
 * @param string $role Required role
 * @param string $redirectTo Redirect URL if not logged in
 */
function requireLogin($role = null, $redirectTo = 'login.php') {
    if (!isLoggedIn($role)) {
        header("Location: $redirectTo");
        exit();
    }
}

/**
 * Log activity to activity_log table
 * 
 * @param string $userType Type of user (admin, teacher, student)
 * @param string $userId User ID
 * @param string $action Action description
 * @return bool Success status
 */
function logActivity($userType, $userId, $action) {
    return dbExecute(
        "INSERT INTO activity_log (user_type, user_id, action) VALUES (?, ?, ?)",
        [$userType, $userId, $action]
    );
}

/**
 * Get current user ID based on session
 * 
 * @return string|null User ID or null if not logged in
 */
function getCurrentUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['admin_id'])) {
        return $_SESSION['admin_username'] ?? 'admin';
    } elseif (isset($_SESSION['teacher_id'])) {
        return $_SESSION['teacher_id'];
    } elseif (isset($_SESSION['student_id'])) {
        return $_SESSION['student_id'];
    }
    
    return null;
}

/**
 * Close database connections
 */
function closeDBConnections() {
    global $pdo, $conn;
    $pdo = null;
    if ($conn) {
        $conn->close();
    }
}

// Register shutdown function to close connections
register_shutdown_function('closeDBConnections');

// ========================================
// ERROR HANDLING
// ========================================

/**
 * Custom error handler for database operations
 */
function dbErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    
    // Don't show errors to users in production
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
        echo "An error occurred. Please contact support.";
    } else {
        echo "<b>Error:</b> $errstr<br>";
        echo "<b>File:</b> $errfile<br>";
        echo "<b>Line:</b> $errline<br>";
    }
}

// Set custom error handler
set_error_handler('dbErrorHandler');

// ========================================
// ENVIRONMENT CONFIGURATION
// ========================================

// Set to 'production' in live environment
define('ENVIRONMENT', 'development');

// Display errors only in development
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>