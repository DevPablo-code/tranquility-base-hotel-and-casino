<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit; }

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/lang.php'; 

$id = $_POST['id'] ?? '';
$userId = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
$roomId = $_POST['room_id'];
$firstName = trim($_POST['first_name']);
$lastName = trim($_POST['last_name']);
$phone = trim($_POST['phone']);
$passport = trim($_POST['passport_no']);
$checkIn = $_POST['check_in'];
$checkOut = $_POST['check_out'];
$totalPrice = $_POST['total_price'];
$status = $_POST['status'];
$payStatus = $_POST['payment_status'];

try {
    if (empty($totalPrice)) {
        $stmtRoom = $pdo->prepare("SELECT price FROM rooms WHERE id = ?");
        $stmtRoom->execute([$roomId]);
        $pricePerNight = $stmtRoom->fetchColumn();
        
        $d1 = new DateTime($checkIn);
        $d2 = new DateTime($checkOut);
        $days = $d1->diff($d2)->days;
        if ($days < 1) $days = 1;
        $totalPrice = $days * $pricePerNight;
    }

    $pdo->beginTransaction();

    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE bookings SET 
                user_id=?, room_id=?, first_name=?, last_name=?, 
                check_in=?, check_out=?, phone=?, passport_no=?, 
                total_price=?, status=?, payment_status=?
            WHERE id=?
        ");
        $stmt->execute([$userId, $roomId, $firstName, $lastName, $checkIn, $checkOut, $phone, $passport, $totalPrice, $status, $payStatus, $id]);
        $bookingId = $id;
    } else {
        // INSERT
        $stmt = $pdo->prepare("
            INSERT INTO bookings 
                (user_id, room_id, first_name, last_name, check_in, check_out, phone, passport_no, total_price, status, payment_status, transaction_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'MANUAL-ADMIN')
        ");
        $stmt->execute([$userId, $roomId, $firstName, $lastName, $checkIn, $checkOut, $phone, $passport, $totalPrice, $status, $payStatus]);
        $bookingId = $pdo->lastInsertId();
    }

    $pdo->commit();

    $stmtRow = $pdo->prepare("
        SELECT 
            b.*, 
            r.number as room_number, 
            u.username,
            rt.title as room_title
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN rooms r ON b.room_id = r.id
        LEFT JOIN room_translations rt ON r.id = rt.room_id AND rt.language_id = (SELECT id FROM languages WHERE code = :lang)
        WHERE b.id = :id
    ");
    $stmtRow->execute([':lang' => $lang_code, ':id' => $bookingId]);
    $b = $stmtRow->fetch(PDO::FETCH_ASSOC);

    echo "<div style='color:var(--light-blue); margin-top:1rem; text-align:right;'>[SAVED SUCCESSFULLY]</div>";

    ob_start();
    include $projectRoot . '/partials/admin/booking_row.php';
    $rowHtml = trim(ob_get_clean());

    echo "<table style='display: none'>";
    if ($id) {
        echo str_replace('<tr', '<tr hx-swap-oob="true"', $rowHtml);
    } else {
        echo '<tbody hx-swap-oob="beforeend:#bookings-list">';
        echo $rowHtml;
        echo '</tbody>';
    }
    echo "</table>";

    if (!$id) {
        echo '<div hx-swap-oob="innerHTML:#booking-form-container"></div>';
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Save Booking Error: " . $e->getMessage());
    echo "<div style='color:var(--danger);'>Error: " . $e->getMessage() . "</div>";
}
?>