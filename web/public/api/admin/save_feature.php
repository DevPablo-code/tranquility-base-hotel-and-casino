<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit; }

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$id = $_POST['id'] ?? '';
$nameEn = trim($_POST['name_en']);
$nameUa = trim($_POST['name_ua']);

try {
    $pdo->beginTransaction();

    if ($id) {
        $stmt = $pdo->prepare("UPDATE features SET name = ? WHERE id = ?");
        $stmt->execute([$nameEn, $id]);
        $featureId = $id;
    } else {
        $stmt = $pdo->prepare("INSERT INTO features (name) VALUES (?)");
        $stmt->execute([$nameEn]);
        $featureId = $pdo->lastInsertId();
    }

    $langs = $pdo->query("SELECT code, id FROM languages")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $pdo->prepare("DELETE FROM feature_translations WHERE feature_id = ?")->execute([$featureId]);
    
    $stmtTrans = $pdo->prepare("INSERT INTO feature_translations (feature_id, language_id, name) VALUES (?, ?, ?)");
    
    if (isset($langs['en'])) $stmtTrans->execute([$featureId, $langs['en'], $nameEn]);
    if (isset($langs['ua'])) $stmtTrans->execute([$featureId, $langs['ua'], $nameUa]);

    $pdo->commit();

    echo "<div style='color:var(--light-blue); margin-top:1rem; text-align:right;'>SAVED</div>";

    $f = ['id' => $featureId, 'name' => $nameEn];
    
    ob_start();
    include $projectRoot . '/partials/admin/feature_row.php';
    $rowHtml = ob_get_clean();

   echo "<table style='display: none'>";

    if ($id) {
        echo str_replace('<tr', '<tr hx-swap-oob="true"', $rowHtml);
    } else {
        echo '<tbody hx-swap-oob="beforeend:#features-list">';
        echo $rowHtml;
        echo '</tbody>';
    }

    echo "</tbody></table>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<div style='color:var(--danger);'>Error: " . $e->getMessage() . "</div>";
}
?>