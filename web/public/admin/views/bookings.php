<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/lang.php'; 

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.*, 
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

if (empty($bookings)): ?>
    <p style="color: var(--dry-sage); text-align: center; font-family: var(--font-mono);">No active logs found.</p>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Guest</th>
                <th>Details</th>
                <th>Room</th>
                <th>Period</th>
                <th>Total</th>
                <th>Status</th>
                <th>CMD</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr id="booking-row-<?= $b['id'] ?>">
                    <td>#<?= $b['id'] ?></td>
                    <td>
                        <strong style="color:var(--gold)"><?= htmlspecialchars($b['username']) ?></strong>
                    </td>
                    <td>
                        <div style="font-size:0.7rem; color:var(--dry-sage);">
                            PH: <?= htmlspecialchars($b['phone']) ?><br>
                            ID: <?= htmlspecialchars($b['passport_no'] ?? 'N/A') ?>
                        </div>
                    </td>
                    <td>
                        <?= htmlspecialchars($b['room_number']) ?><br>
                        <small style="color:var(--dry-sage)"><?= htmlspecialchars($b['room_title']) ?></small>
                    </td>
                    <td><?= $b['check_in'] ?> <br> <?= $b['check_out'] ?></td>
                    <td style="color:var(--light-blue)"><?= (int)$b['total_price'] ?></td>
                    <td>
                        <?php 
                            $statusColor = match($b['status']) {
                                'confirmed' => 'var(--light-blue)',
                                'cancelled' => 'var(--danger)',
                                default => 'var(--soft-peach)'
                            };
                        ?>
                        <span style="color:<?= $statusColor ?>"><?= strtoupper($b['status']) ?></span>
                        <?php if($b['payment_status'] == 'paid'): ?>
                            <br><span style="font-size:0.6rem; background:var(--gold); color:black; padding:1px 3px;">PAID</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($b['status'] !== 'cancelled'): ?>
                            <button hx-post="/api/admin/change_status.php?id=<?= $b['id'] ?>&status=cancelled"
                                    hx-confirm="Confirm CANCELLATION of booking #<?= $b['id'] ?>?"
                                    hx-target="#booking-row-<?= $b['id'] ?>"
                                    class="btn-action btn-del">
                                CANCEL
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>