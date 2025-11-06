// js/student.js
document.addEventListener("DOMContentLoaded", () => {
  const db = getDB();
  const session = getSession();

  // 🔒 Redirect if not a student
  if (!session || session.role !== "Student") {
    window.location.href = "login.html";
    return;
  }

  // 🎓 Get logged-in student's data
  const student = db.students.find(s => s.student_id === session.refId);

  // Display basic info
  document.getElementById("studentName").textContent = student.student_name;
  document.getElementById("studentGrade").textContent = student.grade_level || "—";
  document.getElementById("studentSection").textContent = student.section || "—";

  // Status text and color setup
  const statusElement = document.getElementById("studentStatus");
  const statusMsg = document.getElementById("statusMessage");
  const scheduleTable = document.getElementById("scheduleTable");

  let statusText = "Pending";
  let msg = "";
  let msgClass = "";

  switch (student.status) {
    case "Enrolled":
      statusText = "Enrolled";
      msg = `✅ You are officially enrolled in ${student.grade_level} - ${student.section}.`;
      msgClass = "bg-green-100 text-green-700";
      break;

    case "Rejected":
      statusText = "Rejected";
      msg = `❌ Your enrollment has been rejected. Please contact the registrar for more information.`;
      msgClass = "bg-red-100 text-red-700";
      break;

    default:
      statusText = "Pending";
      msg = `⏳ Your enrollment is still under review. Please wait for the admin’s validation.`;
      msgClass = "bg-yellow-100 text-yellow-700";
      break;
  }

  // Apply styles and display status
  statusElement.textContent = statusText;
  statusMsg.textContent = msg;
  statusMsg.className = `mt-6 p-4 rounded-md text-center font-medium ${msgClass}`;

  // 🕓 Load schedule only if student is enrolled
  scheduleTable.innerHTML = "";
  if (student.status === "Enrolled") {
    const schedules = db.schedules.filter(
      sched => sched.grade_level === student.grade_level && sched.section === student.section
    );

    if (schedules.length > 0) {
      schedules.forEach(s => {
        scheduleTable.innerHTML += `
          <tr>
            <td class="px-4 py-2 border">${s.subject_name}</td>
            <td class="px-4 py-2 border">${s.day_time}</td>
            <td class="px-4 py-2 border">${s.room}</td>
          </tr>`;
      });
    } else {
      scheduleTable.innerHTML = `<tr>
        <td colspan="3" class="text-center py-3 text-gray-500">No schedule assigned yet.</td>
      </tr>`;
    }
  } else {
    scheduleTable.innerHTML = `<tr>
      <td colspan="3" class="text-center py-3 text-gray-500">Your schedule will appear here once you are enrolled.</td>
    </tr>`;
  }
});
