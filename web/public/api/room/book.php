<?php
// www/api/room/book.php
session_start();
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/lang.php'; 
require_once $projectRoot . '/includes/book_form_template.php'; // Підключаємо шаблон

// === ОТРИМАННЯ ДАНИХ ===
$roomId = $_POST['room_id'] ?? 0;
$checkIn = $_POST['check_in'] ?? '';
$checkOut = $_POST['check_out'] ?? '';

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$passportNo = trim($_POST['passport'] ?? '');
$notes = trim($_POST['notes'] ?? ''); 

// Банківські дані
$cardNum = str_replace(' ', '', $_POST['card_num'] ?? '');
$cardCVC = $_POST['card_cvc'] ?? '';

// === ВАЛІДАЦІЯ ===

// 1. Авторизація
if (!isset($_SESSION['user_id'])) {
    echo renderBookingForm($ui, $roomId, array_merge($_POST, ['error_message' => $ui['err_auth']]));
    exit;
}
$userId = $_SESSION['user_id'];

// 2. Дати
if ($checkIn >= $checkOut) {
    echo renderBookingForm($ui, $roomId, array_merge($_POST, ['error_message' => $ui['err_dates']]));
    exit;
}

// 3. Імена
if (strlen($firstName) < 2 || strlen($lastName) < 2) {
    echo renderBookingForm($ui, $roomId, array_merge($_POST, ['error_message' => 'Full Name is required.']));
    exit;
}

// 4. Паспорт
if (strlen($passportNo) < 6) {
    echo renderBookingForm($ui, $roomId, array_merge($_POST, ['error_message' => 'Valid Passport ID required.']));
    exit;
}

try {
    // === ПЕРЕВІРКА ДОСТУПНОСТІ ===
    $checkSql = "SELECT COUNT(*) FROM bookings 
                 WHERE room_id = ? 
                 AND status IN ('confirmed', 'paid') 
                 AND check_in < ? AND check_out > ?";
    
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([$roomId, $checkOut, $checkIn]);
    
    if ($stmt->fetchColumn() > 0) {
        echo renderBookingForm($ui, $roomId, array_merge($_POST, ['error_message' => 'Room unavailable for selected dates.']));
        exit;
    }

    // === РОЗРАХУНОК ЦІНИ ===
    $stmtRoom = $pdo->prepare("SELECT price FROM rooms WHERE id = ?");
    $stmtRoom->execute([$roomId]);
    $pricePerNight = $stmtRoom->fetchColumn();

    $d1 = new DateTime($checkIn);
    $d2 = new DateTime($checkOut);
    $days = $d1->diff($d2)->days;
    if ($days < 1) $days = 1;
    $totalPrice = $days * $pricePerNight;

    // === ОПЛАТА (GO MICROSERVICE) ===
    $bankUrl = 'http://bank:3000/api/process-payment'; 
    $paymentData = [
        'card_number' => $cardNum,
        'amount'      => (float)$totalPrice,
        'currency'    => 'CREDITS',
        'cvv'         => $cardCVC
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
    
    if ($response === false) {
        throw new Exception("Bank connection failed: " . curl_error($ch));
    }
    curl_close($ch);

    $bankResult = json_decode($response, true);

    // Відмова банку
    if ($httpCode !== 200 || ($bankResult['status'] ?? '') !== 'approved') {
        $bankMsg = $bankResult['message'] ?? 'Transaction Declined';
        echo renderBookingForm($ui, $roomId, array_merge($_POST, ['error_message' => 'BANK ERROR: ' . $bankMsg]));
        exit;
    }

    $transactionId = $bankResult['transaction_id'];

    // === ЗБЕРЕЖЕННЯ В БД ===
    $bookingStatus = 'confirmed';
    $paymentStatus = 'paid';

    $insert = $pdo->prepare("
        INSERT INTO bookings (
            user_id, room_id, first_name, last_name, phone, passport_no, 
            check_in, check_out, total_price, notes, 
            status, payment_status, transaction_id
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $insert->execute([
        $userId, $roomId, $firstName, $lastName, $phone, $passportNo,
        $checkIn, $checkOut, $totalPrice, $notes,
        $bookingStatus, $paymentStatus, $transactionId
    ]);

    // Аудит
    require_once $projectRoot . '/includes/functions.php';
    logAction($pdo, $userId, 'CREATE_BOOKING', "Room: $roomId, Total: $totalPrice");

    // === УСПІХ ===
    ?>
    <div class="booking-success" style="border: 1px double var(--gold); padding: 1.5rem; animation: fadeIn 0.5s;">
        <h4 style="color: var(--midnight-violet); font-family: var(--font-heading); margin-bottom: 1rem; letter-spacing: 0.1em;">PAYMENT ACCEPTED</h4>
        
        <div style="text-align: left; font-family: var(--font-mono); font-size: 0.75rem; color: var(--midnight-violet); margin-top: 1rem; border-top: 1px dashed var(--midnight-violet); padding-top: 0.5rem;">
            <div style="display:flex; justify-content:space-between;">
                <span>GUEST:</span> 
                <strong><?= htmlspecialchars($firstName . ' ' . $lastName) ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>PASSPORT:</span> 
                <span><?= htmlspecialchars($passportNo) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span>TXN ID:</span> 
                <span style="font-size:0.65rem"><?= $transactionId ?></span>
            </div>
            
            <div style="margin-top: 10px; font-weight: bold; border-top: 1px solid var(--midnight-violet); padding-top: 5px; display:flex; justify-content:space-between; font-size: 0.9rem;">
                <span>TOTAL:</span>
                <span><?= $totalPrice ?> <?= $ui['currency'] ?></span>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.2em;">
            See you on the dark side of the moon.
        </div>
    </div>
    <?php

} catch (Exception $e) {
    error_log("Booking Critical Error: " . $e->getMessage());
    echo renderBookingForm($ui, $roomId, array_merge($_POST, ['error_message' => $ui['system_failure']]));
}
?>