<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$stmt = $pdo->query("
    SELECT f.id, ft.name 
    FROM features f 
    LEFT JOIN feature_translations ft ON f.id = ft.feature_id 
    JOIN languages l ON ft.language_id = l.id
    WHERE l.code = 'en'
    ORDER BY f.id ASC
");
$features = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="text-align: right; margin-bottom: 1rem;">
    <button hx-get="/admin/forms/feature_form.php" 
            hx-target="#form-container" 
            hx-swap="innerHTML"
            class="btn-action btn-main" style="padding: 10px 20px;">
        + ADD FEATURE
    </button>
</div>

<div id="form-container"></div>

<table class="admin-table">
    <thead>
        <tr>
            <th style="width: 50px;">ID</th>
            <th>Name (System/EN)</th>
            <th style="width: 150px;">Actions</th>
        </tr>
    </thead>
    <tbody id="features-list">
        <?php foreach ($features as $f): ?>
            <?php include $projectRoot . '/partials/admin/feature_row.php'; ?>
        <?php endforeach; ?>
    </tbody>
</table>