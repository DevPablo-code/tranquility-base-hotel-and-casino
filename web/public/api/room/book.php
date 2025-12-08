<?php
session_start();
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/lang.php'; 

function renderErrorForm($ui, $roomId, $errorMsg, $data) {
    $checkIn = htmlspecialchars($data['check_in'] ?? '');
    $checkOut = htmlspecialchars($data['check_out'] ?? '');
    $phone = htmlspecialchars($data['phone'] ?? '');
    $passportNo = htmlspecialchars($data['passport'] ?? '');
    $cardNum = htmlspecialchars($data['card_num'] ?? '');
    $cardExp = htmlspecialchars($data['card_exp'] ?? '');
    $cardCVC = htmlspecialchars($data['card_cvc'] ?? '');

    ?>
    <form hx-post="/api/room/book.php" hx-swap="outerHTML" class="booking-form" id="booking-form-<?= $roomId ?>">
        
        <div class="booking-error-message" style="border: 2px solid var(--danger); padding: 10px; margin-bottom: 1rem; color: var(--danger); font-family: var(--font-mono); text-align: center; background: rgba(255, 68, 68, 0.1);">
            <?= htmlspecialchars($ui['booking_failed_title'] . ': ' . $errorMsg) ?>
        </div>
        
        <input type="hidden" name="room_id" value="<?= $roomId ?>">
        
        <div class="booking-grid">
            <div>
                <label class="booking-label"><?= $ui['lbl_checkin'] ?></label>
                <input type="date" name="check_in" value="<?= $checkIn ?>" required class="booking-input">
            </div>
            <div>
                <label class="booking-label"><?= $ui['lbl_checkout'] ?></label>
                <input type="date" name="check_out" value="<?= $checkOut ?>" required class="booking-input">
            </div>
        </div>

        <div class="booking-grid">
            <div>
                <label class="booking-label"><?= $ui['lbl_phone'] ?></label>
                <input type="tel" name="phone" value="<?= $phone ?>" required class="booking-input" placeholder="+380...">
            </div>
            <div>
                <label class="booking-label"><?= $ui['lbl_passport'] ?></label>
                <input type="text" name="passport" value="<?= $passportNo ?>" required class="booking-input" placeholder="AA123456">
            </div>
        </div>

        <div style="border: 1px solid var(--gold); padding: 10px; margin-bottom: 1rem; background: rgba(212, 175, 55, 0.05);">
            <div style="font-family: var(--font-heading); color: var(--gold); font-size: 0.7rem; margin-bottom: 0.5rem; letter-spacing: 0.1em;">
                <?= $ui['pay_title'] ?>
            </div>
            
            <div style="margin-bottom: 0.5rem;">
                <label class="booking-label"><?= $ui['lbl_card_num'] ?></label>
                <input type="text" name="card_num" value="<?= $cardNum ?>" required class="booking-input" 
                       placeholder="0000 0000 0000 0000" maxlength="19" 
                       style="letter-spacing: 0.1em; font-family: monospace;">
            </div>
            
            <div class="booking-grid" style="margin-bottom: 0;">
                <div>
                    <label class="booking-label"><?= $ui['lbl_card_exp'] ?></label>
                    <input type="text" name="card_exp" value="<?= $cardExp ?>" required class="booking-input" placeholder="MM/YY" maxlength="5">
                </div>
                <div>
                    <label class="booking-label"><?= $ui['lbl_card_cvc'] ?></label>
                    <input type="password" name="card_cvc" value="<?= $cardCVC ?>" required class="booking-input" placeholder="123" maxlength="3">
                </div>
            </div>
        </div>
        
        <div class="booking-actions">
            <button type="submit" class="btn-confirm">
                <?= $ui['btn_confirm'] ?>
            </button>
            <button type="button" 
                    hx-get="/api/room/reset_form_button.php?id=<?= $roomId ?>" 
                    hx-target="#booking-form-<?= $roomId ?>" 
                    hx-swap="outerHTML"
                    class="btn-secondary">
                <?= $ui['btn_cancel'] ?>
            </button>
        </div>
    </form>
    <?php
}

if (!isset($_SESSION['user_id'])) {
    renderErrorForm($ui, $roomId, $ui['err_auth'], $_POST);
    exit;
}

$userId = $_SESSION['user_id'];
$roomId = $_POST['room_id'];
$checkIn = $_POST['check_in'];
$checkOut = $_POST['check_out'];

$phone = trim($_POST['phone'] ?? '');
$passportNo = trim($_POST['passport'] ?? '');
$notes = trim($_POST['notes'] ?? ''); 

if ($checkIn >= $checkOut) {
    renderErrorForm($ui, $roomId, $ui['err_dates'], $_POST);
    exit;
}

if (strlen($passportNo) < 6) {
    renderErrorForm($ui, $roomId, 'Passport/ID number is too short.', $_POST);
    exit;
}

try {
    $checkSql = "SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status = 'confirmed' AND check_in < ? AND check_out > ?";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([$roomId, $checkOut, $checkIn]);
    
    if ($stmt->fetchColumn() > 0) {
        renderErrorForm($ui, $roomId, 'Unavailable for these dates.', $_POST);
        exit;
    }

    $roomStmt = $pdo->prepare("SELECT price FROM rooms WHERE id = ?");
    $roomStmt->execute([$roomId]);
    $pricePerNight = $roomStmt->fetchColumn();

    $d1 = new DateTime($checkIn);
    $d2 = new DateTime($checkOut);
    $days = $d1->diff($d2)->days;
    $totalPrice = $days * $pricePerNight;

     $bankUrl = 'http://bank:3000/api/process-payment'; 
    $paymentData = [
        'card_number' => str_replace(' ', '', $_POST['card_num']),
        'amount'      => $totalPrice,
        'currency'    => 'CREDITS',
        'cvv'         => $_POST['card_cvc']
    ];

    $ch = curl_init($bankUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($paymentData),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $bankResult = json_decode($response, true);

    if ($httpCode !== 200 || ($bankResult['status'] ?? '') !== 'approved') {
        $errorMsg = $bankResult['message'] ?? 'Transaction Declined';
        renderErrorForm($ui, $roomId, 'BANK ERROR: ' . $errorMsg, $_POST);
        exit;
    }

    $transactionId = $bankResult['transaction_id'];

    $paymentStatus = 'paid';

    $insert = $pdo->prepare("
        INSERT INTO bookings (user_id, room_id, phone, check_in, check_out, total_price, notes, transaction_id, passport_no, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([$userId, $roomId, $phone, $checkIn, $checkOut, $totalPrice, $notes, $transactionId, $passportNo, $paymentStatus]);

    require_once $projectRoot . '/includes/functions.php';
    logAction($pdo, $userId, 'CREATE_BOOKING', "Room ID: $roomId, Total: $totalPrice");

    ?>
    <div class="booking-success" style="border: 1px double var(--gold); padding: 1.5rem;">
        <h4 style="color: var(--midnight-violet);">PAYMENT ACCEPTED</h4>
        <div style="text-align: left; font-family: var(--font-mono); font-size: 0.75rem; color: var(--midnight-violet); margin-top: 1rem; border-top: 1px dashed var(--midnight-violet); padding-top: 0.5rem;">
            <div>GUEST: <?= htmlspecialchars($_SESSION['username']) ?></div>
            <div>PASSPORT: <?= htmlspecialchars($passportNo) ?></div>
            <div>TXN ID: <?= $transactionId ?></div>
            <div style="margin-top: 5px; font-weight: bold;">
                PAID: <?= $totalPrice ?> <?= $ui['currency'] ?>
            </div>
        </div>
        
        <div style="margin-top: 1rem; font-size: 0.6rem; text-transform: uppercase;">
            See you on the dark side of the moon.
        </div>
    </div>
    <?php

} catch (Exception $e) {
    error_log("Booking Error: " . $e->getMessage());
    renderErrorForm($ui, $roomId, $ui['system_failure'], $_POST);
}