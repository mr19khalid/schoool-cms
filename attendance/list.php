<?php
require_once '../includes/config.php';

$stmt = $pdo->query("SELECT a.date, c.name AS class_name, c.section, COUNT(a.id) AS total_records
                     FROM attendance a
                     JOIN classes c ON c.id = a.class_id
                     GROUP BY a.class_id, a.date
                     ORDER BY a.date DESC");
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance List</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3><i class="fas fa-list me-2"></i>Attendance Records</h3>
  <a href="mark.php" class="btn btn-primary mb-3"><i class="fas fa-plus me-2"></i>Mark Attendance</a>

  <table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
      <tr>
        <th>Date</th>
        <th>Class</th>
        <th>Total Records</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($records as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['date']) ?></td>
        <td><?= htmlspecialchars($r['class_name'].' '.$r['section']) ?></td>
        <td><?= $r['total_records'] ?></td>
        <td>
          <a href="view.php?class=<?= urlencode($r['class_name']) ?>&date=<?= $r['date'] ?>" class="btn btn-sm btn-info">
            <i class="fas fa-eye"></i> View
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
