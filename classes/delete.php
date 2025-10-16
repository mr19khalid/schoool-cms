<?php
// classes/delete.php
require __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    // delete class
    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: list.php');
exit;
