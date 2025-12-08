<?php

function logAction($pdo, $userId, $action, $details = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt->execute([$userId, $action, $details, $ip]);
    } catch (Exception $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}
?>