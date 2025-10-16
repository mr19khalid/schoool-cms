<?php
require_once '../includes/config.php';

$filter_date = $_GET['date'] ?? '';
$filter_class = $_GET['class_id'] ?? '';

$classes = $pdo->query("SELECT * FROM classes")->fetchAll();

$query = "SELECT c.name AS class_name, s.roll_no, s.name, a.status, a.date 
          FROM attendance a
          JOIN students s ON s.id = a.student_id
          JOIN classes c ON c.id = a.class_id WHERE 1";

$params = [];
if ($filter_date) { $query .= " AND a.date = ?"; $params[] = $filter_date; }
if ($filter_class) { $query .= " AND c.id = ?"; $params[] = $filter_class; }

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3><i class="fas fa-chart-line me-2"></i>Attendance Reports</h3>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Select Class</label>
      <select name="class_id" class="form-select">
        <option value="">All Classes</option>
        <?php foreach ($classes as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $filter_class==$c['id']?'selected':'' ?>>
          <?= htmlspecialchars($c['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Select Date</label>
      <input type="date" name="date" value="<?= $filter_date ?>" class="form-control">
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Filter</button>
    </div>
  </form>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Date</th>
        <th>Class</th>
        <th>Roll No</th>
        <th>Name</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($records as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['date']) ?></td>
        <td><?= htmlspecialchars($r['class_name']) ?></td>
        <td>#<?= htmlspecialchars($r['roll_no']) ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td>
          <?php if ($r['status'] == 'present'): ?>
            <span class="badge bg-success">Present</span>
          <?php elseif ($r['status'] == 'absent'): ?>
            <span class="badge bg-danger">Absent</span>
          <?php else: ?>
            <span class="badge bg-warning text-dark">Leave</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
