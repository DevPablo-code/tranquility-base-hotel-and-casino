<?php 
// www/index.php
$projectRoot = __DIR__ . '/../';
$lang_code = $_GET['lang'] ?? 'en';
$transFile = $projectRoot . 'lang/' . $lang_code . '.php';
$ui = file_exists($transFile) ? require $transFile : require $projectRoot . 'lang/en.php';
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tranquility Base Hotel & Casino</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        void: '#050505',       // Космічний чорний
                        surface: '#121212',    // Поверхня карток
                        gold: {
                            DEFAULT: '#D4AF37', // Класичне золото
                            dim: '#8a711f',
                            light: '#f9e6ac'
                        },
                        velvet: '#500000',     // Темно-червоний (казино)
                        moon: '#e0e0e0'        // Місячне світло (текст)
                    },
                    fontFamily: {
                        serif: ['"Cinzel"', 'serif'],
                        sans: ['"Montserrat"', 'sans-serif'],
                    },
                    backgroundImage: {
                        'stars': "url('https://www.transparenttextures.com/patterns/stardust.png')",
                    },
                    boxShadow: {
                        'gold-glow': '0 0 15px rgba(212, 175, 55, 0.15)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #050505;
            color: #e0e0e0;
        }
        /* Зірки на фоні */
        .star-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("https://www.transparenttextures.com/patterns/stardust.png");
            opacity: 0.3; pointer-events: none; z-index: -1;
        }
        /* Градієнтна підсвітка зверху */
        .lunar-glow {
            position: fixed; top: -20%; left: 0; right: 0; height: 50%;
            background: radial-gradient(circle, rgba(30,30,40,0.8) 0%, rgba(5,5,5,0) 70%);
            z-index: -1; pointer-events: none;
        }
    </style>
</head>
<body class="font-sans min-h-screen flex flex-col relative selection:bg-gold selection:text-void">

    <div class="star-bg"></div>
    <div class="lunar-glow"></div>

    <header class="py-10 px-6 text-center relative z-10">
        <h1 class="font-serif text-4xl md:text-6xl text-gold tracking-[0.2em] uppercase mb-2 drop-shadow-lg">
            Tranquility Base
        </h1>
        <div class="h-px w-24 bg-gradient-to-r from-transparent via-gold to-transparent mx-auto mb-3"></div>
        <p class="text-[10px] md:text-xs font-bold tracking-[0.6em] text-gray-500 uppercase">
            Hotel & Casino • Lunar Surface
        </p>
        
        <div class="absolute top-8 right-8 flex gap-4 text-[10px] tracking-widest text-gray-600">
            <a href="?lang=en" class="hover:text-gold transition-colors <?= $lang_code=='en'?'text-gold':'' ?>">EN</a>
            <a href="?lang=ua" class="hover:text-gold transition-colors <?= $lang_code=='ua'?'text-gold':'' ?>">UA</a>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8 relative z-10 max-w-7xl">
        
        <div class="backdrop-blur-md bg-surface/60 border border-white/5 rounded-2xl p-8 mb-16 shadow-2xl max-w-3xl mx-auto">
            <form id="search-form" class="space-y-8">
                
                <div class="relative group">
                    <input type="text" 
                           name="query" 
                           class="w-full bg-void/50 border border-white/10 rounded-lg text-xl text-white placeholder-gray-600 focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/50 py-4 px-6 transition-all text-center tracking-wide"
                           placeholder="<?= $ui['search_placeholder'] ?>"
                           hx-post="/api/room/search?lang=<?= $lang_code ?>"
                           hx-trigger="keyup changed delay:500ms"
                           hx-target="#room-grid"
                           hx-indicator="#loading-bar">
                    
                    <div id="loading-bar" class="htmx-indicator absolute bottom-0 left-0 h-0.5 bg-gold w-full animate-pulse rounded-b-lg"></div>
                </div>

                <div class="flex flex-wrap justify-center gap-4">
                    <label class="cursor-pointer select-none">
                        <input type="checkbox" name="features[]" value="1" 
                               class="peer sr-only"
                               hx-post="/api/room/search?lang=<?= $lang_code ?>" hx-target="#room-grid" hx-include="#search-form">
                        <div class="px-6 py-2 border border-white/10 rounded-full text-xs uppercase tracking-widest text-gray-400 peer-checked:bg-gold peer-checked:text-black peer-checked:border-gold transition-all hover:border-gold/50">
                            Wi-Fi
                        </div>
                    </label>

                    <label class="cursor-pointer select-none">
                        <input type="checkbox" name="features[]" value="2" 
                               class="peer sr-only"
                               hx-post="/api/room/search?lang=<?= $lang_code ?>" hx-target="#room-grid" hx-include="#search-form">
                        <div class="px-6 py-2 border border-white/10 rounded-full text-xs uppercase tracking-widest text-gray-400 peer-checked:bg-gold peer-checked:text-black peer-checked:border-gold transition-all hover:border-gold/50">
                            Moon View
                        </div>
                    </label>
                </div>
            </form>
        </div>

        <div id="room-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
                $_POST['query'] = ''; 
                $_GET['lang'] = $lang_code;
                include 'api/room/search.php'; 
            ?>
        </div>

    </main>

    <footer class="py-12 text-center border-t border-white/5 bg-void">
        <p class="text-gold/30 text-xs tracking-[0.2em] font-serif italic">"Mark speaking, please tell me how may I direct your call?"</p>
    </footer>

</body>
</html>