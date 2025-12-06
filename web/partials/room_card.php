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
            <span class="card-price">
                <?= (int)$room['price'] ?> <?= $ui['currency'] ?? 'CR' ?>
            </span>
        </div>

        <p class="card-desc">
            <?= htmlspecialchars($room['description']) ?>
        </p>

        <div class="card-features">
            <?php foreach($featuresList as $feat): ?>
                <span class="feature-tag"><?= htmlspecialchars(trim($feat)) ?></span>
            <?php endforeach; ?>
        </div>

        <button class="btn-book"
                hx-post="/api/room/book.php?id=<?= $room['id'] ?>"
                hx-swap="outerHTML">
            <?= $ui['book_btn'] ?? 'Reserve' ?>
        </button>
    </div>
</div>