function login(username, password) {
  const db = getDB();
  return db.accounts.find(acc => acc.username === username && acc.password === password);
}

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  if (!loginForm) return;

  loginForm.addEventListener("submit", e => {
    e.preventDefault();

    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!username || !password) {
      alert("Please fill out all fields.");
      return;
    }

    const acc = login(username, password);
    if (!acc) {
      alert("Invalid credentials. Please check your username or password.");
      return;
    }

    setSession(acc);

    if (acc.role === "Admin") window.location.href = "admin-dashboard.html";
    if (acc.role === "Teacher") window.location.href = "teacher-dashboard.html";
    if (acc.role === "Student") window.location.href = "student-dashboard.html";
  });
});
