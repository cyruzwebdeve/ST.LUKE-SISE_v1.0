<?php
require_once '../config.php';
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get database connection
try {
    $pdo = getDBConnection();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    // ========================================
    // DASHBOARD STATISTICS
    // ========================================
    case 'getDashboardStats':
        try {
            $stats = [];
            
            // Total Teachers
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM teacher");
            $stats['totalTeachers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Teachers with accounts
            $stmt = $pdo->query("
                SELECT COUNT(*) as count 
                FROM teacher t 
                INNER JOIN user_account u ON t.teacher_id = u.teacher_id
            ");
            $stats['teachersWithAccounts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Teachers without accounts
            $stats['teachersWithoutAccounts'] = $stats['totalTeachers'] - $stats['teachersWithAccounts'];
            
            // Total Students
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM student");
            $stats['totalStudents'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Enrolled Students
            $stmt = $pdo->query("
                SELECT COUNT(*) as count 
                FROM enrollment 
                WHERE enrollment_status = 'enrolled'
            ");
            $stats['enrolledStudents'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Active Schedules
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM schedule");
            $stats['totalSchedules'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Pending Enrollments
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM enrollment WHERE enrollment_status = 'pending'");
            $stats['pendingEnrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'getActivityLogs':
        try {
            // Check if activity_log table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'activity_log'");
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                // Get recent login activities from activity_log table
                $stmt = $pdo->query("
                    SELECT 
                        al.username,
                        al.role,
                        al.login_time,
                        al.activity_type,
                        al.activity_description,
                        CASE 
                            WHEN al.role = 'teacher' THEN t.teacher_name
                            WHEN al.role = 'student' THEN s.student_name
                            WHEN al.role = 'admin' THEN 'Administrator'
                            ELSE al.username
                        END as name
                    FROM activity_log al
                    LEFT JOIN user_account u ON al.user_id = u.user_id
                    LEFT JOIN teacher t ON u.teacher_id = t.teacher_id
                    LEFT JOIN student s ON u.student_id = s.student_id
                    WHERE al.role IN ('teacher', 'student', 'admin')
                    ORDER BY al.login_time DESC
                    LIMIT 50
                ");
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Fallback: use user_account creation dates
                $logs = [];
            }
            
            // If no logs, fallback to user_account creation dates
            if (empty($logs)) {
                $stmt = $pdo->query("
                    SELECT 
                        u.username,
                        u.role,
                        u.date_created as login_time,
                        'account_created' as activity_type,
                        'Account created' as activity_description,
                        CASE 
                            WHEN u.role = 'teacher' THEN t.teacher_name
                            WHEN u.role = 'student' THEN s.student_name
                            ELSE 'Admin'
                        END as name
                    FROM user_account u
                    LEFT JOIN teacher t ON u.teacher_id = t.teacher_id
                    LEFT JOIN student s ON u.student_id = s.student_id
                    WHERE u.role IN ('teacher', 'student')
                    ORDER BY u.date_created DESC
                    LIMIT 20
                ");
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode(['success' => true, 'logs' => $logs]);
        } catch(PDOException $e) {
            // Return empty logs instead of error
            echo json_encode(['success' => true, 'logs' => []]);
        }
        break;

    // ========================================
    // TEACHER ACCOUNTS
    // ========================================
    case 'getTeacherAccounts':
        try {
            $stmt = $pdo->query("
                SELECT t.*, u.username 
                FROM teacher t
                LEFT JOIN user_account u ON t.teacher_id = u.teacher_id
                ORDER BY t.teacher_id
            ");
            $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'teachers' => $teachers]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'addTeacher':
        try {
            $teacherName = $_POST['teacher_name'];
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Generate teacher ID
            $stmt = $pdo->query("SELECT teacher_id FROM teacher ORDER BY teacher_id DESC LIMIT 1");
            $lastTeacher = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lastTeacher) {
                $lastNum = (int)substr($lastTeacher['teacher_id'], 1);
                $newNum = $lastNum + 1;
            } else {
                $newNum = 1;
            }
            $teacherId = 'T' . str_pad($newNum, 3, '0', STR_PAD_LEFT);
            
            // Insert teacher
            $stmt = $pdo->prepare("INSERT INTO teacher (teacher_id, teacher_name) VALUES (?, ?)");
            $stmt->execute([$teacherId, $teacherName]);
            
            // Create user account
            $stmt = $pdo->prepare("
                INSERT INTO user_account (username, password, role, teacher_id) 
                VALUES (?, ?, 'teacher', ?)
            ");
            $stmt->execute([$username, $password, $teacherId]);
            
            echo json_encode(['success' => true, 'message' => 'Teacher added successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'getTeacherDetails':
        try {
            $teacherId = $_GET['teacher_id'];
            $stmt = $pdo->prepare("
                SELECT t.*, u.username,
                    (SELECT COUNT(*) FROM subject WHERE teacher_id = t.teacher_id) as subject_count,
                    (SELECT COUNT(*) FROM schedule WHERE teacher_id = t.teacher_id) as schedule_count
                FROM teacher t
                LEFT JOIN user_account u ON t.teacher_id = u.teacher_id
                WHERE t.teacher_id = ?
            ");
            $stmt->execute([$teacherId]);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'teacher' => $teacher]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'updateTeacher':
        try {
            $teacherId = $_POST['teacher_id'];
            $teacherName = $_POST['teacher_name'];
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Update teacher name
            $stmt = $pdo->prepare("UPDATE teacher SET teacher_name = ? WHERE teacher_id = ?");
            $stmt->execute([$teacherName, $teacherId]);
            
            // Update username if provided
            if (!empty($username)) {
                // Check if user account exists
                $stmt = $pdo->prepare("SELECT user_id FROM user_account WHERE teacher_id = ?");
                $stmt->execute([$teacherId]);
                $userExists = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userExists) {
                    // Update existing account
                    if (!empty($password)) {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE user_account SET username = ?, password = ? WHERE teacher_id = ?");
                        $stmt->execute([$username, $hashedPassword, $teacherId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE user_account SET username = ? WHERE teacher_id = ?");
                        $stmt->execute([$username, $teacherId]);
                    }
                } else {
                    // Create new account if it doesn't exist
                    if (!empty($password)) {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            INSERT INTO user_account (username, password, role, teacher_id) 
                            VALUES (?, ?, 'teacher', ?)
                        ");
                        $stmt->execute([$username, $hashedPassword, $teacherId]);
                    }
                }
            } elseif (!empty($password)) {
                // Only update password if username not provided
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE user_account SET password = ? WHERE teacher_id = ?");
                $stmt->execute([$hashedPassword, $teacherId]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Teacher updated successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'deleteTeacher':
        try {
            $teacherId = $_POST['teacher_id'];
            
            // Delete user account first
            $stmt = $pdo->prepare("DELETE FROM user_account WHERE teacher_id = ?");
            $stmt->execute([$teacherId]);
            
            // Update subjects to remove teacher assignment
            $stmt = $pdo->prepare("UPDATE subject SET teacher_id = NULL WHERE teacher_id = ?");
            $stmt->execute([$teacherId]);
            
            // Delete schedules
            $stmt = $pdo->prepare("DELETE FROM schedule WHERE teacher_id = ?");
            $stmt->execute([$teacherId]);
            
            // Delete teacher
            $stmt = $pdo->prepare("DELETE FROM teacher WHERE teacher_id = ?");
            $stmt->execute([$teacherId]);
            
            echo json_encode(['success' => true, 'message' => 'Teacher deleted successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // ========================================
    // STUDENT ACCOUNTS
    // ========================================
    case 'getStudentAccounts':
        try {
            $stmt = $pdo->query("
                SELECT s.*, e.enrollment_status, sec.section_name
                FROM student s
                LEFT JOIN enrollment e ON s.student_id = e.student_id
                LEFT JOIN section sec ON e.section_id = sec.section_id
                ORDER BY s.student_id DESC
            ");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'students' => $students]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'getStudentDetails':
        try {
            $studentId = $_GET['student_id'];
            $stmt = $pdo->prepare("
                SELECT s.*, e.enrollment_status, sec.section_name
                FROM student s
                LEFT JOIN enrollment e ON s.student_id = e.student_id
                LEFT JOIN section sec ON e.section_id = sec.section_id
                WHERE s.student_id = ?
            ");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'student' => $student]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'updateStudent':
        try {
            $studentId = $_POST['student_id'];
            $studentName = $_POST['student_name'];
            $gradeLevel = $_POST['grade_level'];
            $gender = $_POST['gender'] ?? null;
            $birthdate = $_POST['birthdate'] ?? null;
            $religion = $_POST['religion'] ?? null;
            $address = $_POST['address'] ?? null;
            $contactNumber = $_POST['contact_number'] ?? null;
            
            $stmt = $pdo->prepare("
                UPDATE student SET 
                    student_name = ?,
                    grade_level = ?,
                    gender = ?,
                    birthdate = ?,
                    religion = ?,
                    address = ?,
                    contact_number = ?
                WHERE student_id = ?
            ");
            $stmt->execute([
                $studentName, 
                $gradeLevel, 
                $gender, 
                $birthdate ?: null, 
                $religion, 
                $address, 
                $contactNumber, 
                $studentId
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'deleteStudent':
        try {
            $studentId = $_POST['student_id'];
            
            // Delete related records first
            $stmt = $pdo->prepare("DELETE FROM grade WHERE student_id = ?");
            $stmt->execute([$studentId]);
            
            $stmt = $pdo->prepare("DELETE FROM enrollment WHERE student_id = ?");
            $stmt->execute([$studentId]);
            
            $stmt = $pdo->prepare("DELETE FROM user_account WHERE student_id = ?");
            $stmt->execute([$studentId]);
            
            // Delete student
            $stmt = $pdo->prepare("DELETE FROM student WHERE student_id = ?");
            $stmt->execute([$studentId]);
            
            echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // ========================================
    // SCHEDULE MANAGEMENT
    // ========================================
    case 'getSchedules':
        try {
            $stmt = $pdo->query("
                SELECT 
                    sch.*,
                    t.teacher_name,
                    sub.subject_name,
                    sec.section_name
                FROM schedule sch
                INNER JOIN teacher t ON sch.teacher_id = t.teacher_id
                INNER JOIN subject sub ON sch.subject_code = sub.subject_code
                INNER JOIN section sec ON sch.section_id = sec.section_id
                ORDER BY sch.day_time, sec.section_name
            ");
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'schedules' => $schedules]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'getSubjects':
        try {
            $stmt = $pdo->query("SELECT * FROM subject ORDER BY subject_name");
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'subjects' => $subjects]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'getSections':
        try {
            $stmt = $pdo->query("SELECT * FROM section ORDER BY grade_level, section_name");
            $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'sections' => $sections]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'addSchedule':
        try {
            $teacherId = $_POST['teacher_id'];
            $subjectCode = $_POST['subject_code'];
            $sectionId = $_POST['section_id'];
            $day = $_POST['day'];
            $time = $_POST['time'];
            $roomNumber = $_POST['room_number'];
            
            // Create datetime from day and time
            // Find next occurrence of the specified day
            $dayOfWeek = date('N', strtotime($day)); // 1 (Monday) through 7 (Sunday)
            $currentDayOfWeek = date('N');
            $daysUntilNext = ($dayOfWeek - $currentDayOfWeek + 7) % 7;
            if ($daysUntilNext == 0) $daysUntilNext = 7; // If today, schedule for next week
            
            $nextDate = date('Y-m-d', strtotime("+$daysUntilNext days"));
            $datetime = $nextDate . ' ' . $time;
            
            // Check for conflicts
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM schedule 
                WHERE section_id = ? 
                AND DATE_FORMAT(day_time, '%Y-%m-%d %H:%i') = ?
            ");
            $stmt->execute([$sectionId, $datetime]);
            $conflict = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($conflict['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Schedule conflict detected for this section!']);
                break;
            }
            
            // Insert schedule
            $stmt = $pdo->prepare("
                INSERT INTO schedule (day_time, room_number, subject_code, section_id, teacher_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$datetime, $roomNumber, $subjectCode, $sectionId, $teacherId]);
            
            echo json_encode(['success' => true, 'message' => 'Schedule added successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'deleteSchedule':
        try {
            $scheduleId = $_POST['schedule_id'];
            $stmt = $pdo->prepare("DELETE FROM schedule WHERE schedule_id = ?");
            $stmt->execute([$scheduleId]);
            echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // ========================================
    // ENROLLMENT MANAGEMENT
    // ========================================
    case 'getPendingEnrollments':
        try {
            // Get pending enrollments
            $stmt = $pdo->query("
                SELECT e.*, s.student_name, s.grade_level
                FROM enrollment e
                INNER JOIN student s ON e.student_id = s.student_id
                WHERE e.enrollment_status = 'pending'
                ORDER BY e.date_enrolled DESC
            ");
            $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get all sections
            $stmt = $pdo->query("SELECT * FROM section ORDER BY grade_level, section_name");
            $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'enrollments' => $enrollments,
                'sections' => $sections
            ]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'approveEnrollment':
        try {
            $enrollmentId = $_POST['enrollment_id'];
            $sectionId = $_POST['section_id'];
            
            $stmt = $pdo->prepare("
                UPDATE enrollment 
                SET enrollment_status = 'enrolled', section_id = ?
                WHERE enrollment_id = ?
            ");
            $stmt->execute([$sectionId, $enrollmentId]);
            
            echo json_encode(['success' => true, 'message' => 'Enrollment approved successfully']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>