<?php
require_once '../includes/config.php';

$class_name = $_GET['class'] ?? '';
$date = $_GET['date'] ?? '';

$stmt = $pdo->prepare("SELECT s.roll_no, s.name, a.status
                       FROM attendance a
                       JOIN students s ON s.id = a.student_id
                       JOIN classes c ON c.id = a.class_id
                       WHERE c.name = ? AND a.date = ?");
$stmt->execute([$class_name, $date]);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h3><i class="fas fa-eye me-2"></i>Attendance - <?= htmlspecialchars($class_name) ?> (<?= htmlspecialchars($date) ?>)</h3>
  <a href="list.php" class="btn btn-secondary mb-3">Back</a>

  <table class="table table-bordered align-middle">
    <thead class="table-dark">
      <tr>
        <th>Roll No</th>
        <th>Name</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($records as $r): ?>
      <tr>
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
