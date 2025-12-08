<?php

set_time_limit(0);

ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ini_set('implicit_flush', 1);

while (ob_get_level() > 0) {
    ob_end_flush();
}

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

$langCode = $_COOKIE['lang'] ?? 'en';

$messagesToSend = [];
$messagesToSend[] = [
    "role" => "system", 
    "content" => "You are Mark, hotel concierge. 70s sci-fi style. 
        Your primary task is to answer the user's last question OR offer ONE brief, concise suggestion. 
        DO NOT generate multiple greeting scenarios or options in one response. Use the following data: $jsonContext"
];

$recentHistory = array_slice($chatHistory, -6);
foreach ($recentHistory as $msg) {
    $messagesToSend[] = $msg;
}

$data = [
    "model" => "mark_receptionist",
    "messages" => $messagesToSend,
    "stream" => true,
    "options" => [
        "num_ctx" => 1024,
        "num_predict" => 40,
        "temperature" => 0.6
    ]
];

$fullResponse = "";

$callback = function($ch, $data) use (&$fullResponse) {
    $length = strlen($data);
    
    $jsonObjects = explode("\n", $data);
    
    foreach ($jsonObjects as $jsonStr) {
        if (trim($jsonStr) === '') continue;
        $json = json_decode($jsonStr, true);
        
        if (isset($json['message']['content'])) {
            $token = $json['message']['content'];

            $token = str_replace('*', ' *', $token);
            
            $token = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' $0', $token);

            $fullResponse .= $token;
            
            $safeToken = htmlspecialchars($token);

            $safeToken = str_replace("\n", "<br>", $safeToken);

            $finalOutput = preg_replace('/(\*[^\*]*\*)/', ' <span class="mark-action">\1</span> ', $safeToken);
            
            echo "event: message\n";
            echo "data: $finalOutput\n\n";
            
            flush();
        }
        
       if (isset($json['done']) && $json['done'] === true) {
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
    return $length;
};

$ch = curl_init('http://ai:11434/api/chat');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$success = curl_exec($ch);

if ($success === false) {
    echo "event: message\n";
    echo "data: [CONNECTION ERROR: " . curl_error($ch) . "]\n\n";
    echo "event: message\n";
    echo "data: <script>this.closest('[hx-ext]').remove()</script>\n\n";
    flush();
}

curl_close($ch);
?>