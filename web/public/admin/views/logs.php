<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

try {
    $stmt = $pdo->query("
        SELECT a.*, u.username 
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 100
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<div style='color:var(--danger)'>Error: " . $e->getMessage() . "</div>";
    exit;
}
?>

<div style="margin-bottom: 1rem; display:flex; justify-content:space-between; align-items:center;">
    <h3 style="color:var(--dry-sage); font-family:var(--font-heading); margin:0;">SYSTEM ACTIVITY STREAM</h3>
    <span style="font-size:0.7rem; color:var(--dry-sage);">LAST 100 EVENTS</span>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th style="width: 50px;">ID</th>
            <th style="width: 150px;">Timestamp</th>
            <th style="width: 120px;">User / IP</th>
            <th style="width: 150px;">Action</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody style="font-family: 'Courier New', monospace;">
        <?php foreach ($logs as $log): ?>
            <?php 
                $actionColor = match(true) {
                    str_contains($log['action'], 'DELETE') => 'var(--danger)',
                    str_contains($log['action'], 'SECURITY') => 'var(--danger)',
                    str_contains($log['action'], 'LOGIN') => 'var(--light-blue)',
                    str_contains($log['action'], 'CREATE') => 'var(--gold)',
                    str_contains($log['action'], 'UPDATE') => 'var(--soft-peach)',
                    default => 'var(--dry-sage)'
                };
            ?>
            <tr>
                <td style="color:var(--dry-sage);">#<?= $log['id'] ?></td>
                
                <td style="font-size:0.7rem; color:var(--soft-peach);">
                    <?= $log['created_at'] ?>
                </td>
                
                <td>
                    <?php if($log['username']): ?>
                        <strong style="color:var(--gold);"><?= htmlspecialchars($log['username'] ?? '') ?></strong>
                    <?php else: ?>
                        <span style="color:var(--dry-sage); font-style:italic;">SYSTEM / GUEST</span>
                    <?php endif; ?>
                    <div style="font-size:0.65rem; color:var(--dry-sage); opacity:0.7;">
                        <?= htmlspecialchars($log['ip_address'] ?? '') ?>
                    </div>
                </td>
                
                <td style="color: <?= $actionColor ?>; font-weight:bold;">
                    [ <?= htmlspecialchars($log['action'] ?? '') ?> ]
                </td>
                
                <td style="font-size:0.75rem; color:rgba(255,255,255,0.7);">
                    <?= htmlspecialchars($log['details'] ?? '') ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>