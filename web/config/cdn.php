<?php

function uploadImageToCdn($fileArray) {
    if ($fileArray['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $cFile = new CURLFile(
        $fileArray['tmp_name'],
        $fileArray['type'],
        $fileArray['name']
    );

    $ch = curl_init('http://cdn:4000/upload');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $cFile]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("CDN Error: $response");
        return null;
    }

    $json = json_decode($response, true);
    return $json['filename'] ?? null;
}
?>