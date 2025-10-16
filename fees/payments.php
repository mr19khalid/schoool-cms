<?php
// fees/payments.php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ../login.php'); exit; }

$msg = '';

// handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fee_id'])) {
    $fee_id = (int)$_POST['fee_id'];
    $status = $_POST['status'] ?? 'due';
    $stmt = $pdo->prepare("UPDATE fees SET status = ? WHERE id = ?");
    $stmt->execute([$status, $fee_id]);
    $msg = "Status updated.";
}

// fetch fees (you can add filters later)
$query = "
 SELECT f.*, s.name AS student_name, s.parent_name, s.photo, s.roll_no, c.name AS class_name
 FROM fees f
 JOIN students s ON f.student_id = s.id
 LEFT JOIN classes c ON s.class_id = c.id
 ORDER BY f.due_date DESC, c.name, s.roll_no
";
$fees = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payments - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>.photo{width:48px;height:48px;object-fit:cover;border-radius:50%;}</style>
</head>
<body class="bg-light">
<div class="container py-4">
  <h3>💳 Payments</h3>
  <a href="../dashboard.php" class="btn btn-secondary mb-3">← Dashboard</a>
  <a href="create_fee.php" class="btn btn-primary mb-3">+ Create Fee</a>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered mb-0 align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Photo</th>
            <th>Student</th>
            <th>Parent</th>
            <th>Class</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Description</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($fees): foreach ($fees as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td>
              <?php if (!empty($r['photo']) && file_exists(__DIR__ . '/../' . $r['photo'])): ?>
                <img src="../<?= htmlspecialchars($r['photo']) ?>" class="photo" alt="photo">
              <?php else: ?>
                <img src="https://via.placeholder.com/48" class="photo" alt="no photo">
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($r['student_name']) ?> <br><small>Roll: <?= htmlspecialchars($r['roll_no']) ?></small></td>
            <td><?= htmlspecialchars($r['parent_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['class_name'] ?? '-') ?></td>
            <td>PKR <?= number_format($r['amount'], 2) ?></td>
            <td><?= htmlspecialchars($r['due_date'] ?? '-') ?></td>
            <td>
              <form method="POST" class="d-flex">
                <input type="hidden" name="fee_id" value="<?= (int)$r['id'] ?>">
                <select name="status" class="form-select form-select-sm me-2" style="width:115px;">
                  <option value="due" <?= $r['status']==='due' ? 'selected' : '' ?>>Due</option>
                  <option value="partial" <?= $r['status']==='partial' ? 'selected' : '' ?>>Partial</option>
                  <option value="paid" <?= $r['status']==='paid' ? 'selected' : '' ?>>Paid</option>
                </select>
                <button class="btn btn-success btn-sm">Update</button>
              </form>
            </td>
            <td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
            <td>
              <a href="receipt.php?fee_id=<?= (int)$r['id'] ?>" class="btn btn-outline-primary btn-sm" target="_blank">🧾 Receipt</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="10" class="text-center">No fee records.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
