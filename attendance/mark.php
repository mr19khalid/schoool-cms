<?php
require_once '../includes/config.php';

// Fetch all classes
$stmt = $pdo->query("SELECT * FROM classes ORDER BY id ASC");
$classes = $stmt->fetchAll();

$class_id = $_GET['class_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');
$students = [];

// Fetch students when class selected
if ($class_id) {
  $stmt = $pdo->prepare("SELECT * FROM students WHERE class_id = ? ORDER BY roll_no ASC");
  $stmt->execute([$class_id]);
  $students = $stmt->fetchAll();
}

// Save attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $class_id = $_POST['class_id'];
  $date = $_POST['date'];
  $statuses = $_POST['status'] ?? [];

  foreach ($statuses as $student_id => $status) {
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, class_id, date, status) VALUES (?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE status = VALUES(status)");
    $stmt->execute([$student_id, $class_id, $date, $status]);
  }

  header("Location: list.php?success=1");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Mark Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
  --primary-color: #4361ee;
  --secondary-color: #3f37c9;
  --success-color: #4cc9f0;
  --warning-color: #f8961e;
  --danger-color: #f72585;
  --light-bg: #f8f9fa;
  --dark-bg: #1e1e2f;
  --card-bg: #ffffff;
  --dark-card-bg: #2a2a3f;
  --text-color: #2a2a2a;
  --dark-text-color: #ffffff;
}

body {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  padding-top: 20px;
  transition: all 0.3s ease;
}

.dark-mode {
  background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3f 100%);
}

.container { 
  max-width: 1200px; 
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.page-title {
  color: white;
  font-weight: 800;
  font-size: 2.2rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
  margin: 0;
}

.back-btn {
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(10px);
  border: none;
  border-radius: 50px;
  color: white;
  padding: 10px 20px;
  font-weight: 600;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.back-btn:hover {
  background: rgba(255,255,255,0.3);
  transform: translateY(-2px);
  color: white;
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.card {
  border: none;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  overflow: hidden;
  margin-bottom: 25px;
  transition: all 0.3s ease;
  background: var(--card-bg);
}

.dark-mode .card {
  background: var(--dark-card-bg);
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.card-header {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
  padding: 20px 25px;
  border-bottom: none;
}

.card-header h4 {
  margin: 0;
  font-weight: 700;
  font-size: 1.4rem;
}

.form-control, .form-select {
  border-radius: 12px;
  padding: 12px 15px;
  border: 1px solid #e0e0e0;
  transition: all 0.3s ease;
}

.dark-mode .form-control, 
.dark-mode .form-select {
  background: #3a3a4f;
  border: 1px solid #4a4a5f;
  color: var(--dark-text-color);
}

.form-control:focus, .form-select:focus {
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
  border-color: var(--primary-color);
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  border: none;
  border-radius: 12px;
  padding: 12px 20px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
}

.btn-success {
  background: linear-gradient(135deg, #4cc9f0, #4895ef);
  border: none;
  border-radius: 12px;
  padding: 12px 25px;
  font-weight: 600;
  font-size: 1.1rem;
  transition: all 0.3s ease;
}

.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(76, 201, 240, 0.4);
}

.table-responsive {
  border-radius: 15px;
  overflow: hidden;
}

.table {
  margin-bottom: 0;
  border-collapse: separate;
  border-spacing: 0;
}

.table thead {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  color: white;
}

.table thead th {
  border: none;
  padding: 18px 15px;
  font-weight: 600;
  font-size: 1rem;
}

.table tbody tr {
  transition: all 0.2s ease;
}

.table tbody tr:hover {
  background: rgba(102, 126, 234, 0.08);
}

.dark-mode .table tbody tr:hover {
  background: rgba(102, 126, 234, 0.15);
}

.table tbody td {
  padding: 15px;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}

.dark-mode .table tbody td {
  border-bottom: 1px solid #3a3a4f;
}

.status-select {
  border-radius: 10px;
  padding: 8px 12px;
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
}

.status-select:focus {
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
}

.student-roll {
  font-weight: 700;
  color: var(--primary-color);
  font-size: 1.1rem;
}

.dark-mode .student-roll {
  color: #7b9aff;
}

.student-name {
  font-weight: 500;
  font-size: 1.05rem;
}

.dark-toggle {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(10px);
  border: none;
  border-radius: 50px;
  padding: 12px 18px;
  color: white;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  z-index: 100;
}

.dark-toggle:hover {
  transform: scale(1.05);
  background: rgba(255,255,255,0.3);
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .back-btn {
    margin-top: 15px;
    align-self: flex-start;
  }
  
  .page-title {
    font-size: 1.8rem;
  }
}
</style>
</head>
<body>

<div class="container py-4">
  <div class="header-section">
    <h1 class="page-title"><i class="fas fa-check-circle me-2"></i>Mark Attendance</h1>
    <a href="index.php" class="back-btn">
      <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>
  </div>

  <form method="GET" class="card">
    <div class="card-header">
      <h4><i class="fas fa-filter me-2"></i>Select Class & Date</h4>
    </div>
    <div class="card-body p-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Select Class</label>
          <select name="class_id" class="form-select" required>
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id']==$class_id?'selected':'' ?>>
              <?= htmlspecialchars($c['name']) ?> <?= $c['section']?'('.$c['section'].')':'' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Date</label>
          <input type="date" name="date" value="<?= $date ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
          <button class="btn btn-primary w-100 py-2"><i class="fas fa-users me-2"></i>Load Students</button>
        </div>
      </div>
    </div>
  </form>

  <?php if ($students): ?>
  <form method="POST" class="card">
    <div class="card-header">
      <h4><i class="fas fa-user-graduate me-2"></i>Student Attendance</h4>
    </div>
    <div class="card-body p-4">
      <input type="hidden" name="class_id" value="<?= $class_id ?>">
      <input type="hidden" name="date" value="<?= $date ?>">

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Roll #</th>
              <th>Student Name</th>
              <th>Attendance Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $s): ?>
            <tr>
              <td><span class="student-roll">#<?= htmlspecialchars($s['roll_no']) ?></span></td>
              <td><span class="student-name"><?= htmlspecialchars($s['name']) ?></span></td>
              <td>
                <select name="status[<?= $s['id'] ?>]" class="form-select status-select">
                  <option value="present">✅ Present</option>
                  <option value="absent">❌ Absent</option>
                  <option value="leave">📝 Leave</option>
                </select>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-end mt-4">
        <button class="btn btn-success px-4 py-2"><i class="fas fa-save me-2"></i>Save Attendance</button>
      </div>
    </div>
  </form>
  <?php endif; ?>
</div>

<button class="btn dark-toggle">
  <i class="fas fa-moon"></i>
</button>

<script>
  const toggle = document.querySelector('.dark-toggle');
  
  // Check for saved theme preference or respect OS preference
  const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
  const currentTheme = localStorage.getItem('theme');
  
  if (currentTheme === 'dark' || (!currentTheme && prefersDarkScheme.matches)) {
    document.body.classList.add('dark-mode');
    toggle.innerHTML = '<i class="fas fa-sun"></i>';
  }
  
  toggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    
    if (document.body.classList.contains('dark-mode')) {
      toggle.innerHTML = '<i class="fas fa-sun"></i>';
      localStorage.setItem('theme', 'dark');
    } else {
      toggle.innerHTML = '<i class="fas fa-moon"></i>';
      localStorage.setItem('theme', 'light');
    }
  });
</script>

</body>
</html>