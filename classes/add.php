<?php
// classes/add.php
require __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$error = '';
$name = '';
$section = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $section = trim($_POST['section']);

    if ($name == '') {
        $error = "Class name is required.";
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO classes (name, section) VALUES (?, ?)");
        $stmt->execute([$name, $section ?: null]);
        header('Location: list.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Class - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="../dashboard.php" class="btn btn-sm btn-secondary mb-3">← Dashboard</a>
  <div class="card p-3">
    <h4>Add Class</h4>

    <?php if($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Class Name</label>
          <input name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Class 8" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Section (optional)</label>
          <input name="section" class="form-control" value="<?= htmlspecialchars($section) ?>" placeholder="e.g. A or B">
        </div>
      </div>

      <button class="btn btn-primary">Save Class</button>
      <a href="list.php" class="btn btn-secondary">Back to List</a>
    </form>
  </div>
</div>
</body>
</html>
