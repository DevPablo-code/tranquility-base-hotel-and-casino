<?php
    $user = $_SESSION['username'] ?? null;
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
    <script src="https://unpkg.com/htmx.org@1.9.10/dist/ext/sse.js"></script>
    <script>
        function moveCarousel(button, direction) {
    // Знаходимо контейнер каруселі (div.carousel-container)
    const container = button.closest('.carousel-container');
    if (!container) return;

    const track = container.querySelector('.carousel-track');
    const slides = container.querySelectorAll('.carousel-slide');
    const totalSlides = slides.length;

    let currentIndex = parseInt(track.dataset.currentIndex) || 0;

    let newIndex = currentIndex + direction;

    if (newIndex >= totalSlides) {
        newIndex = 0;
    } else if (newIndex < 0) {
        newIndex = totalSlides - 1;
    }

    const offset = newIndex * -100;

    track.style.transform = `translateX(${offset}%)`;

    track.dataset.currentIndex = newIndex;
}
    </script>
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
               <a href="/my_bookings.php" class="user-link"><?= $ui["nav_reservations"] ?></a> 
                <span class="user-name">:: <?= htmlspecialchars($user) ?> ::</span>
                <?= ($_SESSION['role'] ?? '') == 'admin' ? '<a href="/admin/index.php" class="user-link">:: Control Panel :: </a>' : '' ?>
                <a href="/api/auth/logout.php" class="user-link"><?= $ui["nav_logout"] ?></a>
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
        
        <a href="/"style="text-decoration: none; color: unset; ">
            <h1 class="brand-title">Tranquility Base</h1>
            <p class="brand-subtitle">Hotel & Casino • Mare Tranquillitatis</p>
        </a>

    </header>
