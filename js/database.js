// ✅ Default SIES Database with Enrollment Status System
const defaultDB = {
  // --- STUDENTS LIST ---
  students: [
    { student_id: "S001", student_name: "James Martin", grade_level: "Grade 5", section: "Gumamela", status: "Enrolled" },
    { student_id: "S002", student_name: "Angel De Vera", grade_level: "Grade 5", section: "Gumamela", status: "Enrolled" },
    { student_id: "S003", student_name: "Mark Santos", grade_level: "Grade 6", section: "Sampaguita", status: "Enrolled" },

    // Example of a newly registered (pending) student:
    { student_id: "S004", student_name: "Juan Martin", grade_level: "", section: "", status: "Pending" },
  ],

  // --- CLASS SCHEDULES ---
  schedules: [
    { 
      subject_code: "ENG101", 
      subject_name: "English", 
      day_time: "Mon 9AM–10AM", 
      room: "Room 203", 
      grade_level: "Grade 5", 
      section: "Gumamela" 
    }
  ],

  // --- GRADES (Teacher-managed) ---
  grades: [
    // Example: { student_id: "S001", subject_name: "English", grade: 95 }
  ],

  // --- ACCOUNTS LIST ---
  accounts: [
    { username: "admin", password: "admin", role: "Admin" },
    { username: "jane", password: "1234", role: "Teacher", refId: "T001" },
    { username: "james", password: "1234", role: "Student", refId: "S001" },
    { username: "angel", password: "1234", role: "Student", refId: "S002" },
    { username: "mark", password: "1234", role: "Student", refId: "S003" },

    // Default pending student account
    { username: "juan", password: "1234", role: "Student", refId: "S004" }
  ]
};

// ✅ Helper Functions for Local Storage Management
function getDB() {
  return JSON.parse(localStorage.getItem("siesDB")) || defaultDB;
}

function saveDB(data) {
  localStorage.setItem("siesDB", JSON.stringify(data));
}

function setSession(user) {
  localStorage.setItem("sessionUser", JSON.stringify(user));
}

function getSession() {
  return JSON.parse(localStorage.getItem("sessionUser"));
}

function logout() {
  localStorage.removeItem("sessionUser");
  window.location.href = "login.html";
}

// ✅ Utility: Update Student Status (used by Admin)
function updateStudentStatus(studentId, newStatus) {
  const db = getDB();
  const student = db.students.find(s => s.student_id === studentId);
  if (student) student.status = newStatus;
  saveDB(db);
}

// ✅ Utility: Get Student Info by refId
function getStudentByRefId(refId) {
  const db = getDB();
  return db.students.find(s => s.student_id === refId);
}
