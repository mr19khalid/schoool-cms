<?php
// students/edit.php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list.php'); exit;
}
$id = (int)$_GET['id'];

// fetch student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) {
    header('Location: list.php'); exit;
}

// fetch classes
$classesStmt = $pdo->query("SELECT id, name, section FROM classes ORDER BY name");
$classes = $classesStmt->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll = trim($_POST['roll_no']);
    $name = trim($_POST['name']);
    $dob = $_POST['dob'] ?: null;
    $gender = $_POST['gender'] ?? 'Male';
    $class_id = $_POST['class_id'] ?: null;
    $parent = trim($_POST['parent_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // photo update
    $photoPath = $student['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, GIF allowed for photo.";
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $error = "Photo must be smaller than 2MB.";
        } else {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $photoName = time() . '_' . rand(100,999) . '.' . $ext;
            $target = $uploadDir . $photoName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                // delete old
                if ($student['photo'] && file_exists(__DIR__ . '/../' . $student['photo'])) {
                    @unlink(__DIR__ . '/../' . $student['photo']);
                }
                $photoPath = 'uploads/' . $photoName;
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    }

    if (!$error) {
        $upd = $pdo->prepare("UPDATE students SET roll_no = ?, name = ?, dob = ?, gender = ?, class_id = ?, parent_name = ?, phone = ?, address = ?, photo = ? WHERE id = ?");
        $upd->execute([
            $roll ?: null,
            $name,
            $dob,
            $gender,
            $class_id ?: null,
            $parent ?: null,
            $phone ?: null,
            $address ?: null,
            $photoPath,
            $id
        ]);
        header('Location: list.php'); exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Student - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="list.php" class="btn btn-sm btn-secondary mb-3">← Back to List</a>
  <div class="card p-3">
    <h4>Edit Student</h4>
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label>Roll No</label>
          <input name="roll_no" class="form-control" value="<?= htmlspecialchars($student['roll_no']) ?>">
        </div>
        <div class="col-md-8 mb-3">
          <label>Full Name</label>
          <input name="name" class="form-control" required value="<?= htmlspecialchars($student['name']) ?>">
        </div>

        <div class="col-md-3 mb-3">
          <label>DOB</label>
          <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($student['dob']) ?>">
        </div>

        <div class="col-md-3 mb-3">
          <label>Gender</label>
          <select name="gender" class="form-select">
            <option <?= $student['gender']=='Male' ? 'selected' : '' ?>>Male</option>
            <option <?= $student['gender']=='Female' ? 'selected' : '' ?>>Female</option>
            <option <?= $student['gender']=='Other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label>Class</label>
          <select name="class_id" class="form-select">
            <option value="">-- No Class / Select --</option>
            <?php foreach($classes as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($student['class_id'] == $c['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name'] . ($c['section'] ? ' - '.$c['section'] : '')) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label>Parent / Guardian</label>
          <input name="parent_name" class="form-control" value="<?= htmlspecialchars($student['parent_name']) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label>Phone</label>
          <input name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>">
        </div>

        <div class="col-md-8 mb-3">
          <label>Address</label>
          <input name="address" class="form-control" value="<?= htmlspecialchars($student['address']) ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label>Photo (upload to replace)</label>
          <input type="file" name="photo" class="form-control">
          <?php if($student['photo']): ?>
            <div class="mt-2">
              <img src="../<?= htmlspecialchars($student['photo']) ?>" style="height:60px;object-fit:cover;">
            </div>
          <?php endif; ?>
        </div>

      </div>

      <button class="btn btn-primary">Update Student</button>
      <a class="btn btn-secondary" href="list.php">Cancel</a>
    </form>
  </div>
</div>
</body>
</html>
