// js/teacher.js
document.addEventListener("DOMContentLoaded", () => {
  const db = getDB();
  const studentSelect = document.getElementById("studentId");
  const subjectSelect = document.getElementById("subjectCode");
  const gradeList = document.getElementById("gradeList");

  // Populate dropdowns
  db.students.forEach(s => {
    studentSelect.innerHTML += `<option value="${s.student_id}">${s.student_name}</option>`;
  });

  db.schedules.forEach(sub => {
    subjectSelect.innerHTML += `<option value="${sub.subject_code}">${sub.subject_name}</option>`;
  });

  function renderGrades() {
    gradeList.innerHTML = "";
    db.grades.forEach((g, i) => {
      const subj = db.schedules.find(s => s.subject_code === g.subject_code);
      const stu = db.students.find(s => s.student_id === g.student_id);
      gradeList.innerHTML += `
        <div class="flex justify-between border p-3 rounded">
          <div>
            <p class="font-semibold">${stu.student_name}</p>
            <p class="text-sm text-gray-600">${subj.subject_name} • ${g.grade_score} (${g.grading_period})</p>
          </div>
          <button onclick="deleteGrade(${i})" class="text-red-600 hover:text-red-800">🗑️</button>
        </div>`;
    });
  }

  document.getElementById("addGradeForm").addEventListener("submit", e => {
    e.preventDefault();
    const grade = {
      student_id: studentSelect.value,
      subject_code: subjectSelect.value,
      grade_score: parseInt(document.getElementById("gradeScore").value),
      grading_period: document.getElementById("gradingPeriod").value
    };

    db.grades.push(grade);
    saveDB(db);
    e.target.reset();
    renderGrades();
    alert("✅ Grade added successfully!");
  });

  window.deleteGrade = i => {
    db.grades.splice(i, 1);
    saveDB(db);
    renderGrades();
  };

  renderGrades();
});
