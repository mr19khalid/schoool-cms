<?php
include '../includes/config.php';

// Fetch existing settings
$stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$settings = $stmt->fetch();

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_name = $_POST['school_name'];
    $theme_color = $_POST['theme_color'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $logoPath = $settings['school_logo'];

    // Handle logo upload
    if (!empty($_FILES['school_logo']['name'])) {
        $targetDir = "../uploads/";
        $fileName = time() . '_' . basename($_FILES["school_logo"]["name"]);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES["school_logo"]["tmp_name"], $targetFile);
        $logoPath = $fileName;
    }

    // Update settings
    $stmt = $pdo->prepare("UPDATE settings 
        SET school_name=?, school_logo=?, theme_color=?, email=?, phone=?, address=? 
        WHERE id=1");
    $stmt->execute([$school_name, $logoPath, $theme_color, $email, $phone, $address]);

    header("Location: index.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container">
  <div class="card shadow p-4">
    <h4 class="mb-3"><i class="fas fa-cog"></i> System Settings</h4>

    <?php if (!empty($_GET['success'])): ?>
      <div class="alert alert-success">Settings updated successfully!</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">School Name</label>
        <input type="text" name="school_name" class="form-control" 
               value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">School Logo</label><br>
        <?php if (!empty($settings['school_logo'])): ?>
          <img src="../uploads/<?php echo htmlspecialchars($settings['school_logo']); ?>" 
               style="height:70px; margin-bottom:10px;">
        <?php endif; ?>
        <input type="file" name="school_logo" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Theme Color</label>
        <input type="color" name="theme_color" class="form-control form-control-color" 
               value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#4361ee'); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Contact Email</label>
        <input type="email" name="email" class="form-control" 
               value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Contact Phone</label>
        <input type="text" name="phone" class="form-control" 
               value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary">💾 Save Settings</button>
    </form>
  </div>
</div>

<script src="https://kit.fontawesome.com/a2e0cfd8c8.js" crossorigin="anonymous"></script>
</body>
</html>
