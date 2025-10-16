<?php
// setup_admin.php (run once)
require 'includes/config.php';
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$name = 'Administrator';
$stmt = $pdo->prepare("INSERT INTO admins (username, password, name) VALUES (?, ?, ?)");
$stmt->execute([$username, $password, $name]);
echo "✅ Admin user created successfully!";
?>
