<?php
// students/add.php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$error = '';
$old = [
  'roll_no'=>'', 'name'=>'', 'dob'=>'', 'gender'=>'Male', 'class_id'=>'', 'parent_name'=>'', 'phone'=>'', 'address'=>''
];

// fetch classes for dropdown
$classesStmt = $pdo->query("SELECT id, name, section FROM classes ORDER BY name");
$classes = $classesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = array_merge($old, $_POST);
    $roll = trim($_POST['roll_no']);
    $name = trim($_POST['name']);
    $dob = $_POST['dob'] ?: null;
    $gender = $_POST['gender'] ?? 'Male';
    $class_id = $_POST['class_id'] ?: null;
    $parent = trim($_POST['parent_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // photo handling
    $photoPath = null;
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, GIF allowed for photo.";
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $error = "Photo must be smaller than 2MB.";
        } else {
            // create uploads dir if not exists
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $photoName = time() . '_' . rand(100,999) . '.' . $ext;
            $target = $uploadDir . $photoName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photoPath = 'uploads/' . $photoName; // store relative to project root
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO students (roll_no, name, dob, gender, class_id, parent_name, phone, address, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $roll ?: null,
            $name,
            $dob,
            $gender,
            $class_id ?: null,
            $parent ?: null,
            $phone ?: null,
            $address ?: null,
            $photoPath
        ]);
        header('Location: list.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Student - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="../dashboard.php" class="btn btn-sm btn-secondary mb-3">← Dashboard</a>
  <div class="card p-3">
    <h4>Add Student</h4>

    <?php if($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Roll No</label>
          <input name="roll_no" class="form-control" value="<?= htmlspecialchars($old['roll_no']) ?>">
        </div>
        <div class="col-md-8 mb-3">
          <label class="form-label">Full Name</label>
          <input name="name" class="form-control" required value="<?= htmlspecialchars($old['name']) ?>">
        </div>

        <div class="col-md-3 mb-3">
          <label class="form-label">Date of Birth</label>
          <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($old['dob']) ?>">
        </div>

        <div class="col-md-3 mb-3">
          <label class="form-label">Gender</label>
          <select name="gender" class="form-select">
            <option <?= $old['gender']=='Male' ? 'selected' : '' ?>>Male</option>
            <option <?= $old['gender']=='Female' ? 'selected' : '' ?>>Female</option>
            <option <?= $old['gender']=='Other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Class</label>
          <select name="class_id" class="form-select">
            <option value="">-- No Class / Select --</option>
            <?php foreach($classes as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($old['class_id'] == $c['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name'] . ($c['section'] ? ' - '.$c['section'] : '')) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Parent / Guardian</label>
          <input name="parent_name" class="form-control" value="<?= htmlspecialchars($old['parent_name']) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label">Phone</label>
          <input name="phone" class="form-control" value="<?= htmlspecialchars($old['phone']) ?>">
        </div>

        <div class="col-md-8 mb-3">
          <label class="form-label">Address</label>
          <input name="address" class="form-control" value="<?= htmlspecialchars($old['address']) ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Photo (optional)</label>
          <input type="file" name="photo" class="form-control">
          <small class="text-muted">jpg, png, gif (max 2MB)</small>
        </div>
      </div>

      <button class="btn btn-primary">Save Student</button>
      <a href="list.php" class="btn btn-secondary">Back to List</a>
    </form>
  </div>
</div>
</body>
</html>
