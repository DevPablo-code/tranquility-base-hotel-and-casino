<?php
session_start();
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/lang.php';

$bookingId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$bookingId) {
    http_response_code(401);
    die("<div style='color:var(--danger);'>Authorization error.</div>");
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled', payment_status = 'refund_pending' WHERE id = ? AND user_id = ? AND status != 'cancelled'");
    $stmt->execute([$bookingId, $userId]);
    
    $isCancelled = $stmt->rowCount() > 0;
    
    $pdo->commit();

    $stmt = $pdo->prepare("
        SELECT b.*, r.number, rt.title, r.price 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN room_translations rt ON r.id = rt.room_id AND rt.language_id = (SELECT id FROM languages WHERE code = :lang)
        WHERE b.id = :booking_id
    ");
    $stmt->execute([':booking_id' => $bookingId, ':lang' => $lang_code]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
         die("<div style='color:var(--danger);'>Booking not found.</div>");
    }

    $statusColor = 'var(--danger)';
    $totalPrice = $booking['total_price'];
    
    ?>
    <div id="booking-<?= $bookingId ?>" class="booking-item" style="border: 1px solid <?= $statusColor ?>; padding: 1.5rem; background: var(--midnight-violet); border-radius: 8px; opacity: 0.5;">
        <h3 style="color: var(--vanilla-custard); font-family: var(--font-heading); margin-bottom: 0.5rem;"><?= htmlspecialchars($booking['title']) ?> (<?= htmlspecialchars($booking['number']) ?>)</h3>
        <p style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--dry-sage);">
            Check-in: <?= htmlspecialchars($booking['check_in']) ?> | Check-out: <?= htmlspecialchars($booking['check_out']) ?><br>
            Status: <strong style="color: <?= $statusColor ?>;">CANCELLED</strong><br>
            Total Paid: <strong style="color: var(--gold);"><?= (int)$totalPrice ?> <?= $ui['currency'] ?></strong>
        </p>

        <div style="margin-top: 1rem; color: var(--danger); font-family: var(--font-mono); font-size: 0.75rem;">
            <?= ($lang_code == 'ua') ? '[СКАСОВАНО] Кошти буде повернено.' : '[TERMINATED] Funds will be returned.' ?>
        </div>
    </div>
    <?php

} catch (Exception $e) {
    http_response_code(500);
    error_log("Cancel Error: " . $e->getMessage());
    echo "<div style='color: var(--danger);'>System Error during cancellation.</div>";
}
?>