<?php 
    $projectRoot = __DIR__ . '/../';
    $lang_code = $_GET['lang'] ?? 'en';
    $transFile = $projectRoot . 'lang/' . $lang_code . '.php';
    $ui = file_exists($transFile) ? require $transFile : require $projectRoot . 'lang/en.php';

    $dbPath = $projectRoot . '/config/db.php';

    if (file_exists($dbPath)) {
        require_once $dbPath;
    } else {
        die("Configuration Error");
    }   

    $stmtFeatures = $pdo->prepare("
        SELECT f.id, ft.name 
        FROM features f
        JOIN feature_translations ft ON f.id = ft.feature_id
        JOIN languages l ON ft.language_id = l.id
        WHERE l.code = :lang
        ORDER BY f.id ASC
    ");
    $stmtFeatures->execute([':lang' => $lang_code]);
    $allFeatures = $stmtFeatures->fetchAll(PDO::FETCH_ASSOC);

    $checkedFeatures = $_GET['features'] ?? [];
?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tranquility Base Hotel & Casino</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Montserrat:wght@300;400&family=Courier+Prime&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/assets/style.css">
    
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
</head>
<body>
    <?php
        function getLangUrl($newLang) {
            $params = $_GET;
            $params['lang'] = $newLang;
            return '?' . http_build_query($params);
        }
    ?>

    <header class="site-header">
        <?php 
            $urlParams = $_GET; 
            $isOob = false; 
            include $projectRoot . '/partials/lang_switcher.php'; 
        ?>
        
        <h1 class="brand-title">Tranquility Base</h1>
        <p class="brand-subtitle">Hotel & Casino • Mare Tranquillitatis</p>
    </header>

    <main>
        <div class="search-container">
            <form id="search-form">
                <div class="search-box">
                    <input type="text" 
                           name="query" 
                           class="search-input"
                           placeholder="<?= $ui['search_placeholder'] ?>"
                           value="<?= htmlspecialchars($_GET['query'] ?? '') ?>"
                           autocomplete="off"
                           hx-post="/api/room/search.php?lang=<?= $lang_code ?>"
                           hx-trigger="keyup changed delay:500ms"
                           hx-target="#room-grid"
                           hx-indicator="#loading">
                    
                    <span id="loading" class="htmx-indicator">
                        // SCANNING...
                    </span>
                </div>

            <div class="search-filters">
                <?php foreach ($allFeatures as $feature): ?>
                    <label class="filter-label">
                        <input type="checkbox" name="features[]" value="<?= $feature['id'] ?>" 
                            class="filter-checkbox"
                            
                            <?= (in_array($feature['id'], $checkedFeatures)) ? 'checked' : '' ?>
                            
                            hx-post="/api/room/search.php?lang=<?= $lang_code ?>" 
                            hx-target="#room-grid" 
                            hx-include="#search-form">
                        
                        <?= htmlspecialchars($feature['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            </form>
        </div>

        <div id="room-grid" class="room-grid">
            <?php 
                $_POST['query'] = ''; 
                $_GET['lang'] = $lang_code;
                include 'api/room/search.php'; 
            ?>
        </div>
    </main>

    <footer class="site-footer">
        <p>"<?= $ui['footer_quote'] ?? '"Mark speaking, please tell me how may I direct your call?' ?>"</p>
    </footer>

</body>
</html>