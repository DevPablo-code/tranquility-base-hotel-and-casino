<?php
$featuresList = isset($room['features']) ? explode(',', $room['features']) : [];
?>

<div class="room-card">
    <div class="card-image-wrapper">
        <?php if(!empty($room['image'])): ?>
            <img src="/assets/rooms/<?= htmlspecialchars($room['image']) ?>" 
                 class="card-image" 
                 alt="<?= htmlspecialchars($room['title']) ?>">
        <?php else: ?>
            <div style="height:100%; display:flex; align-items:center; justify-content:center; color:var(--dry-sage);">
                NO VISUAL
            </div>
        <?php endif; ?>
    </div>

    <div class="card-content">
    <div class="card-header">
        <h3 class="card-title"><?= htmlspecialchars($room['title']) ?></h3>
        
        <div class="card-meta">
            <span class="card-price">
                <?= (int)$room['price'] ?><small><?= $ui['currency'] ?? 'CR' ?></small>
            </span>
            
            <div class="card-capacity">
                <?= $room['capacity'] ?> <?= $ui['guests_label'] ?>
            </div>
        </div>
    </div>

    <p class="card-desc">
        <?= htmlspecialchars($room['description']) ?>
    </p>
    
    <div class="card-features">
        <?php foreach($featuresList as $feat): ?>
            <span class="feature-tag"><?= htmlspecialchars(trim($feat)) ?></span>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <button hx-post="/api/room/book_form.php?id=<?= $room['id'] ?>" ...>
            <?= $ui['book_btn'] ?>
        </button>
    <?php else: ?>
        <button hx-get="/api/auth/login_modal.php?lang=<?= $lang_code ?? 'en' ?>" 
                hx-target="body" 
                hx-swap="beforeend"
                class="btn-book">
            <?= $ui['book_btn'] ?>
        </button>
    <?php endif; ?>
</div>
</div>