<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'enrollment_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $studentId = $_SESSION['student_id'];
    
    // Fetch student information from student table
    $stmt = $pdo->prepare("SELECT * FROM student WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch enrollment information
    $stmt = $pdo->prepare("
        SELECT e.*, s.section_name 
        FROM enrollment e
        LEFT JOIN section s ON e.section_id = s.section_id
        WHERE e.student_id = ?
        ORDER BY e.date_enrolled DESC
        LIMIT 1
    ");
    $stmt->execute([$studentId]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch schedule using original schema structure
    $schedules = [];
    if ($enrollment && $enrollment['section_id']) {
        $stmt = $pdo->prepare("
            SELECT sch.*, sub.subject_name, t.teacher_name
            FROM schedule sch
            INNER JOIN subject sub ON sch.subject_code = sub.subject_code
            INNER JOIN teacher t ON sch.teacher_id = t.teacher_id
            WHERE sch.section_id = ?
            ORDER BY sch.day_time
        ");
        $stmt->execute([$enrollment['section_id']]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fetch grades using grading_period structure (1st, 2nd, 3rd, 4th quarters)
    $stmt = $pdo->prepare("
        SELECT g.*, s.subject_name
        FROM grade g
        INNER JOIN subject s ON g.subject_code = s.subject_code
        WHERE g.student_id = ?
        ORDER BY 
            FIELD(g.grading_period, '1st', '2nd', '3rd', '4th'),
            s.subject_name
    ");
    $stmt->execute([$studentId]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group grades by quarter
    $gradesByQuarter = [];
    foreach ($grades as $grade) {
        $quarter = $grade['grading_period'];
        if (!isset($gradesByQuarter[$quarter])) {
            $gradesByQuarter[$quarter] = [];
        }
        $gradesByQuarter[$quarter][] = $grade;
    }
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Dashboard - SIES</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen font-sans bg-gray-50">
    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-blue-600 text-white p-6 flex flex-col">
        <div class="w-16 h-16 bg-white rounded-full mx-auto mb-2 flex items-center justify-center">
          <span class="text-2xl font-bold text-blue-600">SL</span>
        </div>
            
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold">Student Portal</h1>
                <p class="text-sm text-blue-200 mt-2"><?php echo htmlspecialchars($student['student_id']); ?></p>
            </div>

            <nav class="flex-1 space-y-2">
                <button id="dashboardBtn" onclick="showSection('dashboard')" class="w-full text-left px-3 py-2 rounded hover:bg-blue-500 bg-blue-500">Dashboard</button>
                <button id="scheduleBtn" onclick="showSection('schedule')" class="w-full text-left px-3 py-2 rounded hover:bg-blue-500">Schedule</button>
                <button id="gradesBtn" onclick="showSection('grades')" class="w-full text-left px-3 py-2 rounded hover:bg-blue-500">Grades</button>
            </nav>

            <div class="mt-6">
                <a href="logout.php" class="block w-full bg-orange-500 hover:bg-orange-600 py-2 rounded text-center">Logout</a>
            </div>
        </aside>


        <!-- MAIN CONTENT -->
        <main class="flex-1 p-8 overflow-y-auto">
            <h2 class="text-3xl font-bold text-blue-800 mb-6">Welcome, <?php echo htmlspecialchars($student['student_name']); ?>!</h2>

            <!-- DASHBOARD -->
            <section id="dashboardSection">
                <h3 class="text-xl font-semibold text-blue-700 border-b-2 border-orange-500 pb-1 mb-4">Dashboard Overview</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white border-l-4 border-blue-500 shadow rounded-lg p-6">
                        <p class="text-sm text-gray-500 uppercase">Grade Level</p>
                        <p class="text-3xl font-bold text-blue-700 mt-2"><?php echo htmlspecialchars($student['grade_level']); ?></p>
                    </div>

                    <div class="bg-white border-l-4 border-orange-500 shadow rounded-lg p-6">
                        <p class="text-sm text-gray-500 uppercase">Section</p>
                        <p class="text-2xl font-bold text-orange-600 mt-2">
                            <?php echo $enrollment && $enrollment['section_name'] ? htmlspecialchars($enrollment['section_name']) : 'Not Assigned'; ?>
                        </p>
                    </div>

                    <div class="bg-white border-l-4 border-blue-400 shadow rounded-lg p-6">
                        <p class="text-sm text-gray-500 uppercase">Status</p>
                        <p class="text-xl font-bold text-blue-600 mt-2">
                            <?php echo $enrollment ? ucfirst(htmlspecialchars($enrollment['enrollment_status'])) : 'Not Enrolled'; ?>
                        </p>
                    </div>
                </div>

                <!-- Student Information Card -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Student Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Full Name</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($student['student_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Gender</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($student['gender']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Birthdate</p>
                            <p class="font-semibold text-gray-800">
                                <?php echo $student['birthdate'] ? date('F d, Y', strtotime($student['birthdate'])) : 'N/A'; ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Contact Number</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($student['contact_number']); ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Address</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($student['address']); ?></p>
                        </div>
                    </div>
                </div>

                <?php if ($enrollment): ?>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <p class="font-semibold text-blue-800">Enrollment Date: 
                        <?php echo date('F d, Y', strtotime($enrollment['date_enrolled'])); ?>
                    </p>
                </div>
                <?php endif; ?>
            </section>


            <!-- SCHEDULE -->
            <section id="scheduleSection" class="hidden">
                <h3 class="text-xl font-semibold text-blue-700 border-b-2 border-orange-500 pb-1 mb-4">Class Schedule</h3>

                <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
                    <?php if (count($schedules) > 0): ?>
                    <table class="min-w-full text-left border border-gray-200">
                        <thead class="bg-blue-100 text-blue-800">
                            <tr>
                                <th class="px-4 py-2 border">Subject</th>
                                <th class="px-4 py-2 border">Day & Time</th>
                                <th class="px-4 py-2 border">Room</th>
                                <th class="px-4 py-2 border">Teacher</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php foreach ($schedules as $schedule): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border font-semibold">
                                    <?php echo htmlspecialchars($schedule['subject_name']); ?>
                                </td>
                                <td class="px-4 py-2 border">
                                    <?php 
                                    $datetime = new DateTime($schedule['day_time']);
                                    echo $datetime->format('l, g:i A'); 
                                    ?>
                                </td>
                                <td class="px-4 py-2 border">
                                    Room <?php echo htmlspecialchars($schedule['room_number']); ?>
                                </td>
                                <td class="px-4 py-2 border">
                                    <?php echo htmlspecialchars($schedule['teacher_name']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-gray-600 text-center py-8">
                        <?php if (!$enrollment || !$enrollment['section_id']): ?>
                        No schedule available. Section not yet assigned.
                        <?php else: ?>
                        No schedule available for your section yet.
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </section>


            <!-- GRADES -->
            <section id="gradesSection" class="hidden">
                <h3 class="text-xl font-semibold text-blue-700 border-b-2 border-orange-500 pb-1 mb-4">Grades</h3>

                <!-- Filter -->
                <div class="flex items-center justify-between mb-4">
                    <label for="quarterSelect" class="font-medium text-gray-700">Select Quarter:</label>
                    <select id="quarterSelect" onchange="filterGrades()" class="border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-400">
                        <option value="all">All Quarters</option>
                        <option value="1st">1st Quarter</option>
                        <option value="2nd">2nd Quarter</option>
                        <option value="3rd">3rd Quarter</option>
                        <option value="4th">4th Quarter</option>
                    </select>
                </div>

                <?php if (count($grades) > 0): ?>
                    <?php foreach ($gradesByQuarter as $quarter => $quarterGrades): ?>
                    <div class="grade-quarter bg-white rounded-lg shadow p-6 mb-6" data-quarter="<?php echo $quarter; ?>">
                        <h4 class="text-lg font-semibold text-blue-700 mb-4"><?php echo htmlspecialchars($quarter); ?> Quarter</h4>
                        
                        <table class="min-w-full text-left border border-gray-200">
                            <thead class="bg-blue-100 text-blue-800">
                                <tr>
                                    <th class="px-4 py-2 border">Subject</th>
                                    <th class="px-4 py-2 border">Grade</th>
                                    <th class="px-4 py-2 border">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <?php 
                                $totalGrade = 0;
                                $subjectCount = 0;
                                foreach ($quarterGrades as $grade): 
                                    $totalGrade += $grade['grade_score'];
                                    $subjectCount++;
                                    $remarks = $grade['grade_score'] >= 75 ? 'Passed' : 'Failed';
                                    $remarksClass = $grade['grade_score'] >= 75 ? 'text-green-600' : 'text-red-600';
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border"><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                    <td class="px-4 py-2 border font-semibold"><?php echo number_format($grade['grade_score'], 2); ?></td>
                                    <td class="px-4 py-2 border font-semibold <?php echo $remarksClass; ?>">
                                        <?php echo $remarks; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="bg-blue-50 font-semibold">
                                    <td class="px-4 py-2 border">Average</td>
                                    <td class="px-4 py-2 border text-blue-700">
                                        <?php echo number_format($totalGrade / $subjectCount, 2); ?>
                                    </td>
                                    <td class="px-4 py-2 border"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <p class="text-gray-600">No grades available yet.</p>
                </div>
                <?php endif; ?>
            </section>

        </main>
    </div>

    <!-- JS FOR TAB SWITCHING -->
    <script>
        function showSection(sectionName) {
            // Hide all sections
            document.getElementById('dashboardSection').classList.add('hidden');
            document.getElementById('scheduleSection').classList.add('hidden');
            document.getElementById('gradesSection').classList.add('hidden');

            // Remove active class from all buttons
            document.getElementById('dashboardBtn').classList.remove('bg-blue-500');
            document.getElementById('scheduleBtn').classList.remove('bg-blue-500');
            document.getElementById('gradesBtn').classList.remove('bg-blue-500');

            // Show selected section
            document.getElementById(sectionName + 'Section').classList.remove('hidden');
            document.getElementById(sectionName + 'Btn').classList.add('bg-blue-500');
        }

        function filterGrades() {
            const selectedQuarter = document.getElementById('quarterSelect').value;
            const allQuarters = document.querySelectorAll('.grade-quarter');
            
            allQuarters.forEach(quarter => {
                if (selectedQuarter === 'all') {
                    quarter.style.display = 'block';
                } else if (quarter.getAttribute('data-quarter') === selectedQuarter) {
                    quarter.style.display = 'block';
                } else {
                    quarter.style.display = 'none';
                }
            });
        }

        // Default tab
        window.addEventListener('DOMContentLoaded', () => {
            showSection('dashboard');
        });
    </script>
</body>
</html>