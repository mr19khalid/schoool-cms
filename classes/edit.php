<?php
// classes/edit.php
require __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit;
}

// fetch existing class
$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->execute([$id]);
$class = $stmt->fetch();

if (!$class) {
    die("Class not found!");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $section = trim($_POST['section']);

    if ($name === '') {
        $error = "Class name is required.";
    } else {
        $stmt = $pdo->prepare("UPDATE classes SET name = ?, section = ? WHERE id = ?");
        $stmt->execute([$name, $section, $id]);
        header('Location: list.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Class - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <a href="list.php" class="btn btn-secondary mb-3">← Back to Class List</a>
  <div class="card shadow-sm p-3">
    <h4>Edit Class</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Class Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($class['name']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Section</label>
        <input type="text" name="section" class="form-control" value="<?= htmlspecialchars($class['section']) ?>">
      </div>
      <button class="btn btn-primary">💾 Update</button>
    </form>
  </div>
</div>

</body>
</html>
