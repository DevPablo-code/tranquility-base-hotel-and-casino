<?php
session_start();
if (($_SESSION['role'] ?? 'guest') !== 'admin') { http_response_code(403); exit; }

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$id = $_GET['id'];
$status = $_GET['status'];

$stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

header("HX-Trigger: load");
?>
<tr id="booking-row-<?= $id ?>">
    <td colspan="7" style="text-align:center; color:var(--danger);">BOOKING #<?= $id ?> CANCELLED</td>
</tr>