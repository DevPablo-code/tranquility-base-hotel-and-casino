<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit; }

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';
require_once $projectRoot . '/config/cdn.php';

$id = $_POST['id'] ?? '';
$number = $_POST['number'];
$price = $_POST['price'];
$capacity = $_POST['capacity'];

$transData = [
    'en' => ['title' => $_POST['title_en'], 'desc' => $_POST['desc_en']],
    'ua' => ['title' => $_POST['title_ua'], 'desc' => $_POST['desc_ua']]
];

try {
    $pdo->beginTransaction();

    if ($id) {
        $stmt = $pdo->prepare("UPDATE rooms SET number=?, price=?, capacity=? WHERE id=?");
        $stmt->execute([$number, $price, $capacity, $id]);
        $roomId = $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO rooms (number, price, capacity, status) VALUES (?, ?, ?, 'free')");
        $stmt->execute([$number, $price, $capacity]);
        $roomId = $pdo->lastInsertId();
    }

    $langs = $pdo->query("SELECT code, id FROM languages")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $pdo->prepare("DELETE FROM room_translations WHERE room_id = ?")->execute([$roomId]);
    
    $stmtTrans = $pdo->prepare("INSERT INTO room_translations (room_id, language_id, title, description) VALUES (?, ?, ?, ?)");
    
    foreach ($transData as $code => $data) {
        if (isset($langs[$code])) {
            $stmtTrans->execute([$roomId, $langs[$code], $data['title'], $data['desc']]);
        }
    }

    $pdo->prepare("DELETE FROM room_features WHERE room_id = ?")->execute([$roomId]);
    if (!empty($_POST['features']) && is_array($_POST['features'])) {
        $stmtFeat = $pdo->prepare("INSERT INTO room_features (room_id, feature_id) VALUES (?, ?)");
        foreach ($_POST['features'] as $featId) {
            if (is_numeric($featId)) {
                $stmtFeat->execute([$roomId, $featId]);
            }
        }
    }

    if (isset($_FILES['new_photo']) && $_FILES['new_photo']['error'] === UPLOAD_ERR_OK) {
        $filename = uploadImageToCdn($_FILES['new_photo']);
        if ($filename) {
            $pdo->prepare("INSERT INTO photos (filename) VALUES (?)")->execute([$filename]);
            $photoId = $pdo->lastInsertId();
            
            $isPrimary = isset($_POST['is_primary']) ? 1 : 0;
            if ($isPrimary) {
                $pdo->prepare("UPDATE room_photos SET is_primary = 0 WHERE room_id = ?")->execute([$roomId]);
            } else {
                $count = $pdo->query("SELECT COUNT(*) FROM room_photos WHERE room_id = $roomId")->fetchColumn();
                if ($count == 0) $isPrimary = 1;
            }

            $pdo->prepare("INSERT INTO room_photos (room_id, photo_id, is_primary) VALUES (?, ?, ?)")
                ->execute([$roomId, $photoId, $isPrimary]);
        }
    }

    $pdo->commit();

$sqlStatus = "SELECT COUNT(*) FROM bookings 
                  WHERE room_id = ? 
                  AND status IN ('confirmed', 'paid') 
                  AND check_in <= CURDATE() 
                  AND check_out > CURDATE()";
    
    $stmtStatus = $pdo->prepare($sqlStatus);
    $stmtStatus->execute([$roomId]);
    $isOccupied = $stmtStatus->fetchColumn() > 0;

    $realStatus = $isOccupied ? 'occupied' : 'free';

    $pdo->prepare("UPDATE rooms SET status = ? WHERE id = ?")->execute([$realStatus, $roomId]);

    $sqlFeat = "SELECT GROUP_CONCAT(ft.name SEPARATOR ', ') 
                FROM room_features rf
                JOIN features f ON rf.feature_id = f.id
                LEFT JOIN feature_translations ft ON f.id = ft.feature_id
                JOIN languages l ON ft.language_id = l.id
                WHERE rf.room_id = ? AND l.code = 'en'";
    
    $stmtFeat = $pdo->prepare($sqlFeat);
    $stmtFeat->execute([$roomId]);
    $featuresStr = $stmtFeat->fetchColumn();

    echo "<div style='color:var(--light-blue); margin-top:1rem; text-align:right; font-family:var(--font-mono);'>
            [DATA SAVED SUCCESSFULLY]
          </div>";

    echo '<div hx-swap-oob="innerHTML:#form-container"></div>';
          
    $r = [
        'id' => $roomId, 
        'number' => $number, 
        'price' => $price, 
        'capacity' => $capacity, 
        'status' => $realStatus,
        'title' => $transData['en']['title'],
        'features_list' => $featuresStr
    ];

    ob_start();
    include $projectRoot . '/partials/admin/room_row.php';
    $rowHtml = ob_get_clean();

    echo "<table style='display: none'><tbody>";

    if ($id) {
        echo str_replace('<tr', '<tr hx-swap-oob="true"', $rowHtml);
    } else {
        echo '<tbody hx-swap-oob="beforeend:#rooms-list">';
        echo $rowHtml;
        echo '</tbody>';
    }

    echo "</tbody></table>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Save Error: " . $e->getMessage());
    echo "<div style='color:var(--danger); margin-top:1rem;'>ERROR: " . $e->getMessage() . "</div>";
}
?>