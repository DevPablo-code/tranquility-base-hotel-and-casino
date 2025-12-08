<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') exit;

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$id = $_GET['id'] ?? null;
$room = [];
$translations = [];
$currentPhotos = [];
$roomFeatureIds = [];

$stmtAllFeat = $pdo->query("
    SELECT f.id, ft.name 
    FROM features f 
    JOIN feature_translations ft ON f.id = ft.feature_id 
    JOIN languages l ON ft.language_id = l.id 
    WHERE l.code = 'en'
    ORDER BY f.id ASC
");
$allFeatures = $stmtAllFeat->fetchAll(PDO::FETCH_ASSOC);

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtTrans = $pdo->prepare("
        SELECT l.code, rt.title, rt.description 
        FROM room_translations rt
        JOIN languages l ON rt.language_id = l.id
        WHERE rt.room_id = ?
    ");
    $stmtTrans->execute([$id]);
    while ($row = $stmtTrans->fetch(PDO::FETCH_ASSOC)) {
        $translations[$row['code']] = $row;
    }

    $stmtFeat = $pdo->prepare("SELECT feature_id FROM room_features WHERE room_id = ?");
    $stmtFeat->execute([$id]);
    $roomFeatureIds = $stmtFeat->fetchAll(PDO::FETCH_COLUMN);

    $stmtPhotos = $pdo->prepare("
        SELECT p.id as photo_id, p.filename, rp.is_primary 
        FROM room_photos rp 
        JOIN photos p ON rp.photo_id = p.id 
        WHERE rp.room_id = ?
        ORDER BY rp.is_primary DESC
    ");
    $stmtPhotos->execute([$id]);
    $currentPhotos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);
}
?>

<form hx-post="/api/admin/save_room.php" 
      hx-target="#form-message" 
      hx-swap="beforeend" 
      class="crud-form"
      enctype="multipart/form-data">
    
    <input type="hidden" name="id" value="<?= $id ?>">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid var(--dry-sage); padding-bottom:0.5rem;">
        <h3 style="color:var(--gold); margin:0;">
            <?= $id ? 'EDITING UNIT #' . htmlspecialchars($room['number']) : 'INITIALIZING NEW UNIT' ?>
        </h3>
        <button type="button" onclick="this.closest('.crud-form').remove()" class="btn-action btn-del">CLOSE</button> 
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <label class="form-label">Number</label>
            <input type="text" name="number" value="<?= htmlspecialchars($room['number'] ?? '') ?>" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Price (Credits)</label>
            <input type="number" name="price" value="<?= htmlspecialchars($room['price'] ?? '') ?>" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Capacity (Pax)</label>
            <input type="number" name="capacity" value="<?= htmlspecialchars($room['capacity'] ?? 2) ?>" class="form-input" required>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div style="border: 1px dashed var(--dry-sage); padding: 1rem; position: relative;">
            <span style="position:absolute; top:-10px; left:10px; background:var(--midnight-violet); padding:0 5px; color:var(--light-blue); font-size:0.7rem;">ENGLISH</span>
            <div class="form-group">
                <input type="text" name="title_en" placeholder="Title" value="<?= htmlspecialchars($translations['en']['title'] ?? '') ?>" class="form-input" required>
            </div>
            <div class="form-group">
                <textarea name="desc_en" placeholder="Description" class="form-input" rows="3"><?= htmlspecialchars($translations['en']['description'] ?? '') ?></textarea>
            </div>
        </div>
        <div style="border: 1px dashed var(--dry-sage); padding: 1rem; position: relative;">
            <span style="position:absolute; top:-10px; left:10px; background:var(--midnight-violet); padding:0 5px; color:var(--gold); font-size:0.7rem;">UKRAINIAN</span>
            <div class="form-group">
                <input type="text" name="title_ua" placeholder="Назва" value="<?= htmlspecialchars($translations['ua']['title'] ?? '') ?>" class="form-input" required>
            </div>
            <div class="form-group">
                <textarea name="desc_ua" placeholder="Опис" class="form-input" rows="3"><?= htmlspecialchars($translations['ua']['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 2rem; border-top: 1px solid var(--dry-sage); padding-top: 1rem;">
        <h4 style="color:var(--soft-peach); font-family:var(--font-heading); margin-bottom: 1rem; font-size:0.9rem;">MODULE FEATURES</h4>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;">
            <?php foreach ($allFeatures as $feat): ?>
                <label style="color: var(--dry-sage); font-size: 0.8rem; cursor: pointer; display:flex; align-items:center; gap:0.5rem; border: 1px solid var(--dry-sage); padding: 5px; border-radius: 4px;">
                    <input type="checkbox" name="features[]" value="<?= $feat['id'] ?>" 
                           <?= in_array($feat['id'], $roomFeatureIds) ? 'checked' : '' ?>> 
                    <?= htmlspecialchars($feat['name']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="border-top: 1px solid var(--dry-sage); padding-top: 1rem;">
        <h4 style="color:var(--soft-peach); font-family:var(--font-heading); margin-bottom: 1rem; font-size:0.9rem;">VISUAL ARCHIVE</h4>
        
        <?php if (!empty($currentPhotos)): ?>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
            <?php foreach ($currentPhotos as $photo): ?>
                <div id="photo-<?= $photo['photo_id'] ?>" style="position: relative; width: 80px; height: 80px; border: 1px solid var(--dry-sage);">
                    <img src="http://localhost:4000/images/<?= htmlspecialchars($photo['filename']) ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                    <button type="button"
                            hx-post="/api/admin/delete_photo.php?room_id=<?= $id ?>&photo_id=<?= $photo['photo_id'] ?>"
                            hx-target="#photo-<?= $photo['photo_id'] ?>"
                            hx-swap="outerHTML"
                            style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border: none; cursor: pointer; padding: 0 5px;">×</button>
                    <?php if($photo['is_primary']): ?>
                        <span style="position: absolute; bottom: 0; width:100%; text-align:center; background:var(--gold); color:black; font-size:0.5rem;">MAIN</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="background: rgba(255,255,255,0.05); padding: 1rem; display:flex; align-items:center; gap:1rem;">
            <input type="file" name="new_photo" class="form-input" accept="image/*" style="font-size:0.8rem;">
            <label style="color: var(--dry-sage); font-size: 0.8rem;">
                <input type="checkbox" name="is_primary" value="1"> Set Main
            </label>
        </div>
    </div>

    <div style="text-align: right; margin-top: 2rem;">
        <button type="submit" class="btn-action btn-main" style="padding: 10px 30px; font-size: 0.9rem;">
            <?= $id ? 'UPDATE DATA' : 'INITIATE' ?>
        </button>
    </div>
    
    <div id="form-message"></div>
</form>