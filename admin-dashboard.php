<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard - SIES</title>
  <link rel="shortcut icon" href="photo/logo.png" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/ea9fdbb77c.js" crossorigin="anonymous"></script>
</head>

<body class="min-h-screen font-sans bg-gray-50">
  <div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-blue-600 text-white p-6 flex flex-col">
      <div class="mb-6 text-center">
        <div class="w-16 h-16 bg-white rounded-full mx-auto mb-2 flex items-center justify-center">
          <span class="text-2xl font-bold text-blue-600">SL</span>
        </div>
        <h1 class="text-2xl font-bold">Admin Panel</h1>
      </div>

      <nav class="flex-1 space-y-2">
        <button id="dashboardBtn" onclick="showSection('dashboard')" class="nav-btn active w-full text-left px-3 py-2 rounded">
          Dashboard
        </button>

        <!-- MANAGE ACCOUNTS -->
        <button id="accountsToggle" class="w-full text-left px-3 py-2 rounded hover:bg-blue-500 flex items-center justify-between">
          <span>Manage Accounts</span>
          <span id="accountsArrow">▾</span>
        </button>
        <div id="accountsSub" class="collapsible max-h-0 pl-4 overflow-hidden transition-all duration-300">
          <button id="teacherAccountsBtn" onclick="showSection('teacherAcc')" class="nav-btn w-full text-left px-3 py-2 rounded hover:bg-blue-500">
            Teacher Accounts
          </button>
          <button id="studentAccountsBtn" onclick="showSection('studentAcc')" class="nav-btn w-full text-left px-3 py-2 rounded hover:bg-blue-500">
            Student Accounts
          </button>
        </div>

        <!-- SCHEDULES -->
        <button id="schedulesToggle" class="w-full text-left px-3 py-2 rounded hover:bg-blue-500 flex items-center justify-between">
          <span>Schedules</span>
          <span id="schedulesArrow">▾</span>
        </button>
        <div id="schedulesSub" class="collapsible max-h-0 pl-4 overflow-hidden transition-all duration-300">
          <button id="teacherSchedBtn" onclick="showSection('teacherSched')" class="nav-btn w-full text-left px-3 py-2 rounded hover:bg-blue-500">
            Manage Schedules
          </button>
        </div>

        <!-- ENROLLMENTS -->
        <button id="enrollmentsBtn" onclick="showSection('enrollments')" class="nav-btn w-full text-left px-3 py-2 rounded hover:bg-blue-500">
          Enrollments
        </button>
      </nav>

      <div class="mt-6">
        <a href="admin-logout.php" class="block w-full bg-orange-500 hover:bg-orange-600 py-2 rounded text-center">
          Logout
        </a>
      </div>
    </aside>


    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8 overflow-y-auto">
      <h2 class="text-3xl font-bold text-blue-800 mb-6">
        <i class="fa-solid fa-user-shield"></i> Welcome, Admin
      </h2>

      <!-- DASHBOARD -->
      <section id="dashboardSection">
        <h3 class="text-xl font-semibold text-blue-700 border-b-2 border-orange-500 pb-1 mb-4">Dashboard Overview</h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white border-l-4 border-blue-500 shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
            <p class="text-sm text-gray-500 uppercase">Total Teachers</p>
            <p id="totalTeachers" class="text-3xl font-bold text-blue-700">-</p>
            <p class="text-xs text-gray-400 mt-1">Registered teachers</p>
          </div>

          <div class="bg-white border-l-4 border-orange-500 shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
            <p class="text-sm text-gray-500 uppercase">Total Students</p>
            <p id="totalStudents" class="text-3xl font-bold text-orange-600">-</p>
            <p class="text-xs text-gray-400 mt-1">Registered students</p>
          </div>

          <div class="bg-white border-l-4 border-green-500 shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
            <p class="text-sm text-gray-500 uppercase">Active Schedules</p>
            <p id="totalSchedules" class="text-3xl font-bold text-green-600">-</p>
            <p class="text-xs text-gray-400 mt-1">Current schedules</p>
          </div>

          <div class="bg-white border-l-4 border-red-500 shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
            <p class="text-sm text-gray-500 uppercase">Pending Enrollments</p>
            <p id="pendingEnrollments" class="text-3xl font-bold text-red-600">-</p>
            <p class="text-xs text-gray-400 mt-1">Awaiting approval</p>
          </div>
        </div>

        <!-- Active Accounts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="bg-white shadow rounded-lg p-6">
            <h4 class="text-lg font-semibold mb-3 text-blue-700 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Active Teacher Accounts
            </h4>
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Total Accounts:</span>
                <span id="activeTeachers" class="font-bold text-blue-600 text-xl">-</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">With Login Access:</span>
                <span id="teachersWithAccounts" class="font-bold text-green-600 text-xl">-</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Without Login:</span>
                <span id="teachersWithoutAccounts" class="font-bold text-red-600 text-xl">-</span>
              </div>
            </div>
          </div>

          <div class="bg-white shadow rounded-lg p-6">
            <h4 class="text-lg font-semibold mb-3 text-orange-700 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Active Student Accounts
            </h4>
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Total Students:</span>
                <span id="activeStudents" class="font-bold text-orange-600 text-xl">-</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Enrolled:</span>
                <span id="enrolledStudents" class="font-bold text-green-600 text-xl">-</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Pending Enrollment:</span>
                <span id="pendingStudents" class="font-bold text-yellow-600 text-xl">-</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
          <h4 class="text-lg font-semibold mb-3 text-blue-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Recent Activity Logs
          </h4>
          <div class="max-h-96 overflow-y-auto">
            <ul id="activityLogs" class="divide-y divide-gray-200 text-sm text-gray-700">
              <li class="py-2 text-gray-400">Loading...</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- TEACHER ACCOUNTS -->
      <section id="teacherAccountsSection" class="hidden">
        <h3 class="text-xl font-semibold text-blue-700 border-b-2 border-orange-500 pb-1 mb-4">Teacher Account Management</h3>

        <!-- ADD FORM -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h4 class="font-semibold mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add New Teacher
          </h4>
          <form id="addTeacherForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">Teacher Name *</label>
              <input name="teacher_name" class="border border-gray-300 rounded p-2 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Maria Santos" required>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">Username *</label>
              <input name="username" class="border border-gray-300 rounded p-2 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. msantos" required>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">Password *</label>
              <input name="password" type="password" class="border border-gray-300 rounded p-2 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="••••••" required>
            </div>
            <div class="flex items-end">
              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded w-full font-semibold transition-colors">
                <span class="flex items-center justify-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add Teacher
                </span>
              </button>
            </div>
          </form>
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold mb-3 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            All Teacher Accounts
          </h4>
          <div class="overflow-x-auto">
            <table class="min-w-full text-left border-collapse">
              <thead class="bg-gradient-to-r from-blue-100 to-blue-50">
                <tr>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Teacher ID</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Name</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Username</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Status</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800 text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="teacherAccountsList">
                <tr>
                  <td colspan="5" class="px-4 py-3 text-center text-gray-400">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- STUDENT ACCOUNTS -->
      <section id="studentAccountsSection" class="hidden">
        <h3 class="text-xl font-semibold text-blue-700 border-b-2 border-orange-500 pb-1 mb-4">Student Account Management</h3>
        
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold mb-3 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            All Student Accounts
          </h4>
          <div class="overflow-x-auto">
            <table class="min-w-full text-left border-collapse">
              <thead class="bg-gradient-to-r from-blue-100 to-blue-50">
                <tr>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Student ID</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Name</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Grade Level</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Section</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800">Status</th>
                  <th class="px-4 py-3 border-b-2 border-blue-200 font-semibold text-blue-800 text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="studentAccountsList">
                <tr>
                  <td colspan="6" class="px-4 py-3 text-center text-gray-400">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- SCHEDULES -->
      <section id="teacherSchedSection" class="hidden">
        <h3 class="text-xl font-semibold text-blue-700 border-b-2 border-orange-500 pb-1 mb-4">Manage Schedules</h3>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h4 class="font-semibold mb-4">Add New Schedule</h4>
          <form id="addScheduleForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block mb-1 text-sm font-medium">Teacher *</label>
              <select name="teacher_id" id="scheduleTeacher" class="border rounded p-2 w-full" required>
                <option value="">Select Teacher</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium">Subject *</label>
              <select name="subject_code" id="scheduleSubject" class="border rounded p-2 w-full" required>
                <option value="">Select Subject</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium">Section *</label>
              <select name="section_id" id="scheduleSection" class="border rounded p-2 w-full" required>
                <option value="">Select Section</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium">Day *</label>
              <select name="day" id="scheduleDay" class="border rounded p-2 w-full" required>
                <option value="">Select Day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
              </select>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium">Time *</label>
              <input type="time" name="time" id="scheduleTime" class="border rounded p-2 w-full" required>
            </div>
            <div>
              <label class="block mb-1 text-sm font-medium">Room Number *</label>
              <input type="number" name="room_number" class="border rounded p-2 w-full" placeholder="101" required>
            </div>
            <div class="flex items-end">
              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded w-full">
                Add Schedule
              </button>
            </div>
          </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold mb-3">Existing Schedules</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-blue-100">
                <tr>
                  <th class="px-3 py-2">Teacher</th>
                  <th class="px-3 py-2">Subject</th>
                  <th class="px-3 py-2">Section</th>
                  <th class="px-3 py-2">Day & Time</th>
                  <th class="px-3 py-2">Room</th>
                  <th class="px-3 py-2 text-center">Action</th>
                </tr>
              </thead>
              <tbody id="schedulesList">
                <tr>
                  <td colspan="6" class="px-3 py-2 text-center text-gray-400">Loading schedules...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ENROLLMENTS -->
      <section id="enrollmentsSection" class="hidden">
        <h3 class="text-xl font-semibold text-blue-700 border-b-2 border-orange-500 pb-1 mb-4">Enrollment Management</h3>
        
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold mb-3">Pending Enrollments</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full text-left">
              <thead class="bg-yellow-100">
                <tr>
                  <th class="px-3 py-2">Student ID</th>
                  <th class="px-3 py-2">Student Name</th>
                  <th class="px-3 py-2">Grade Level</th>
                  <th class="px-3 py-2">Date Enrolled</th>
                  <th class="px-3 py-2">Assign Section</th>
                  <th class="px-3 py-2 text-center">Action</th>
                </tr>
              </thead>
              <tbody id="pendingEnrollmentsList">
                <tr>
                  <td colspan="6" class="px-3 py-2 text-center text-gray-400">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </main>
  </div>

  <script src="js/admin-dashboard.js"></script>

  <style>
    .nav-btn.active {
      background-color: #2563eb;
      font-weight: 600;
    }
    .nav-btn:hover {
      background-color: #3b82f6;
    }
  </style>
</body>
</html>