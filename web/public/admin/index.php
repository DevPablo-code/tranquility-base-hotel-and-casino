<?php
session_start();
$projectRoot = dirname(__DIR__, 2);

if (($_SESSION['role'] ?? 'guest') !== 'admin') {
    http_response_code(403);
    die("<body style='background:#050505; color:#a63d1e; display:flex; justify-content:center; align-items:center; height:100vh; font-family:monospace; text-transform:uppercase;'>Access Denied. Clearance Level: Administrator.</body>");
}

require_once $projectRoot . '/config/lang.php';

$currentTab = $_GET['tab'] ?? 'bookings';
$initialUrl = match($currentTab) {
    'rooms' => '/admin/views/rooms.php',
    'features'  => '/admin/views/features.php',
    'logs'  => '/admin/views/logs.php',
    'staff'    => '/admin/views/staff.php',
    default => '/admin/views/bookings.php'
};
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
    <meta charset="UTF-8">
    <title>Admin Console // Tranquility Base</title>
    <link rel="stylesheet" href="/assets/style.css">
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <style>
        body { padding-top: 2rem; background-color: var(--midnight-violet); }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--dry-sage);
            height: 60px;
        }

        .btn-exit {
            text-decoration: none;
            padding: 0.5rem 1.5rem;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--danger); 
            border: 1px solid var(--danger);
            transition: all 0.3s;
            
            white-space: nowrap;
            flex-shrink: 0; 
        }

        .btn-exit:hover {
            background: var(--danger);
            color: white;
            box-shadow: 0 0 10px rgba(255, 68, 68, 0.4);
        }
        
        .admin-nav { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--dry-sage); padding-bottom: 1rem; }
        .admin-nav button { 
            padding: 0.5rem 1.5rem; border: 1px solid var(--dry-sage); background: transparent; 
            color: var(--dry-sage); cursor: pointer; text-transform: uppercase; font-family: var(--font-mono); 
            transition: all 0.3s; letter-spacing: 0.1em;
        }
        .admin-nav button.active, .admin-nav button:hover { 
            background: var(--gold); color: var(--midnight-violet); border-color: var(--gold); font-weight: bold;
        }

        .admin-table { width: 100%; border-collapse: collapse; font-family: var(--font-mono); font-size: 0.8rem; margin-top: 1rem; }
        .admin-table th, .admin-table td { border: 1px solid var(--dry-sage); padding: 0.8rem; text-align: left; color: var(--soft-peach); }
        .admin-table th { background: rgba(212, 175, 55, 0.1); color: var(--gold); text-transform: uppercase; letter-spacing: 0.1em; }
        .admin-table tr:hover { background: rgba(255, 255, 255, 0.05); }

        .crud-form { background: rgba(0,0,0,0.3); padding: 2rem; border: 1px solid var(--gold); margin-bottom: 2rem; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
        
        .btn-action { margin-right: 5px; cursor: pointer; padding: 4px 10px; font-size: 0.7rem; text-transform: uppercase; font-family: var(--font-heading); border: 1px solid transparent; transition: all 0.2s; }
        .btn-edit { border-color: var(--light-blue); color: var(--light-blue); background: transparent; }
        .btn-edit:hover { background: var(--light-blue); color: var(--midnight-violet); }
        .btn-del { border-color: var(--danger); color: var(--danger); background: transparent; }
        .btn-del:hover { background: var(--danger); color: white; }
        .btn-main { background: var(--gold); color: var(--midnight-violet); font-weight: bold; border: 1px solid var(--gold); }
        .btn-main:hover { background: transparent; color: var(--gold); }
    </style>
</head>
<body>

<div class="main-container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
<header class="admin-header">
        <h1 class="brand-title" style="font-size: 2rem; margin:0; line-height: 1;">
            SYSTEM OPERATIONS
        </h1>
        
        <a href="/" class="btn-exit">
            EXIT CONSOLE
        </a>
    </header>
<nav class="admin-nav">
        <button hx-get="/admin/views/bookings.php" 
                hx-target="#admin-content"
                hx-push-url="?tab=bookings"
                onclick="document.querySelectorAll('.admin-nav button').forEach(b => b.classList.remove('active')); this.classList.add('active');"
                class="<?= ($currentTab === 'bookings') ? 'active' : '' ?>">
            BOOKINGS
        </button>
        
        <button hx-get="/admin/views/rooms.php" 
                hx-target="#admin-content"
                hx-push-url="?tab=rooms"
                onclick="document.querySelectorAll('.admin-nav button').forEach(b => b.classList.remove('active')); this.classList.add('active');"
                class="<?= ($currentTab === 'rooms') ? 'active' : '' ?>">
            ROOMS DATABASE
        </button>

        <button hx-get="/admin/views/features.php" 
                hx-target="#admin-content"
                hx-push-url="?tab=features"
                onclick="document.querySelectorAll('.admin-nav button').forEach(b => b.classList.remove('active')); this.classList.add('active');"
                class="<?= ($currentTab === 'features') ? 'active' : '' ?>">
            FEATURES DATABASE
        </button>

        <button hx-get="/admin/views/staff.php" 
            hx-target="#admin-content"
            hx-push-url="?tab=staff"
            onclick="document.querySelectorAll('.admin-nav button').forEach(b => b.classList.remove('active')); this.classList.add('active');"
            class="<?= ($currentTab === 'staff') ? 'active' : '' ?>">
            STAFF DATABASE
        </button>

        <button hx-get="/admin/views/logs.php" 
                hx-target="#admin-content"
                hx-push-url="?tab=logs"
                onclick="document.querySelectorAll('.admin-nav button').forEach(b => b.classList.remove('active')); this.classList.add('active');"
                class="<?= ($currentTab === 'logs') ? 'active' : '' ?>">
            SECURITY LOGS
        </button>
    </nav>

    <div id="admin-content" hx-get="<?= $initialUrl ?>" hx-trigger="load">
        <div class="htmx-indicator" style="color: var(--gold); font-family: var(--font-mono);">
            // INITIALIZING DATA STREAM...
        </div>
    </div>
</div>

</body>
</html>