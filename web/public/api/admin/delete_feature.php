<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM features WHERE id = ?");
$stmt->execute([$id]);
?>