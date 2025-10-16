<?php
require 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // Login success
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "❌ Wrong username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Login - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="card shadow p-4" style="width: 350px;">
    <h3 class="text-center mb-4">School CMS Login</h3>

    <?php if($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>
</div>

</body>
</html>
