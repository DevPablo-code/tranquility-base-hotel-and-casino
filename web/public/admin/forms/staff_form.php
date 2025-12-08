<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$id = $_GET['id'] ?? null;
$staff = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
    $stmt->execute([$id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form hx-post="/api/admin/save_staff.php" 
      hx-target="#form-message"
      class="crud-form"
      style="max-width: 600px; margin: 0 auto 2rem auto;">
    
    <input type="hidden" name="id" value="<?= $id ?>">
    
    <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem; border-bottom:1px solid var(--dry-sage); padding-bottom:0.5rem;">
        <h3 style="color:var(--gold); margin:0;">
            <?= $id ? 'EDIT EMPLOYEE #' . htmlspecialchars($staff['id']) : 'NEW EMPLOYEE' ?>
        </h3>
        <button type="button" onclick="this.closest('.crud-form').remove()" class="btn-action btn-del">CLOSE</button>
    </div>

    <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($staff['full_name'] ?? '') ?>" class="form-input" required>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
            <label class="form-label">Position</label>
            <input type="text" name="position" value="<?= htmlspecialchars($staff['position'] ?? '') ?>" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">Salary (Credits)</label>
            <input type="number" step="0.01" name="salary" value="<?= htmlspecialchars($staff['salary'] ?? 0.00) ?>" class="form-input" required>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Employment Date</label>
        <input type="date" name="employment_date" value="<?= htmlspecialchars($staff['employment_date'] ?? date('Y-m-d')) ?>" class="form-input" required>
    </div>

    <div class="form-group">
        <label class="form-label" style="display:flex; align-items:center; gap: 10px; color: var(--dry-sage);">
            <input type="checkbox" name="is_active" value="1" <?= ($staff['is_active'] ?? 1) ? 'checked' : '' ?>> 
            Currently Active
        </label>
    </div>

    <div style="text-align: right; margin-top: 2rem;">
        <button type="submit" class="btn-action btn-main" style="padding: 10px 30px;">SAVE</button>
    </div>
    
    <div id="form-message"></div>
</form>