<?php
session_start();
$fullResponse = $_POST['full_response'] ?? '';

if (!empty($fullResponse) && isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'][] = ["role" => "assistant", "content" => $fullResponse];
}

http_response_code(204); 
?>