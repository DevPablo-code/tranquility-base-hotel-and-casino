<?php
session_start();
$projectRoot = dirname(__DIR__, 3);

require_once $projectRoot . '/config/lang.php';

$dbPath = $projectRoot . '/config/db.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    echo $ui["system_failure"];
    die(); 
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    header("HX-Refresh: true");
    exit;
} else {
    echo $ui["auth_error"];
}
?>