<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit; }

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$id = $_POST['id'] ?? '';
$fullName = trim($_POST['full_name']);
$position = trim($_POST['position']);
$salary = $_POST['salary'];
$employmentDate = $_POST['employment_date'];
$isActive = isset($_POST['is_active']) ? 1 : 0;

if ($salary < 0) {
    http_response_code(400);
    echo "<div style='color:var(--danger);'>ERROR: Salary cannot be negative.</div>";
    exit;
}

try {
    $pdo->beginTransaction();

    if ($id) {
        $stmt = $pdo->prepare("UPDATE staff SET full_name=?, position=?, salary=?, employment_date=?, is_active=? WHERE id=?");
        $stmt->execute([$fullName, $position, $salary, $employmentDate, $isActive, $id]);
        $staffId = $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO staff (full_name, position, salary, employment_date, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $position, $salary, $employmentDate, $isActive]);
        $staffId = $pdo->lastInsertId();
    }

    $pdo->commit();

    echo "<div style='color:var(--light-blue); margin-top:1rem; text-align:right;'>[DATA SAVED SUCCESSFULLY]</div>";

    $s = [
        'id' => $staffId, 
        'full_name' => $fullName, 
        'position' => $position, 
        'salary' => $salary, 
        'employment_date' => $employmentDate,
        'is_active' => $isActive
    ];

    ob_start();
    include $projectRoot . '/partials/admin/staff_row.php';
    $rowHtml = trim(ob_get_clean());

    echo "<table style='display: none'>";
    if ($id) {
        echo str_replace('<tr', '<tr hx-swap-oob="true"', $rowHtml);
    } else {
        echo '<tbody hx-swap-oob="beforeend:#staff-list">';
        echo $rowHtml;
        echo '</tbody>';
    }
    echo "</table>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Save Staff Error: " . $e->getMessage());
    echo "<div style='color:var(--danger);'>ERROR: " . $e->getMessage() . "</div>";
}
?>