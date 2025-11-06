<?php
// Test API Connection
// Place this file in your root directory and access it via browser

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>API Connection Test</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>1. Database Connection</h2>";
try {
    $pdo = getDBConnection();
    echo "✅ <strong style='color:green'>Database connected successfully!</strong><br>";
    echo "Host: localhost<br>";
    echo "Database: enrollment_system<br>";
} catch (Exception $e) {
    echo "❌ <strong style='color:red'>Database connection failed:</strong> " . $e->getMessage() . "<br>";
}

// Test 2: Check Tables
echo "<hr>";
echo "<h2>2. Check Tables</h2>";
try {
    $tables = ['teacher', 'student', 'user_account', 'schedule', 'enrollment', 'activity_log'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '<strong>$table</strong>' exists<br>";
        } else {
            echo "❌ Table '<strong style='color:red'>$table</strong>' NOT found<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking tables: " . $e->getMessage() . "<br>";
}

// Test 3: Count Records
echo "<hr>";
echo "<h2>3. Count Records</h2>";
try {
    $counts = [
        'Teachers' => "SELECT COUNT(*) as count FROM teacher",
        'Students' => "SELECT COUNT(*) as count FROM student",
        'User Accounts' => "SELECT COUNT(*) as count FROM user_account",
        'Schedules' => "SELECT COUNT(*) as count FROM schedule",
        'Enrollments' => "SELECT COUNT(*) as count FROM enrollment",
        'Activity Logs' => "SELECT COUNT(*) as count FROM activity_log"
    ];
    
    foreach ($counts as $label => $query) {
        try {
            $stmt = $pdo->query($query);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "✅ <strong>$label:</strong> $count records<br>";
        } catch (Exception $e) {
            echo "❌ <strong>$label:</strong> " . $e->getMessage() . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error counting records: " . $e->getMessage() . "<br>";
}

// Test 4: Test API Endpoint
echo "<hr>";
echo "<h2>4. Test API Endpoint</h2>";
echo "<a href='api/admin-api.php?action=getDashboardStats' target='_blank'>Click here to test getDashboardStats API</a><br>";
echo "<a href='api/admin-api.php?action=getActivityLogs' target='_blank'>Click here to test getActivityLogs API</a><br>";

// Test 5: Check Activity Log Structure
echo "<hr>";
echo "<h2>5. Activity Log Table Structure</h2>";
try {
    $stmt = $pdo->query("DESCRIBE activity_log");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 6: Sample Activity Log Data
echo "<hr>";
echo "<h2>6. Sample Activity Log Data (Last 5)</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM activity_log ORDER BY login_time DESC LIMIT 5");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($logs) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Log ID</th><th>Username</th><th>Role</th><th>Activity Type</th><th>Login Time</th></tr>";
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td>" . $log['log_id'] . "</td>";
            echo "<td>" . $log['username'] . "</td>";
            echo "<td>" . $log['role'] . "</td>";
            echo "<td>" . $log['activity_type'] . "</td>";
            echo "<td>" . $log['login_time'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ <strong style='color:orange'>No activity logs found. Try logging in first!</strong><br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>✅ Test Complete</h2>";
echo "<p>If all tests pass but dashboard still doesn't show data, check:</p>";
echo "<ul>";
echo "<li>Browser console for JavaScript errors (F12)</li>";
echo "<li>File path: js/admin-dashboard.js is loaded correctly</li>";
echo "<li>API path: api/admin-api.php is accessible</li>";
echo "</ul>";
?>