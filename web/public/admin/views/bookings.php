<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/lang.php'; 

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.id, b.check_in, b.check_out, b.status, b.total_price, b.payment_status,
            b.first_name, b.last_name, b.phone, b.passport_no, b.transaction_id,
            b.user_id,
            r.number as room_number, 
            u.username,
            rt.title as room_title
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN rooms r ON b.room_id = r.id
        LEFT JOIN room_translations rt ON r.id = rt.room_id AND rt.language_id = (SELECT id FROM languages WHERE code = :lang)
        ORDER BY b.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([':lang' => $lang_code]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div style='color:var(--danger)'>DB Error: " . $e->getMessage() . "</div>";
    exit;
}
?>

<div style="text-align: right; margin-bottom: 1rem;">
    <button hx-get="/admin/forms/booking_form.php" 
            hx-target="#booking-form-container" 
            hx-swap="innerHTML"
            class="btn-action btn-main" style="padding: 10px 20px;">
        + CREATE RESERVATION
    </button>
</div>

<div id="booking-form-container"></div>

<?php if (empty($bookings)): ?>
    <p style="color: var(--dry-sage); text-align: center; font-family: var(--font-mono);">No logs found.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Guest Identity</th>
                <th>Room Info</th>
                <th>Dates</th>
                <th>Total</th>
                <th>Status</th>
                <th>CMD</th>
            </tr>
        </thead>
        <tbody id="bookings-list">
            <?php foreach ($bookings as $b): ?>
                <?php include $projectRoot . '/partials/admin/booking_row.php'; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>