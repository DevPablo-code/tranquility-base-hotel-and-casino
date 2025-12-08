<?php
// www/api/chat_stream.php
set_time_limit(0);
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ini_set('implicit_flush', 1);

while (ob_get_level() > 0) {
    ob_end_flush();
}

// Заголовки для потокового виводу (SSE)
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); 

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/db.php';

session_start();
$chatHistory = $_SESSION['chat_history'] ?? [];
session_write_close(); 

try {
    $stmt = $pdo->query("SELECT r.number, t.title, r.price FROM rooms r JOIN room_translations t ON r.id=t.room_id WHERE r.status='free' AND t.language_code='en' LIMIT 3");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonContext = json_encode($rooms);
} catch (Exception $e) {
    $jsonContext = "Data unavailable";
}

$messagesToSend = [];
$messagesToSend[] = [
    "role" => "system", 
    "content" => "You are Mark, hotel concierge. 70s sci-fi style. Your primary task is to answer the user's last question OR offer ONE brief, concise suggestion. DO NOT generate multiple greeting scenarios or options in one response. Use the following room data as context: $jsonContext"
];

$recentHistory = array_slice($chatHistory, -6);
foreach ($recentHistory as $msg) {
    $role = ($msg['role'] === 'mark') ? 'assistant' : 'user'; 
    $messagesToSend[] = ["role" => $role, "content" => $msg['content']];
}

$data = [
    "model" => "mark_receptionist", // ВИКОРИСТОВУЄМО ЛОКАЛЬНУ МОДЕЛЬ
    "messages" => $messagesToSend,
    "stream" => true,
    "options" => [ // ОПЦІЇ, СПЕЦИФІЧНІ ДЛЯ OLLAMA
        "num_ctx" => 1024,
        "num_predict" => 80, 
        "temperature" => 0.6
    ]
];

$fullResponse = "";
$ollamaErrorBody = ""; 

$callback = function($ch, $data) use (&$fullResponse, &$ollamaErrorBody) {
    $length = strlen($data);
    
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
        $ollamaErrorBody .= $data;
        return $length; 
    }

    $jsonObjects = explode("\n", $data);
    
    foreach ($jsonObjects as $jsonStr) {
        if (trim($jsonStr) === '') continue;
        $json = json_decode($jsonStr, true);
        
        // Ollama формат: ['message']['content']
        if (isset($json['message']['content'])) {
            $token = $json['message']['content'];
            
            $fullResponse .= $token;
            
            $safeToken = htmlspecialchars($token);
            $safeToken = str_replace("\n", "<br>", $safeToken);

            $finalOutput = preg_replace('/(\*[^\*]*\*)/', '<span class="mark-action">$1</span>', $safeToken);
            
            echo "event: message\n";
            echo "data: $finalOutput\n\n";
            
            flush();
        }
        
       if (isset($json['done']) && $json['done'] === true) {
            goto finish_stream; 
        }
    }
    return $length;

    finish_stream:
    $fullResponseJs = json_encode($fullResponse);
    echo "event: message\n";
    echo "data: <script>
            htmx.ajax('POST', '/api/chat_save_history.php', {
                values: {full_response: $fullResponseJs}
            });
            document.getElementById('mark-stream-listener').remove();
            const cursor = document.querySelector('#mark-stream-listener .cursor-blink');
            if (cursor) cursor.remove();
        </script>\n\n";
    flush();
    return $length;
};

// --- 4. ВИКОНАННЯ CUrl ---
$ollamaUrl = 'http://ai:11434/api/chat'; // <-- ПОВЕРТАЄМОСЯ ДО ЛОКАЛЬНОГО ENDPOINT
$ch = curl_init($ollamaUrl); 

curl_setopt_array($ch, [
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_WRITEFUNCTION => $callback,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'], // БЕЗ AUTH
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5, 
    CURLOPT_TIMEOUT => 180, 
    CURLOPT_HEADER => false,
]);

$success = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($success === false || $httpCode !== 200) {
    $error = curl_error($ch);
    $errorMsg = "[OLLAMA CONNECTION ERROR]";
    
    if ($httpCode !== 0 && $httpCode !== 200) {
        $errorMsg = "HTTP ERROR ($httpCode): Ollama API failed to respond. Check its logs.";
        $errorData = json_decode($ollamaErrorBody, true);
        if (isset($errorData['error'])) {
             $errorMsg .= " Details: " . $errorData['error'];
        }
    } else if ($httpCode === 0 && $error) {
         $errorMsg = "CONNECTION REFUSED: Is Ollama container running? Error: " . $error;
    }

    error_log("Ollama Failure: " . $errorMsg);
    
    // Вивід помилки та очищення слухача
    echo "event: message\n";
    echo "data: $errorMsg\n\n";
    echo "data: <script>document.getElementById('mark-stream-listener').remove();</script>\n\n";
    flush();

} else {
    if (!str_contains($fullResponse, 'htmx.ajax')) {
        $fullResponseJs = json_encode($fullResponse);
        
        echo "event: message\n";
        echo "data: <script>
            htmx.ajax('POST', '/api/chat_save_history.php', {
                values: {full_response: $fullResponseJs}
            });
            document.getElementById('mark-stream-listener').remove();
            const cursor = document.querySelector('#mark-stream-listener .cursor-blink');
            if (cursor) cursor.remove();
        </script>\n\n";
        flush();
    }
}

curl_close($ch);
?>