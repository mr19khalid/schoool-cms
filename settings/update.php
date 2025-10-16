<?php
require __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: ../login.php');
  exit;
}

$school_name = $_POST['school_name'] ?? '';
$address = $_POST['address'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$session_year = $_POST['session_year'] ?? '';
$theme_color = $_POST['theme_color'] ?? '#4361ee';

// Fetch existing settings record
$stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
$settings = $stmt->fetch();

$logo_path = $settings['school_logo'] ?? null;

// Upload new logo if provided
if (!empty($_FILES['school_logo']['name'])) {
  $file = $_FILES['school_logo'];
  $filename = time() . '_' . basename($file['name']);
  $target = '../uploads/' . $filename;

  if (move_uploaded_file($file['tmp_name'], $target)) {
    $logo_path = 'uploads/' . $filename;
  }
}

// Update or Insert settings
if ($settings) {
  $sql = "UPDATE settings 
          SET school_name=?, address=?, phone=?, email=?, session_year=?, theme_color=?, school_logo=?
          WHERE id=?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$school_name, $address, $phone, $email, $session_year, $theme_color, $logo_path, $settings['id']]);
} else {
  $sql = "INSERT INTO settings (school_name, address, phone, email, session_year, theme_color, school_logo)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$school_name, $address, $phone, $email, $session_year, $theme_color, $logo_path]);
}

header("Location: index.php?success=1");
exit;
