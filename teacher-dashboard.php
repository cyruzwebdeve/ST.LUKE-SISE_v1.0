<?php
session_start();
require_once 'db_connection.php';

// Check if teacher is logged in
requireLogin('teacher', 'teacher-login.php');

$teacherId = $_SESSION['teacher_id'];
$teacherName = $_SESSION['teacher_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Dashboard - SIES</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen font-sans bg-gray-50">
  <div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-blue-600 text-white p-6 flex flex-col justify-between shadow-xl">
      <div>
        <div class="w-16 h-16 bg-white rounded-full mx-auto mb-2 flex items-center justify-center">
          <span class="text-2xl font-bold text-blue-600">SL</span>
        </div>
        <div class="text-center mb-6 pb-4">
          <h1 class="text-2xl font-bold">Teacher Panel</h1>
        </div>
        
        <div class="text-center mb-6 bg-white bg-opacity-10 rounded-lg p-4">
          <p class="text-sm font-medium opacity-90 mb-1">Welcome,</p>
          <p class="text-lg font-bold" id="teacherName"><?php echo htmlspecialchars($teacherName); ?></p>
          <p class="text-xs opacity-75 mt-1"><?php echo htmlspecialchars($teacherId); ?></p>
        </div>
        
        <nav class="space-y-2">
          <button class="nav-btn w-full text-left px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white hover:bg-opacity-10 bg-white bg-opacity-20 font-semibold" 
                  onclick="showSection('gradeEncode', this)">
            <span class="inline-block mr-2"></span> Encode Grades
          </button>
          <button class="nav-btn w-full text-left px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white hover:bg-opacity-10" 
                  onclick="showSection('viewGrades', this)">
            <span class="inline-block mr-2"></span> View Grades
          </button>
          <button class="nav-btn w-full text-left px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white hover:bg-opacity-10" 
                  onclick="showSection('mySchedule', this)">
            <span class="inline-block mr-2"></span> My Schedule
          </button>
        </nav>
      </div>

      <div class="mt-6">
        <a href="teacher-logout.php" 
           class="block w-full bg-orange-500 hover:bg-orange-600 py-3 rounded-lg text-center font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
          Logout
        </a>
      </div>
    </aside>


    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8 overflow-y-auto">
      <h2 class="text-4xl font-bold text-blue-700 mb-8">Grade Management</h2>

      <!-- GRADE ENCODING -->
      <section id="gradeEncode" class="section">
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
          <div class="border-b-2 border-blue-600 pb-3 mb-6">
            <h3 class="text-xl font-bold text-blue-700">Grade Encoding</h3>
          </div>

          <!-- FILTERS -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Grade Level</label>
              <select id="gradeFilter" 
                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
                <option value="">Select Grade Level</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Section</label>
              <select id="sectionFilter" 
                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
                <option value="">Select Section</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
              <select id="subjectFilter" 
                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
                <option value="">Select Subject</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Grading Period</label>
              <select id="periodFilter" 
                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
                <option value="1st">1st Quarter</option>
                <option value="2nd">2nd Quarter</option>
                <option value="3rd">3rd Quarter</option>
                <option value="4th">4th Quarter</option>
              </select>
            </div>

            <div class="flex items-end">
              <button onclick="filterStudents()" 
                      class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md">
                Show Students
              </button>
            </div>
          </div>
        </div>

        <!-- STUDENT LIST -->
        <div id="studentTableContainer" class="hidden bg-white rounded-xl shadow-lg p-6">
          <h4 class="text-xl font-bold text-blue-700 mb-4">Student List</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-left">
              <thead class="bg-blue-100 text-blue-800">
                <tr>
                  <th class="px-4 py-3 border font-semibold">Student ID</th>
                  <th class="px-4 py-3 border font-semibold">Student Name</th>
                  <th class="px-4 py-3 border font-semibold text-center">Current Grade</th>
                  <th class="px-4 py-3 border font-semibold">New Grade</th>
                  <th class="px-4 py-3 border font-semibold text-center">Action</th>
                </tr>
              </thead>
              <tbody id="studentTable" class="text-gray-700"></tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- VIEW GRADES -->
      <section id="viewGrades" class="section hidden">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="border-b-2 border-blue-600 pb-3 mb-6">
            <h3 class="text-xl font-bold text-blue-700">View Student Grades</h3>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Section</label>
              <select id="viewSectionFilter" 
                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
                <option value="">Select Section</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
              <select id="viewSubjectFilter" 
                      class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
                <option value="">Select Subject</option>
              </select>
            </div>
            <div class="flex items-end">
              <button onclick="viewGradesTable()" 
                      class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md">
                View Grades
              </button>
            </div>
          </div>

          <div id="gradesTableContainer" class="hidden overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-left text-sm">
              <thead class="bg-blue-100 text-blue-800">
                <tr>
                  <th class="px-3 py-3 border font-semibold">Student Name</th>
                  <th class="px-3 py-3 border font-semibold text-center">1st Quarter</th>
                  <th class="px-3 py-3 border font-semibold text-center">2nd Quarter</th>
                  <th class="px-3 py-3 border font-semibold text-center">3rd Quarter</th>
                  <th class="px-3 py-3 border font-semibold text-center">4th Quarter</th>
                  <th class="px-3 py-3 border font-semibold text-center">Average</th>
                </tr>
              </thead>
              <tbody id="gradesTableBody" class="text-gray-700"></tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- MY SCHEDULE -->
      <section id="mySchedule" class="section hidden">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="border-b-2 border-blue-600 pb-3 mb-6">
            <h3 class="text-xl font-bold text-blue-700">My Teaching Schedule</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-left">
              <thead class="bg-blue-100 text-blue-800">
                <tr>
                  <th class="px-4 py-3 border font-semibold">Subject</th>
                  <th class="px-4 py-3 border font-semibold">Section</th>
                  <th class="px-4 py-3 border font-semibold">Day & Time</th>
                  <th class="px-4 py-3 border font-semibold">Room</th>
                </tr>
              </thead>
              <tbody id="scheduleTableBody" class="text-gray-700">
                <tr>
                  <td colspan="4" class="px-4 py-3 text-center text-gray-400">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </main>
  </div>

  <script>
    const teacherId = '<?php echo $teacherId; ?>';
    
    // Section switching with Tailwind active state
    function showSection(id, btn) {
      document.querySelectorAll("main section").forEach(sec => sec.classList.add("hidden"));
      document.getElementById(id).classList.remove("hidden");

      document.querySelectorAll(".nav-btn").forEach(b => {
        b.classList.remove("bg-white", "bg-opacity-20", "font-semibold");
        b.classList.add("hover:bg-white", "hover:bg-opacity-10");
      });
      
      if (btn) {
        btn.classList.add("bg-white", "bg-opacity-20", "font-semibold");
        btn.classList.remove("hover:bg-white", "hover:bg-opacity-10");
      }

      // Load data for specific sections
      if (id === 'mySchedule') {
        loadMySchedule();
      } else if (id === 'gradeEncode') {
        loadTeacherSections();
        loadTeacherSubjects();
      } else if (id === 'viewGrades') {
        loadViewGradeDropdowns();
      }
    }

    // Load on page load
    document.addEventListener('DOMContentLoaded', () => {
      loadTeacherSections();
      loadTeacherSubjects();
    });
  </script>
  <script src="js/teacher-dashboard.js"></script>
</body>
</html>