<?php
// www/admin/views/room_details.php
session_start();
$projectRoot = dirname(__DIR__, 2);

if (($_SESSION['role'] ?? 'guest') !== 'admin') {
    http_response_code(403);
    die("Access Denied.");
}

require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/lang.php'; 

$roomId = $_GET['id'] ?? null;

if (!$roomId) {
    die("<div style='color:var(--danger);'>ERROR: Room ID not specified.</div>");
}

try {
    // 1. ОСНОВНІ ДАНІ КІМНАТИ (Без змін)
    $stmtCore = $pdo->prepare("
        SELECT 
            r.*, 
            MAX(CASE WHEN l.code = 'en' THEN rt.title END) AS title_en,
            MAX(CASE WHEN l.code = 'en' THEN rt.description END) AS desc_en,
            MAX(CASE WHEN l.code = 'ua' THEN rt.title END) AS title_ua,
            MAX(CASE WHEN l.code = 'ua' THEN rt.description END) AS desc_ua
        FROM rooms r
        LEFT JOIN room_translations rt ON r.id = rt.room_id
        JOIN languages l ON rt.language_id = l.id
        WHERE r.id = ?
        GROUP BY r.id
    ");
    $stmtCore->execute([$roomId]);
    $room = $stmtCore->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        die("<div style='color:var(--danger);'>ERROR: Room #$roomId not found.</div>");
    }

    // 2. ФОТОГРАФІЇ (Без змін)
    $stmtPhotos = $pdo->prepare("
        SELECT p.filename, rp.is_primary 
        FROM room_photos rp 
        JOIN photos p ON rp.photo_id = p.id 
        WHERE rp.room_id = ? 
        ORDER BY rp.is_primary DESC, p.id ASC
    ");
    $stmtPhotos->execute([$roomId]);
    $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

    // 3. ФІЧІ (Без змін - числовий метод)
    $stmtFeatures = $pdo->prepare("
        SELECT ft.name, l.code 
        FROM room_features rf 
        JOIN feature_translations ft ON rf.feature_id = ft.feature_id 
        JOIN languages l ON ft.language_id = l.id 
        WHERE rf.room_id = ? 
        ORDER BY l.code, ft.name;
    ");
    $stmtFeatures->execute([$roomId]);
    
    $rawFeatures = $stmtFeatures->fetchAll(PDO::FETCH_NUM); 
    $features = [];
    foreach ($rawFeatures as $f) {
        $code = $f[1]; 
        $features[$code][] = $f; 
    }

    // 4. БРОНЮВАННЯ (ОНОВЛЕНО!)
    // Додали first_name, last_name, phone, transaction_id
    $stmtBookings = $pdo->prepare("
        SELECT 
            b.id, b.check_in, b.check_out, b.status, b.total_price, 
            b.first_name, b.last_name, b.phone, b.transaction_id,
            b.user_id, u.username
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        WHERE b.room_id = ? AND b.check_out >= CURDATE()
        ORDER BY b.check_in ASC
    ");
    $stmtBookings->execute([$roomId]);
    $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Room Details Load Error: " . $e->getMessage());
    die("<div style='color:var(--danger);'>DB Error: Cannot load room data.</div>");
}
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
    <meta charset="UTF-8">
    <title>Room Details #<?= $roomId ?></title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        body { background-color: var(--midnight-violet); color: var(--soft-peach); }
        .detail-card { background: rgba(0,0,0,0.2); padding: 1.5rem; border: 1px solid var(--dry-sage); margin-bottom: 2rem; }
        .detail-heading { color: var(--gold); border-bottom: 1px solid var(--gold); padding-bottom: 5px; margin-top: 0; }
        .photo-thumb { width: 100px; height: 100px; object-fit: cover; border: 2px solid var(--dry-sage); }
        .is-primary { border-color: var(--light-blue); box-shadow: 0 0 5px var(--light-blue); }
    </style>
</head>
<body>

<div class="main-container" style="max-width: 1000px; margin: 0 auto; padding: 2rem;">

    <h1 style="color:var(--soft-peach); border-bottom: 2px solid var(--dry-sage); padding-bottom: 10px;">
        ROOM DETAILS <span style="color:var(--gold);">#<?= htmlspecialchars($room['number']) ?></span> (ID: <?= $roomId ?>)
    </h1>
    
    <a href="/admin/index.php?tab=rooms" class="btn-secondary" style="margin-bottom: 2rem; display: inline-block;">
        ← Back to List
    </a>

    <div class="detail-card">
        <h3 class="detail-heading">CORE SPECIFICATIONS</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1rem; font-family: var(--font-mono); font-size: 0.8rem;">
            <div><strong style="color:var(--light-blue);">Price:</strong> <?= $room['price'] ?> CR</div>
            <div><strong style="color:var(--light-blue);">Capacity:</strong> <?= $room['capacity'] ?> PAX</div>
            <div><strong style="color:var(--light-blue);">Status:</strong> <span style="color:<?= $room['status']=='free'?'var(--light-blue)':'var(--danger)' ?>;"><?= strtoupper($room['status']) ?></span></div>
        </div>
    </div>

    <div class="detail-card">
        <h3 class="detail-heading">TRANSLATIONS</h3>
        </div>
    
    <div class="detail-card">
        <h3 class="detail-heading">FEATURES & AMENITIES</h3>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; font-size: 0.85rem;">
            <?php foreach ($features as $code => $featList): ?>
                <div style="flex-basis: 50%;">
                    <strong style="color:<?= $code=='en'?'var(--light-blue)':'var(--gold)' ?>; border-bottom: 1px dotted <?= $code=='en'?'var(--light-blue)':'var(--gold)' ?>;">
                        <?= strtoupper($code) ?> List:
                    </strong>
                    <ul style="list-style: none; padding: 0; margin-top: 5px;">
                        <?php foreach($featList as $feat): ?>
                            <li style="color:var(--dry-sage); padding: 2px 0;">— <?= htmlspecialchars($feat[0] ?? 'N\A') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="detail-card">
        <h3 class="detail-heading">PHOTO GALLERY (CDN)</h3>
        </div>

    <div class="detail-card">
        <h3 class="detail-heading">ACTIVE/FUTURE BOOKINGS</h3>
        <?php if (empty($bookings)): ?>
            <p style="color:var(--light-blue); font-style: italic;">No current or future reservations found.</p>
        <?php else: ?>
            <table class="admin-table" style="width: 100%; font-size: 0.8rem;">
                <thead>
                    <tr>
                        <th>Guest Info</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Trans. ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr style="color:<?= $b['status']=='confirmed'?'var(--soft-peach)':'var(--dry-sage)' ?>;">
                            <td>
                                <strong style="color:var(--gold); font-size: 0.9rem;">
                                    <?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?>
                                </strong>
                                <br>
                                <span style="font-size:0.7rem;"><?= htmlspecialchars($b['phone']) ?></span>
                                <br>
                                <small style="color:var(--dry-sage);">Account: <?= htmlspecialchars($b['username'] ?? 'N/A') ?></small>
                            </td>
                            
                            <td>
                                <div style="display:flex; flex-direction:column;">
                                    <span>IN: <?= $b['check_in'] ?></span>
                                    <span>OUT: <?= $b['check_out'] ?></span>
                                </div>
                            </td>
                            
                            <td><?= strtoupper($b['status']) ?></td>
                            
                            <td style="color:var(--gold); font-weight:bold;"><?= $b['total_price'] ?> CR</td>
                            
                            <td style="font-family: monospace; font-size: 0.7rem; color: var(--dry-sage);">
                                <?= htmlspecialchars($b['transaction_id'] ?? 'N/A') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>