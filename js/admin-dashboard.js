// ========================================
// ADMIN DASHBOARD - FULL CRUD OPERATIONS
// ========================================

// Section Navigation
function showSection(sectionName) {
  console.log('Switching to section:', sectionName);
  
  // Hide all sections
  document.querySelectorAll('main section').forEach(sec => sec.classList.add('hidden'));
  
  // Remove active state from all nav buttons
  document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Show selected section
  const section = document.getElementById(sectionName + 'Section');
  if (section) {
    section.classList.remove('hidden');
    console.log('Section shown:', sectionName + 'Section');
  } else {
    console.error('Section not found:', sectionName + 'Section');
  }
  
  // Set active button
  const activeBtn = document.getElementById(sectionName + 'Btn');
  if (activeBtn) {
    activeBtn.classList.add('active');
  }
  
  // Load data for specific sections
  switch(sectionName) {
    case 'dashboard':
      loadDashboardStats();
      break;
    case 'teacherAcc':
      loadTeacherAccounts();
      break;
    case 'studentAcc':
      loadStudentAccounts();
      break;
    case 'teacherSched':
      console.log('Loading schedules section...');
      loadSchedules();
      loadScheduleDropdowns();
      break;
    case 'enrollments':
      loadPendingEnrollments();
      break;
  }
}

// Collapsible Menu Toggle
document.getElementById('accountsToggle').addEventListener('click', function() {
  const sub = document.getElementById('accountsSub');
  const arrow = document.getElementById('accountsArrow');
  
  if (sub.style.maxHeight && sub.style.maxHeight !== '0px') {
    sub.style.maxHeight = '0px';
    arrow.textContent = '▾';
  } else {
    sub.style.maxHeight = sub.scrollHeight + 'px';
    arrow.textContent = '▴';
  }
});

document.getElementById('schedulesToggle').addEventListener('click', function() {
  const sub = document.getElementById('schedulesSub');
  const arrow = document.getElementById('schedulesArrow');
  
  if (sub.style.maxHeight && sub.style.maxHeight !== '0px') {
    sub.style.maxHeight = '0px';
    arrow.textContent = '▾';
  } else {
    sub.style.maxHeight = sub.scrollHeight + 'px';
    arrow.textContent = '▴';
  }
});

// ========================================
// DASHBOARD STATISTICS
// ========================================
function loadDashboardStats() {
  console.log('Loading dashboard stats...');
  
  fetch('api/admin-api.php?action=getDashboardStats')
    .then(res => {
      console.log('Response status:', res.status);
      return res.json();
    })
    .then(data => {
      console.log('Dashboard stats received:', data);
      
      if (data.success) {
        // Main stats
        document.getElementById('totalTeachers').textContent = data.stats.totalTeachers || '0';
        document.getElementById('totalStudents').textContent = data.stats.totalStudents || '0';
        document.getElementById('totalSchedules').textContent = data.stats.totalSchedules || '0';
        document.getElementById('pendingEnrollments').textContent = data.stats.pendingEnrollments || '0';
        
        // Active accounts - Teachers
        document.getElementById('activeTeachers').textContent = data.stats.totalTeachers || '0';
        document.getElementById('teachersWithAccounts').textContent = data.stats.teachersWithAccounts || '0';
        document.getElementById('teachersWithoutAccounts').textContent = data.stats.teachersWithoutAccounts || '0';
        
        // Active accounts - Students
        document.getElementById('activeStudents').textContent = data.stats.totalStudents || '0';
        document.getElementById('enrolledStudents').textContent = data.stats.enrolledStudents || '0';
        document.getElementById('pendingStudents').textContent = data.stats.pendingEnrollments || '0';
        
        console.log('Dashboard stats updated successfully');
      } else {
        console.error('API returned error:', data.message);
      }
    })
    .catch(err => {
      console.error('Error loading dashboard stats:', err);
    });
  
  loadActivityLogs();
}

function loadActivityLogs() {
  console.log('Loading activity logs...');
  
  fetch('api/admin-api.php?action=getActivityLogs')
    .then(res => {
      console.log('Activity logs response status:', res.status);
      return res.json();
    })
    .then(data => {
      console.log('Activity logs received:', data);
      
      const logsList = document.getElementById('activityLogs');
      
      if (data.success && data.logs && data.logs.length > 0) {
        logsList.innerHTML = data.logs.map(log => {
          const timeAgo = getTimeAgo(log.login_time);
          const iconColor = log.role === 'teacher' ? 'text-blue-600' : 
                           log.role === 'student' ? 'text-orange-600' : 'text-gray-600';
          const icon = log.role === 'teacher' ? 
            `<svg class="w-4 h-4 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>` :
            `<svg class="w-4 h-4 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>`;
          
          return `
            <li class="py-3 hover:bg-gray-50 px-2 rounded transition-colors">
              <div class="flex items-start">
                <div class="flex-shrink-0 mt-1">${icon}</div>
                <div class="ml-3 flex-1">
                  <p class="text-sm font-medium text-gray-900">
                    ${log.name || log.username} <span class="text-gray-500 font-normal">(${log.username})</span>
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                      log.role === 'teacher' ? 'bg-blue-100 text-blue-800' : 
                      log.role === 'student' ? 'bg-orange-100 text-orange-800' : 
                      'bg-gray-100 text-gray-800'
                    }">
                      ${log.role === 'teacher' ? 'Teacher' : log.role === 'student' ? 'Student' : 'Admin'}
                    </span>
                    <span class="ml-2">${timeAgo}</span>
                  </p>
                </div>
                <div class="text-xs text-gray-400 mt-1">
                  ${new Date(log.login_time).toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                  })}
                </div>
              </div>
            </li>
          `;
        }).join('');
        
        console.log('Activity logs rendered successfully');
      } else {
        logsList.innerHTML = `
          <li class="py-8 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <p>No recent activity</p>
          </li>
        `;
        console.log('No activity logs found');
      }
    })
    .catch(err => {
      console.error('Error loading activity logs:', err);
      document.getElementById('activityLogs').innerHTML = 
        '<li class="py-2 text-red-500">Error loading activity logs. Check console for details.</li>';
    });
}

// Helper function to get time ago string
function getTimeAgo(datetime) {
  const now = new Date();
  const loginTime = new Date(datetime);
  const diffMs = now - loginTime;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
  if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
  if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
  return loginTime.toLocaleDateString();
}

// ========================================
// TEACHER ACCOUNTS - FULL CRUD
// ========================================
function loadTeacherAccounts() {
  fetch('api/admin-api.php?action=getTeacherAccounts')
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('teacherAccountsList');
      if (data.success && data.teachers.length > 0) {
        tbody.innerHTML = data.teachers.map(teacher => `
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-2 border">${teacher.teacher_id}</td>
            <td class="px-3 py-2 border">${teacher.teacher_name}</td>
            <td class="px-3 py-2 border">${teacher.username || 'N/A'}</td>
            <td class="px-3 py-2 border">
              <span class="px-2 py-1 text-xs rounded ${teacher.username ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                ${teacher.username ? 'Active' : 'No Account'}
              </span>
            </td>
            <td class="px-3 py-2 border text-center">
              <button onclick="viewTeacher('${teacher.teacher_id}')" 
                      class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm mr-1">
                View
              </button>
              <button onclick="editTeacher('${teacher.teacher_id}')" 
                      class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm mr-1">
                Edit
              </button>
              <button onclick="deleteTeacher('${teacher.teacher_id}')" 
                      class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                Delete
              </button>
            </td>
          </tr>
        `).join('');
      } else {
        tbody.innerHTML = '<tr><td colspan="5" class="px-3 py-2 text-center text-gray-400">No teacher accounts found</td></tr>';
      }
    })
    .catch(err => console.error('Error loading teachers:', err));
}

// Add Teacher Form Submit
document.getElementById('addTeacherForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('action', 'addTeacher');
  
  fetch('api/admin-api.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Teacher added successfully!');
      this.reset();
      loadTeacherAccounts();
      loadDashboardStats();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error('Error adding teacher:', err));
});

// View Teacher Details
function viewTeacher(teacherId) {
  fetch(`api/admin-api.php?action=getTeacherDetails&teacher_id=${teacherId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const teacher = data.teacher;
        showModal('Teacher Details', `
          <div class="space-y-3">
            <div>
              <p class="text-sm text-gray-500">Teacher ID</p>
              <p class="font-semibold">${teacher.teacher_id}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Name</p>
              <p class="font-semibold">${teacher.teacher_name}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Username</p>
              <p class="font-semibold">${teacher.username || 'Not assigned'}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Account Status</p>
              <p class="font-semibold">${teacher.username ? 'Active' : 'No Account'}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Total Subjects Teaching</p>
              <p class="font-semibold">${teacher.subject_count || 0}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Total Schedules</p>
              <p class="font-semibold">${teacher.schedule_count || 0}</p>
            </div>
          </div>
        `);
      }
    })
    .catch(err => console.error('Error:', err));
}

// Edit Teacher
function editTeacher(teacherId) {
  fetch(`api/admin-api.php?action=getTeacherDetails&teacher_id=${teacherId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const teacher = data.teacher;
        showModal('Edit Teacher', `
          <form id="editTeacherForm" class="space-y-4">
            <input type="hidden" name="teacher_id" value="${teacher.teacher_id}">
            
            <div>
              <label class="block text-sm font-medium mb-1">Teacher ID</label>
              <input type="text" value="${teacher.teacher_id}" class="w-full border rounded p-2 bg-gray-100" disabled>
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Teacher Name *</label>
              <input type="text" name="teacher_name" value="${teacher.teacher_name}" 
                     class="w-full border rounded p-2" required>
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Username</label>
              <input type="text" name="username" value="${teacher.username || ''}" 
                     class="w-full border rounded p-2" placeholder="Leave blank to keep current">
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">New Password</label>
              <input type="password" name="password" 
                     class="w-full border rounded p-2" placeholder="Leave blank to keep current">
              <p class="text-xs text-gray-500 mt-1">Only enter if you want to change the password</p>
            </div>
            
            <div class="flex gap-2 mt-4">
              <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">
                Update Teacher
              </button>
              <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 py-2 rounded">
                Cancel
              </button>
            </div>
          </form>
        `);
        
        // Handle form submission
        document.getElementById('editTeacherForm').addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);
          formData.append('action', 'updateTeacher');
          
          fetch('api/admin-api.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert('Teacher updated successfully!');
              closeModal();
              loadTeacherAccounts();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(err => console.error('Error:', err));
        });
      }
    })
    .catch(err => console.error('Error:', err));
}

// Delete Teacher
function deleteTeacher(teacherId) {
  if (!confirm('Are you sure you want to delete this teacher? This will also remove all related schedules and subjects.')) return;
  
  fetch('api/admin-api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `action=deleteTeacher&teacher_id=${teacherId}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Teacher deleted successfully!');
      loadTeacherAccounts();
      loadDashboardStats();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error('Error deleting teacher:', err));
}

// ========================================
// STUDENT ACCOUNTS - FULL CRUD
// ========================================
function loadStudentAccounts() {
  fetch('api/admin-api.php?action=getStudentAccounts')
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('studentAccountsList');
      if (data.success && data.students.length > 0) {
        tbody.innerHTML = data.students.map(student => `
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-2 border">${student.student_id}</td>
            <td class="px-3 py-2 border">${student.student_name}</td>
            <td class="px-3 py-2 border">${student.grade_level}</td>
            <td class="px-3 py-2 border">${student.section_name || 'Not Assigned'}</td>
            <td class="px-3 py-2 border">
              <span class="px-2 py-1 text-xs rounded ${student.enrollment_status === 'enrolled' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                ${student.enrollment_status || 'Pending'}
              </span>
            </td>
            <td class="px-3 py-2 border text-center">
              <button onclick="viewStudent('${student.student_id}')" 
                      class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs mr-1">
                View
              </button>
              <button onclick="editStudent('${student.student_id}')" 
                      class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs mr-1">
                Edit
              </button>
              <button onclick="deleteStudent('${student.student_id}')" 
                      class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                Delete
              </button>
            </td>
          </tr>
        `).join('');
      } else {
        tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-2 text-center text-gray-400">No student accounts found</td></tr>';
      }
    })
    .catch(err => console.error('Error loading students:', err));
}

// View Student Details
function viewStudent(studentId) {
  fetch(`api/admin-api.php?action=getStudentDetails&student_id=${studentId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const s = data.student;
        showModal('Student Details', `
          <div class="space-y-3 max-h-96 overflow-y-auto">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Student ID</p>
                <p class="font-semibold">${s.student_id}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Name</p>
                <p class="font-semibold">${s.student_name}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Grade Level</p>
                <p class="font-semibold">${s.grade_level}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Gender</p>
                <p class="font-semibold">${s.gender || 'N/A'}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Birthdate</p>
                <p class="font-semibold">${s.birthdate || 'N/A'}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Religion</p>
                <p class="font-semibold">${s.religion || 'N/A'}</p>
              </div>
              <div class="col-span-2">
                <p class="text-sm text-gray-500">Address</p>
                <p class="font-semibold">${s.address || 'N/A'}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Contact Number</p>
                <p class="font-semibold">${s.contact_number || 'N/A'}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Section</p>
                <p class="font-semibold">${s.section_name || 'Not Assigned'}</p>
              </div>
            </div>
            
            <div class="border-t pt-3 mt-3">
              <p class="font-semibold text-gray-700 mb-2">Parent/Guardian Information</p>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Father's Name</p>
                  <p class="font-semibold">${s.father_name || 'N/A'}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Father's Occupation</p>
                  <p class="font-semibold">${s.father_occupation || 'N/A'}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Mother's Name</p>
                  <p class="font-semibold">${s.mother_name || 'N/A'}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Mother's Occupation</p>
                  <p class="font-semibold">${s.mother_occupation || 'N/A'}</p>
                </div>
              </div>
            </div>
          </div>
        `);
      }
    })
    .catch(err => console.error('Error:', err));
}

// Edit Student
function editStudent(studentId) {
  fetch(`api/admin-api.php?action=getStudentDetails&student_id=${studentId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const s = data.student;
        showModal('Edit Student', `
          <form id="editStudentForm" class="space-y-3 max-h-96 overflow-y-auto">
            <input type="hidden" name="student_id" value="${s.student_id}">
            
            <div class="grid grid-cols-2 gap-3">
              <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Student Name *</label>
                <input type="text" name="student_name" value="${s.student_name}" 
                       class="w-full border rounded p-2" required>
              </div>
              
              <div>
                <label class="block text-sm font-medium mb-1">Grade Level *</label>
                <select name="grade_level" class="w-full border rounded p-2" required>
                  <option value="Kindergarten" ${s.grade_level === 'Kindergarten' ? 'selected' : ''}>Kindergarten</option>
                  <option value="Grade 1" ${s.grade_level === 'Grade 1' ? 'selected' : ''}>Grade 1</option>
                  <option value="Grade 2" ${s.grade_level === 'Grade 2' ? 'selected' : ''}>Grade 2</option>
                  <option value="Grade 3" ${s.grade_level === 'Grade 3' ? 'selected' : ''}>Grade 3</option>
                  <option value="Grade 4" ${s.grade_level === 'Grade 4' ? 'selected' : ''}>Grade 4</option>
                  <option value="Grade 5" ${s.grade_level === 'Grade 5' ? 'selected' : ''}>Grade 5</option>
                  <option value="Grade 6" ${s.grade_level === 'Grade 6' ? 'selected' : ''}>Grade 6</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium mb-1">Gender</label>
                <select name="gender" class="w-full border rounded p-2">
                  <option value="Male" ${s.gender === 'Male' ? 'selected' : ''}>Male</option>
                  <option value="Female" ${s.gender === 'Female' ? 'selected' : ''}>Female</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium mb-1">Birthdate</label>
                <input type="date" name="birthdate" value="${s.birthdate || ''}" 
                       class="w-full border rounded p-2">
              </div>
              
              <div>
                <label class="block text-sm font-medium mb-1">Religion</label>
                <input type="text" name="religion" value="${s.religion || ''}" 
                       class="w-full border rounded p-2">
              </div>
              
              <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Address</label>
                <textarea name="address" class="w-full border rounded p-2" rows="2">${s.address || ''}</textarea>
              </div>
              
              <div>
                <label class="block text-sm font-medium mb-1">Contact Number</label>
                <input type="text" name="contact_number" value="${s.contact_number || ''}" 
                       class="w-full border rounded p-2">
              </div>
            </div>
            
            <div class="flex gap-2 mt-4">
              <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">
                Update Student
              </button>
              <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 py-2 rounded">
                Cancel
              </button>
            </div>
          </form>
        `);
        
        document.getElementById('editStudentForm').addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);
          formData.append('action', 'updateStudent');
          
          fetch('api/admin-api.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert('Student updated successfully!');
              closeModal();
              loadStudentAccounts();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(err => console.error('Error:', err));
        });
      }
    })
    .catch(err => console.error('Error:', err));
}

// Delete Student
function deleteStudent(studentId) {
  if (!confirm('Are you sure you want to delete this student? This will also remove all grades and enrollment records.')) return;
  
  fetch('api/admin-api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `action=deleteStudent&student_id=${studentId}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Student deleted successfully!');
      loadStudentAccounts();
      loadDashboardStats();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error('Error deleting student:', err));
}

// ========================================
// SCHEDULE MANAGEMENT
// ========================================
function loadScheduleDropdowns() {
  console.log('Loading schedule dropdowns...');
  
  // Load teachers
  fetch('api/admin-api.php?action=getTeacherAccounts')
    .then(res => res.json())
    .then(data => {
      console.log('Teachers data:', data);
      const select = document.getElementById('scheduleTeacher');
      if (select && data.success) {
        select.innerHTML = '<option value="">Select Teacher</option>' +
          data.teachers.map(t => `<option value="${t.teacher_id}">${t.teacher_name}</option>`).join('');
        console.log('Teachers dropdown populated');
      } else {
        console.error('Teachers select element not found or data error');
      }
    })
    .catch(err => console.error('Error loading teachers:', err));
  
  // Load subjects
  fetch('api/admin-api.php?action=getSubjects')
    .then(res => res.json())
    .then(data => {
      console.log('Subjects data:', data);
      const select = document.getElementById('scheduleSubject');
      if (select && data.success) {
        select.innerHTML = '<option value="">Select Subject</option>' +
          data.subjects.map(s => `<option value="${s.subject_code}">${s.subject_name}</option>`).join('');
        console.log('Subjects dropdown populated');
      } else {
        console.error('Subjects select element not found or data error');
      }
    })
    .catch(err => console.error('Error loading subjects:', err));
  
  // Load sections
  fetch('api/admin-api.php?action=getSections')
    .then(res => res.json())
    .then(data => {
      console.log('Sections data:', data);
      const select = document.getElementById('scheduleSection');
      if (select && data.success) {
        select.innerHTML = '<option value="">Select Section</option>' +
          data.sections.map(s => `<option value="${s.section_id}">${s.section_name}</option>`).join('');
        console.log('Sections dropdown populated');
      } else {
        console.error('Sections select element not found or data error');
      }
    })
    .catch(err => console.error('Error loading sections:', err));
}

function loadSchedules() {
  console.log('Loading schedules...');
  
  fetch('api/admin-api.php?action=getSchedules')
    .then(res => {
      console.log('Schedules response status:', res.status);
      return res.json();
    })
    .then(data => {
      console.log('Schedules data received:', data);
      
      const tbody = document.getElementById('schedulesList');
      if (!tbody) {
        console.error('schedulesList element not found!');
        return;
      }
      
      if (data.success && data.schedules && data.schedules.length > 0) {
        tbody.innerHTML = data.schedules.map(schedule => {
          const datetime = new Date(schedule.day_time);
          const dayTime = datetime.toLocaleDateString('en-US', { weekday: 'long' }) + ' ' + 
                         datetime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
          
          return `
            <tr class="hover:bg-gray-50">
              <td class="px-3 py-2 border">${schedule.teacher_name}</td>
              <td class="px-3 py-2 border">${schedule.subject_name}</td>
              <td class="px-3 py-2 border">${schedule.section_name}</td>
              <td class="px-3 py-2 border">${dayTime}</td>
              <td class="px-3 py-2 border">Room ${schedule.room_number}</td>
              <td class="px-3 py-2 border text-center">
                <button onclick="editSchedule(${schedule.schedule_id})" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs mr-1">
                  Edit
                </button>
                <button onclick="deleteSchedule(${schedule.schedule_id})" 
                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                  Delete
                </button>
              </td>
            </tr>
          `;
        }).join('');
        console.log('Schedules rendered successfully:', data.schedules.length, 'schedules');
      } else {
        tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-2 text-center text-gray-400">No schedules found. Add your first schedule above!</td></tr>';
        console.log('No schedules found');
      }
    })
    .catch(err => {
      console.error('Error loading schedules:', err);
      const tbody = document.getElementById('schedulesList');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-2 text-center text-red-500">Error loading schedules. Check console.</td></tr>';
      }
    });
}

document.getElementById('addScheduleForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('action', 'addSchedule');
  
  fetch('api/admin-api.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Schedule added successfully!');
      this.reset();
      loadSchedules();
      loadDashboardStats();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error('Error adding schedule:', err));
});

function deleteSchedule(scheduleId) {
  if (!confirm('Are you sure you want to delete this schedule?')) return;
  
  fetch('api/admin-api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `action=deleteSchedule&schedule_id=${scheduleId}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Schedule deleted successfully!');
      loadSchedules();
      loadDashboardStats();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error('Error deleting schedule:', err));
}

function editSchedule(scheduleId) {
  alert('Edit schedule functionality - Schedule ID: ' + scheduleId);
}

// ========================================
// ENROLLMENT MANAGEMENT
// ========================================
function loadPendingEnrollments() {
  fetch('api/admin-api.php?action=getPendingEnrollments')
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('pendingEnrollmentsList');
      if (data.success && data.enrollments.length > 0) {
        tbody.innerHTML = data.enrollments.map(enrollment => `
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-2 border">${enrollment.student_id}</td>
            <td class="px-3 py-2 border">${enrollment.student_name}</td>
            <td class="px-3 py-2 border">${enrollment.grade_level}</td>
            <td class="px-3 py-2 border">${new Date(enrollment.date_enrolled).toLocaleDateString()}</td>
            <td class="px-3 py-2 border">
              <select id="section_${enrollment.student_id}" class="border rounded p-1 w-full text-sm">
                <option value="">Select Section</option>
                ${data.sections.filter(s => s.grade_level === enrollment.grade_level)
                  .map(s => `<option value="${s.section_id}">${s.section_name}</option>`).join('')}
              </select>
            </td>
            <td class="px-3 py-2 border text-center">
              <button onclick="approveEnrollment('${enrollment.student_id}', '${enrollment.enrollment_id}')" 
                      class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                Approve
              </button>
            </td>
          </tr>
        `).join('');
      } else {
        tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-2 text-center text-gray-400">No pending enrollments</td></tr>';
      }
    })
    .catch(err => console.error('Error loading enrollments:', err));
}

function approveEnrollment(studentId, enrollmentId) {
  const sectionSelect = document.getElementById('section_' + studentId);
  const sectionId = sectionSelect.value;
  
  if (!sectionId) {
    alert('Please select a section first!');
    return;
  }
  
  fetch('api/admin-api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `action=approveEnrollment&enrollment_id=${enrollmentId}&section_id=${sectionId}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Enrollment approved successfully!');
      loadPendingEnrollments();
      loadDashboardStats();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error('Error approving enrollment:', err));
}

// ========================================
// MODAL FUNCTIONS
// ========================================
function showModal(title, content) {
  const modalHTML = `
    <div id="customModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
        <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
          <h3 class="text-xl font-bold">${title}</h3>
          <button onclick="closeModal()" class="text-white hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="p-6">
          ${content}
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function closeModal() {
  const modal = document.getElementById('customModal');
  if (modal) {
    modal.remove();
  }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  const modal = document.getElementById('customModal');
  if (modal && e.target === modal) {
    closeModal();
  }
});

// ========================================
// INITIALIZE
// ========================================
document.addEventListener('DOMContentLoaded', function() {
  console.log('Dashboard loaded - initializing...');
  
  // Load dashboard by default
  loadDashboardStats();
  
  // Debug: Check if elements exist
  console.log('Total Teachers element:', document.getElementById('totalTeachers'));
  console.log('Activity Logs element:', document.getElementById('activityLogs'));
});