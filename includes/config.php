<?php
// includes/config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '127.0.0.1';
$db   = 'school_cms';
$user = 'root';
$pass = ''; // WAMP default
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  die("DB Connection failed: " . $e->getMessage());
}

// Fetch school settings globally
try {
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $GLOBALS['school_settings'] = $stmt->fetch() ?: [];
} catch (Exception $e) {
    $GLOBALS['school_settings'] = [];
}

// Default values (in case DB is empty)
$GLOBALS['school_settings'] = array_merge([
    'school_name' => 'School CMS',
    'school_logo' => null,
    'theme_color' => '#4361ee',
], $GLOBALS['school_settings']);
?>
