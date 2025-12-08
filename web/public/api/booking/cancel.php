<?php
session_start();
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/lang.php';

$bookingId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$bookingId) {
    http_response_code(401);
    die("Authorization error.");
}

try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status != 'cancelled'");
    $stmt->execute([$bookingId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        $msg = ($lang_code == 'ua') ? 'Бронювання скасовано. Кошти буде повернено.' : 'Reservation cancelled. Funds will be returned.';
    } else {
        $msg = ($lang_code == 'ua') ? 'Помилка скасування або вже скасовано.' : 'Error or already cancelled.';
    }

    ?>
    <div style="background: var(--danger); padding: 1.5rem; color: var(--midnight-violet); font-family: var(--font-mono); text-align: center; border-radius: 8px;">
        <strong>[TERMINATED]</strong> <?= $msg ?>
    </div>
    <?php

} catch (Exception $e) {
    http_response_code(500);
    error_log("Cancel Error: " . $e->getMessage());
    echo "<div style='color: var(--danger);'>System Error during cancellation.</div>";
}