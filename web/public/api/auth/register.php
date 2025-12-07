<?php
session_start();
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (strlen($username) < 3 || strlen($password) < 4) {
    echo "Error: Username or password too short.";
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo "Error: Callsign already taken.";
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$insert = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'user')");

if ($insert->execute([$username, $hash])) {
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user';

    header("HX-Refresh: true"); 
} else {
    echo "System Error: Registration failed.";
}
?>