<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM staff ORDER BY is_active DESC, full_name ASC");
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<div style='color:var(--danger)'>Error: " . $e->getMessage() . "</div>";
    exit;
}
?>

<div style="text-align: right; margin-bottom: 1rem;">
    <button hx-get="/admin/forms/staff_form.php" 
            hx-target="#form-container" 
            hx-swap="innerHTML"
            class="btn-action btn-main" style="padding: 10px 20px;">
        + ADD EMPLOYEE
    </button>
</div>

<div id="form-container"></div>

<table class="admin-table">
    <thead>
        <tr>
            <th style="width: 50px;">ID</th>
            <th>Name</th>
            <th>Position</th>
            <th>Salary (CR)</th>
            <th>Employed Since</th>
            <th>Status</th>
            <th style="width: 150px;">Actions</th>
        </tr>
    </thead>
    <tbody id="staff-list">
        <?php foreach ($staff as $s): ?>
            <?php include $projectRoot . '/partials/admin/staff_row.php'; ?>
        <?php endforeach; ?>
    </tbody>
</table>