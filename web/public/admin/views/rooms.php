<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$stmt = $pdo->query("
    SELECT r.*, rt.title,
    GROUP_CONCAT(f.name SEPARATOR ', ') as features_list
    FROM rooms r 
    LEFT JOIN room_translations rt ON r.id = rt.room_id 
    JOIN languages l ON rt.language_id = l.id

    LEFT JOIN room_features rf ON r.id = rf.room_id
    LEFT JOIN features f ON rf.feature_id = f.id
    LEFT JOIN feature_translations ft ON f.id = ft.feature_id AND ft.language_id = l.id

    WHERE l.code = 'en'
    GROUP BY r.id, rt.title
    ORDER BY r.number ASC
");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="text-align: right; margin-bottom: 1rem;">
    <button hx-get="/admin/forms/room_form.php" 
            hx-target="#form-container" 
            hx-swap="innerHTML"
            class="btn-action btn-main" style="padding: 10px 20px;">
        + INITIALIZE NEW UNIT
    </button>
</div>

<div id="form-container"></div>

<table class="admin-table">
    <thead>
        <tr>
            <th>No.</th>
            <th>Title (EN)</th>
            <th>Price</th>
            <th>Cap.</th>
            <th>Status</th>
            <th>Controls</th>
        </tr>
    </thead>
    <tbody id="rooms-list">
        <?php foreach ($rooms as $r): ?>
            <?php include $projectRoot . '/partials/admin/room_row.php'; ?>
        <?php endforeach; ?>
    </tbody>
</table>