<?php
session_start();

if (isset($_SESSION['chat_history'])) {
    unset($_SESSION['chat_history']);
}

$langCode = $_COOKIE['lang'] ?? 'en';

$message = ($langCode === 'ua') 
    ? "Журнали пам'яті очищено. Системи перезавантажені." 
    : "Memory logs purged. Systems rebooted.";
?>
<div class="chat-msg mark">
    <span class="mark-label">SYSTEM:</span>
    <span style="color: var(--dry-sage); font-style: italic; font-size: 0.8rem;">
        // <?= htmlspecialchars($message) ?>
    </span>
</div>