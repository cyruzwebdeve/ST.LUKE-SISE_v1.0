function login(username, password) {
  const db = getDB();
  return db.accounts.find(acc => acc.username === username && acc.password === password);
}
