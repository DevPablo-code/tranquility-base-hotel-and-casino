<?php
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/lang.php'; 
require_once $projectRoot . '/includes/book_form_template.php';

$roomId = $_GET['id'] ?? 0;

echo renderBookingForm($ui, $roomId, []); 
?>