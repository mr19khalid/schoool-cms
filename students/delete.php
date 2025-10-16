<?php
// students/delete.php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list.php'); exit;
}
$id = (int)$_GET['id'];

// get student to remove photo
$stmt = $pdo->prepare("SELECT photo FROM students WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$student = $stmt->fetch();

$del = $pdo->prepare("DELETE FROM students WHERE id = ?");
$del->execute([$id]);

// delete photo file if exists
if ($student && $student['photo']) {
    $path = __DIR__ . '/../' . $student['photo'];
    if (file_exists($path)) @unlink($path);
}

header('Location: list.php');
exit;
