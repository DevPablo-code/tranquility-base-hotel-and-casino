<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$roomId = $_GET['room_id'];
$photoId = $_GET['photo_id'];

$stmt = $pdo->prepare("DELETE FROM room_photos WHERE room_id = ? AND photo_id = ?");
$stmt->execute([$roomId, $photoId]);

echo "";
?>