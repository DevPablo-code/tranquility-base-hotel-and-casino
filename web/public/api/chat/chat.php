<?php
session_start();

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

$rawMessage = $_POST['message'] ?? '';

$cleanMessage = htmlspecialchars($rawMessage);

if (trim($cleanMessage) === '') exit;

if (!isset($_SESSION['chat_history'])) $_SESSION['chat_history'] = [];
$_SESSION['chat_history'][] = ["role" => "user", "content" => $cleanMessage];

?>
<div class="chat-msg user">
    <div class="chat-bubble-user">
        <?= $cleanMessage ?>
    </div>
</div>

<div class="chat-msg mark">
    <span class="mark-label">MARK:</span>
    <span id="mark-stream-listener" 
          hx-ext="sse" 
          sse-connect="/api/chat/stream.php" 
          sse-swap="message"
          hx-swap="beforeend">
          <span class="cursor-blink"></span>
    </span>
</div>