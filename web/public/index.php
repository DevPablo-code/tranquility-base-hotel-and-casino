<?php 
    $projectRoot = __DIR__ . '/../';

    $dbPath = $projectRoot . '/config/db.php';

    require_once $projectRoot . '/config/lang.php';

    if (file_exists($dbPath)) {
        require_once $dbPath;
    } else {
        die($ui["system_failure"]);
    }

    session_start();
    $user = $_SESSION['username'] ?? null;

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
    $currentSort = $_GET['sort'] ?? 'price_asc';
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
        <div class="user-status">
            <?php if ($user): ?>
                <span class="user-name">:: <?= htmlspecialchars($user) ?> ::</span>
                <a href="/api/auth/logout.php" style="color: var(--dry-sage); text-decoration: none;"><?= $ui["nav_logout"] ?></a>
            <?php else: ?>
                <button hx-get="/api/auth/login_modal.php" 
                        hx-target="body" 
                        hx-swap="beforeend"
                        class="btn-login-header">
                    <?= $ui["nav_identify"] ?>
                </button>
            <?php endif; ?>
        </div>

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

            <div class="search-options">
        
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

        <div class="sort-container">
            <label class="sort-label"><?= $ui['sort_label'] ?>:</label>
            
            <select name="sort" 
                    class="search-sort"
                    hx-post="/api/room/search.php?lang=<?= $lang_code ?>" 
                    hx-target="#room-grid" 
                    hx-include="#search-form">
                
                <option value="price_asc" <?= ($currentSort == 'price_asc') ? 'selected' : '' ?>>
                    <?= $ui['sort_price_asc'] ?>
                </option>
                <option value="price_desc" <?= ($currentSort == 'price_desc') ? 'selected' : '' ?>>
                    <?= $ui['sort_price_desc'] ?>
                </option>
                <option value="cap_asc" <?= ($currentSort == 'cap_asc') ? 'selected' : '' ?>>
                    <?= $ui['sort_cap_asc'] ?>
                </option>
                <option value="cap_desc" <?= ($currentSort == 'cap_desc') ? 'selected' : '' ?>>
                    <?= $ui['sort_cap_desc'] ?>
                </option>
            </select>
        </div>

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
        <p>"<?= $ui['footer_quote'] ?>"</p>
    </footer>

</body>
</html>