<?php
$projectRoot = dirname(__DIR__, 3); 

$dbPath = $projectRoot . '/config/db.php';

$partialsPath = $projectRoot . '/partials/'; 
$langPath = $projectRoot . '/lang/';

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    die("Configuration Error");
}

$lang_code = $_GET['lang'] ?? 'en';
$transFile = $langPath . $lang_code . '.php';

$ui = file_exists($transFile) ? require $transFile : require $langPath . 'en.php';

$query = $_POST['query'] ?? '';
$features = $_POST['features'] ?? [];

$params = [];
$conditions = ["1=1"]; 

$params[":lang"] = $lang_code;

if (!empty($query)) {
    $conditions[] = "(r.number LIKE :q1 
    OR rt.title LIKE :q2 
    OR rt.description LIKE :q3 
    OR ft.name LIKE :q4
    OR rt.title LIKE :super_q1
    OR ft.name LIKE :super_q2
    OR (CHAR_LENGTH(:raw_q1) < 10 AND levenshtein(ft.name, :raw_q2) <= 2)
    OR (CHAR_LENGTH(:raw_q3) < 10 AND levenshtein(rt.title, :raw_q4) <= 3)
    )";

    $searchString = "%$query%";
    $chars = mb_str_split($query);
    $superFuzzy = '%' . implode('%', $chars) . '%';

    $params[':q1'] = $searchString;
    $params[':q2'] = $searchString;
    $params[':q3'] = $searchString;
    $params[':q4'] = $searchString;

    $params[':super_q1'] = $superFuzzy;
    $params[':super_q2'] = $superFuzzy;
    
    $params[':raw_q1'] = $query;
    $params[':raw_q2'] = $query;
    $params[':raw_q3'] = $query;
    $params[':raw_q4'] = $query;
}

if (!empty($features)) {
    foreach ($features as $i => $fid) {
        $k = ":f$i";
        $conditions[] = "r.id IN (SELECT room_id FROM room_features WHERE feature_id = $k)";
        $params[$k] = $fid;
    }
}

$whereSQL = implode(' AND ', $conditions);

$sql = "SELECT r.*, rt.title, rt.description, 
        GROUP_CONCAT(ft.name SEPARATOR ',') as features
        FROM rooms r
        
        JOIN room_translations rt ON r.id = rt.room_id
        
        LEFT JOIN room_features rf ON r.id = rf.room_id
        LEFT JOIN features f ON rf.feature_id = f.id

        LEFT JOIN feature_translations ft ON f.id = ft.feature_id 
        
        JOIN languages l ON rt.language_id = l.id AND (ft.language_id IS NULL OR ft.language_id = l.id)
        
        WHERE l.code = :lang AND $whereSQL
        
        GROUP BY r.id, rt.title, rt.description, r.number, r.price, r.image, r.status
        ORDER BY r.price ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $urlParams = [
    'lang' => $lang_code,
    'query' => $query
    ];

    if (!empty($features)) {
        $urlParams['features'] = $features;
    }

    if (isset($_SERVER['HTTP_HX_REQUEST'])) {
        $queryString = http_build_query($urlParams);

        header("HX-Replace-Url: /?" . $queryString);
    }

    if ($rooms) {
        foreach ($rooms as $room) {
            include $partialsPath . 'room_card.php';
        }
    } else {
        echo '<div class="no-results">
                <p>' . htmlspecialchars($ui['no_results']) . '</p>
              </div>';
    }

    $isOob = true;
    include $partialsPath . 'lang_switcher.php';

} catch (PDOException $e) {
    echo "<div class='no-results' style='border-color: var(--midnight-violet); color: var(--soft-peach);'>" 
         . $ui['system_failure'] . ": " . $e . 
         "</div>";
}
?>