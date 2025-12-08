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

<?php include $projectRoot . '/partials/header.php'; ?>

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
                <option value="feat_asc" <?= ($currentSort == 'feat_asc') ? 'selected' : '' ?>>
                    <?= $ui['sort_feat_asc'] ?>
                </option>
                <option value="feat_desc" <?= ($currentSort == 'feat_desc') ? 'selected' : '' ?>>
                    <?= $ui['sort_feat_desc'] ?>
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

    <?php 
include $projectRoot . '/partials/footer.php';
?>
    <?php 
include $projectRoot . '/partials/chat_panel.php';
?>
</body>
</html>