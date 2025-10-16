<?php
// fees/create_fee.php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php'); exit;
}

// AJAX endpoints to fetch default fee
if (isset($_GET['get_class_fee']) && isset($_GET['class_id'])) {
    $cid = (int)$_GET['class_id'];
    $stmt = $pdo->prepare("SELECT fee_amount FROM classes WHERE id = ?");
    $stmt->execute([$cid]);
    echo $stmt->fetchColumn() ?: 0;
    exit;
}
if (isset($_GET['get_student_fee']) && isset($_GET['student_id'])) {
    $sid = (int)$_GET['student_id'];
    $stmt = $pdo->prepare("SELECT c.fee_amount FROM students s JOIN classes c ON s.class_id=c.id WHERE s.id = ?");
    $stmt->execute([$sid]);
    echo $stmt->fetchColumn() ?: 0;
    exit;
}

$message = '';

// Handle form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'bulk') {
        $class_id = (int)($_POST['class_id'] ?? 0);
        $due_date = $_POST['due_date'] ?? null;
        $description = trim($_POST['description'] ?? '');
        if (!$class_id || !$due_date) { $message = "Select class and due date."; }
        else {
            // get class fee
            $stmt = $pdo->prepare("SELECT fee_amount FROM classes WHERE id = ?");
            $stmt->execute([$class_id]);
            $fee_amount = $stmt->fetchColumn() ?: 0;

            // get students of class
            $st = $pdo->prepare("SELECT id FROM students WHERE class_id = ?");
            $st->execute([$class_id]);
            $students = $st->fetchAll(PDO::FETCH_ASSOC);

            $ins = $pdo->prepare("INSERT INTO fees (student_id, amount, due_date, status, description) VALUES (?, ?, ?, 'due', ?)");
            $count = 0;
            foreach ($students as $s) {
                $ins->execute([$s['id'], $fee_amount, $due_date, $description]);
                $count++;
            }
            $message = "Generated fees for {$count} students (Class ID: {$class_id}).";
        }
    }

    elseif ($action === 'single') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        $amount = $_POST['amount'] ?? null;
        $due_date = $_POST['due_date'] ?? null;
        $description = trim($_POST['description'] ?? '');
        if (!$student_id || !$amount || !$due_date) { $message = "Select student, amount and due date."; }
        else {
            $ins = $pdo->prepare("INSERT INTO fees (student_id, amount, due_date, status, description) VALUES (?, ?, ?, 'due', ?)");
            $ins->execute([$student_id, $amount, $due_date, $description]);
            $message = "Fee created for student ID {$student_id}.";
        }
    }
}

// fetch lists for forms
$classes = $pdo->query("SELECT * FROM classes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$students = $pdo->query("SELECT s.id, s.name, s.roll_no, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id=c.id ORDER BY c.name, s.roll_no")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Create Fees - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
  <h3>➕ Create Fees</h3>
  <a href="../dashboard.php" class="btn btn-secondary mb-3">← Dashboard</a>
  <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

  <div class="row">
    <!-- Bulk generate for class -->
    <div class="col-md-6">
      <div class="card p-3 mb-3">
        <h5>Generate fees for whole class</h5>
        <form method="POST">
          <input type="hidden" name="action" value="bulk">
          <div class="mb-2">
            <label>Class</label>
            <select name="class_id" id="bulk_class" class="form-select" required>
              <option value="">-- Select Class --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (Default: <?= number_format($c['fee_amount'],2) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-2">
            <label>Due Date</label>
            <input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d') ?>">
          </div>

          <div class="mb-2">
            <label>Description</label>
            <input type="text" name="description" class="form-control" placeholder="e.g. Monthly fee - Oct 2025">
          </div>

          <button class="btn btn-primary">Generate for Class</button>
        </form>
      </div>
    </div>

    <!-- Single student fee -->
    <div class="col-md-6">
      <div class="card p-3 mb-3">
        <h5>Create fee for single student</h5>
        <form method="POST" id="singleForm">
          <input type="hidden" name="action" value="single">
          <div class="mb-2">
            <label>Student</label>
            <select name="student_id" id="student_select" class="form-select" required>
              <option value="">-- Select Student --</option>
              <?php foreach ($students as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['class_name']) ?> - Roll <?= htmlspecialchars($s['roll_no']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-2">
            <label>Amount (Rs)</label>
            <input type="number" name="amount" id="amount_field" class="form-control" step="0.01" required>
          </div>

          <div class="mb-2">
            <label>Due Date</label>
            <input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-d') ?>">
          </div>

          <div class="mb-2">
            <label>Description</label>
            <input type="text" name="description" class="form-control" placeholder="e.g. Admission fee">
          </div>

          <button class="btn btn-primary">Create Fee</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// auto-fill amount for selected student (AJAX)
$('#student_select').on('change', function() {
  var sid = $(this).val();
  if (!sid) { $('#amount_field').val(''); return; }
  $.get('create_fee.php', { get_student_fee: 1, student_id: sid }, function(amount) {
    $('#amount_field').val(amount);
  });
});
</script>
</body>
</html>
