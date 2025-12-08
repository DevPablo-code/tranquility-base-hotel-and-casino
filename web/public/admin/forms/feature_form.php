<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$id = $_GET['id'] ?? null;
$translations = [];

if ($id) {
    $stmt = $pdo->prepare("
        SELECT l.code, ft.name 
        FROM feature_translations ft
        JOIN languages l ON ft.language_id = l.id
        WHERE ft.feature_id = ?
    ");
    $stmt->execute([$id]);
    $translations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
?>

<form hx-post="/api/admin/save_feature.php" 
      hx-target="#form-message"
      class="crud-form"
      style="max-width: 600px; margin: 0 auto 2rem auto;">
    
    <input type="hidden" name="id" value="<?= $id ?>">
    
    <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem; border-bottom:1px solid var(--dry-sage); padding-bottom:0.5rem;">
        <h3 style="color:var(--gold); margin:0;">
            <?= $id ? 'EDIT FEATURE' : 'NEW FEATURE' ?>
        </h3>
        <button type="button" onclick="this.closest('.crud-form').remove()" class="btn-action btn-del">CLOSE</button>
    </div>

    <div style="display: grid; gap: 1rem;">
        <div>
            <label class="form-label" style="color:var(--light-blue);">Name (EN)</label>
            <input type="text" name="name_en" value="<?= htmlspecialchars($translations['en'] ?? '') ?>" class="form-input" required placeholder="e.g. Wi-Fi">
        </div>

        <div>
            <label class="form-label" style="color:var(--gold);">Назва (UA)</label>
            <input type="text" name="name_ua" value="<?= htmlspecialchars($translations['ua'] ?? '') ?>" class="form-input" required placeholder="напр. Вай-Фай">
        </div>
    </div>

    <div style="text-align: right; margin-top: 2rem;">
        <button type="submit" class="btn-action btn-main" style="padding: 10px 30px;">SAVE</button>
    </div>
    
    <div id="form-message"></div>
</form>