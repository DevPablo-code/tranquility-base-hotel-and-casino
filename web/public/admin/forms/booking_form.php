<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$id = $_GET['id'] ?? null;
$booking = [];

$users = $pdo->query("SELECT id, username FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
$rooms = $pdo->query("SELECT id, number, price FROM rooms ORDER BY number")->fetchAll(PDO::FETCH_ASSOC);

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form hx-post="/api/admin/save_booking.php" 
      hx-target="#form-message" 
      class="crud-form">
    
    <input type="hidden" name="id" value="<?= $id ?>">
    
    <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem; border-bottom:1px solid var(--dry-sage); padding-bottom:0.5rem;">
        <h3 style="color:var(--gold); margin:0;">
            <?= $id ? 'EDIT RESERVATION #' . $id : 'NEW MANUAL RESERVATION' ?>
        </h3>
        <button type="button" onclick="this.closest('.crud-form').parentElement.innerHTML = ''" class="btn-action btn-del">CLOSE X</button>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label class="form-label">Linked Account</label>
            <select name="user_id" class="form-input" style="background:var(--midnight-violet);">
                <option value="">(No Account Linked)</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($booking['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Room</label>
            <select name="room_id" class="form-input" style="background:var(--midnight-violet);" required>
                <option value="">Select Room...</option>
                <?php foreach ($rooms as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= ($booking['room_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                        Room <?= htmlspecialchars($r['number']) ?> (<?= $r['price'] ?> CR)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label class="form-label" style="color:var(--light-blue);">First Name</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($booking['first_name'] ?? '') ?>" class="form-input" required placeholder="Ivan">
        </div>
        <div>
            <label class="form-label" style="color:var(--light-blue);">Last Name</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($booking['last_name'] ?? '') ?>" class="form-input" required placeholder="Petrenko">
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label class="form-label">Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($booking['phone'] ?? '') ?>" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Passport ID</label>
            <input type="text" name="passport_no" value="<?= htmlspecialchars($booking['passport_no'] ?? '') ?>" class="form-input" required>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label class="form-label">Check-In</label>
            <input type="date" name="check_in" value="<?= $booking['check_in'] ?? '' ?>" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Check-Out</label>
            <input type="date" name="check_out" value="<?= $booking['check_out'] ?? '' ?>" class="form-input" required>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label class="form-label">Total Price</label>
            <input type="number" name="total_price" value="<?= $booking['total_price'] ?? '' ?>" class="form-input" placeholder="Auto-calc">
        </div>
        <div>
            <label class="form-label">Booking Status</label>
            <select name="status" class="form-input">
                <?php foreach(['confirmed', 'pending', 'cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($booking['status'] ?? '') == $s ? 'selected' : '' ?>><?= strtoupper($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Payment Status</label>
            <select name="payment_status" class="form-input">
                <?php foreach(['paid', 'pending', 'failed', 'refund_pending'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($booking['payment_status'] ?? '') == $s ? 'selected' : '' ?>><?= strtoupper($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="text-align: right; margin-top: 2rem;">
        <button type="submit" class="btn-action btn-main" style="padding: 10px 30px;">SAVE RECORD</button>
    </div>
    
    <div id="form-message"></div>
</form>