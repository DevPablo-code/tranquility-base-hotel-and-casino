<?php
session_start();

$projectRoot = __DIR__ . '/../';
require_once $projectRoot . '/config/lang.php';
require_once $projectRoot . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /"); 
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT b.*, r.number, rt.title, r.price 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN room_translations rt ON r.id = rt.room_id AND rt.language_id = (SELECT id FROM languages WHERE code = :lang)
    WHERE b.user_id = :user_id 
    ORDER BY b.check_in DESC
");
$stmt->execute([':user_id' => $userId, ':lang' => $lang_code]);
$userBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui['page_title'] = ($lang_code == 'ua') ? 'Мої Бронювання' : 'My Reservations';

include $projectRoot . '/partials/header.php';
?>

<div class="main-container" style="padding-top: 10rem;">
    <h1 class="brand-title" style="text-align: center; margin-bottom: 2rem;"><?= $ui['page_title'] ?></h1>

    <?php if (empty($userBookings)): ?>
        <p class="no-results"><?= ($lang_code == 'ua') ? 'Активних бронювань немає.' : 'No active reservations found.' ?></p>
    <?php else: ?>
        <div class="booking-list-container" style="display: flex; flex-direction: column; gap: 1.5rem; max-width: 800px; margin: 0 auto; margin-bottom: 2rem;">
            
            <?php foreach ($userBookings as $booking): 
                $statusColor = ($booking['status'] == 'confirmed' || $booking['status'] == 'paid') ? 'var(--light-blue)' : 'var(--dry-sage)';
                $bookingId = $booking['id'];
                $totalPrice = $booking['total_price'];
            ?>
            <div id="booking-<?= $bookingId ?>" class="booking-item" style="border: 1px solid <?= $statusColor ?>; padding: 1.5rem; background: var(--midnight-violet); border-radius: 8px;">
                <h3 style="color: var(--vanilla-custard); font-family: var(--font-heading); margin-bottom: 0.5rem;"><?= htmlspecialchars($booking['title']) ?> (<?= htmlspecialchars($booking['number']) ?>)</h3>
                <p style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--dry-sage);">
                    Check-in: <?= htmlspecialchars($booking['check_in']) ?> | Check-out: <?= htmlspecialchars($booking['check_out']) ?><br>
                    Status: <strong style="color: <?= $statusColor ?>;"><?= htmlspecialchars($booking['status']) ?></strong><br>
                    Total Paid: <strong style="color: var(--gold);"><?= (int)$totalPrice ?> <?= $ui['currency'] ?></strong>
                </p>

                <?php if ($booking['status'] != 'cancelled'): ?>
                <button hx-post="/api/booking/cancel.php?id=<?= $bookingId ?>"
                        hx-target="#booking-<?= $bookingId ?>"
                        hx-swap="outerHTML"
                        class="btn-secondary" style="margin-top: 1rem; border-color: var(--danger); color: var(--danger);">
                    <?= ($lang_code == 'ua') ? 'Скасувати Бронювання' : 'Cancel Reservation' ?>
                </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

        </div>
    <?php endif; ?>
</div>

    <?php 
include $projectRoot . '/partials/footer.php';
?>
    <?php 
include $projectRoot . '/partials/chat_panel.php';
?>
</body>
</html>