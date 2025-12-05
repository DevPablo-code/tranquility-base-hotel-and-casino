<?php
$projectRoot = dirname(__DIR__, 3); 

$dbPath = $projectRoot . '/config/db.php';
$partialsPath = $projectRoot . '/partials/';
$langPath = $projectRoot . '/lang/'; // Шлях до папки з перекладами UI

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    die("Database config missing");
}

// 1. Отримуємо код мови
$lang_code = $_GET['lang'] ?? 'en';

// 2. Підключаємо файл перекладів UI
$transFile = $langPath . $lang_code . '.php';
// Якщо файл є - беремо його, якщо ні - фолбек на англійську
$ui = file_exists($transFile) ? require $transFile : require $langPath . 'en.php';

$query = $_POST['query'] ?? '';
$features = $_POST['features'] ?? [];

$params = [];
$conditions = ["1=1"]; 

$params[":lang"] = $lang_code;

// 3. Текстовий пошук (Тепер шукаємо і в ft.name!)
if (!empty($query)) {
    // Додали OR ft.name LIKE :q
    $conditions[] = "(r.number LIKE :q OR rt.title LIKE :q OR rt.description LIKE :q OR ft.name LIKE :q)";
    $params[':q'] = "%$query%";
}

// 4. Фільтр по чекбоксах (залишається жорстким)
if (!empty($features)) {
    foreach ($features as $i => $fid) {
        $k = ":f$i";
        $conditions[] = "r.id IN (SELECT room_id FROM room_features WHERE feature_id = $k)";
        $params[$k] = $fid;
    }
}

$whereSQL = implode(' AND ', $conditions);

// 5. Оновлений SQL
// Ми додаємо JOIN feature_translations (ft)
// І обов'язково фільтруємо ft по language_id, який беремо з таблиці languages (l)
$sql = "SELECT r.*, rt.title, rt.description, 
        GROUP_CONCAT(ft.name SEPARATOR ',') as features
        FROM rooms r
        JOIN room_translations rt ON r.id = rt.room_id
        LEFT JOIN room_features rf ON r.id = rf.room_id
        LEFT JOIN features f ON rf.feature_id = f.id
        -- Приєднуємо переклади фіч. Важливо: feature_id + language_id
        LEFT JOIN feature_translations ft ON f.id = ft.feature_id 
        JOIN languages l ON rt.language_id = l.id AND ft.language_id = l.id
        
        WHERE l.code = :lang AND $whereSQL
        
        GROUP BY r.id, rt.title, rt.description, r.number, r.price, r.image, r.status
        ORDER BY r.price ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rooms) {
        foreach ($rooms as $room) {
            // Передаємо масив $ui у partial, щоб там теж перекласти кнопки
            include $partialsPath . 'room_card.php';
        }
    } else {
        // Використовуємо переклад для повідомлення "Не знайдено"
        echo '<div class="col-span-full text-center py-12 border border-dashed border-gray-700 text-gray-500">
                <p class="uppercase tracking-widest">' . htmlspecialchars($ui['no_results']) . '</p>
              </div>';
    }
} catch (PDOException $e) {
    echo "<div class='text-red-500'>" . $ui['system_failure'] . ": " . $e->getMessage() . "</div>";
}
?>