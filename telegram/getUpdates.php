<?php
header('Content-Type: application/json; charset=utf-8');

$botToken = '7744214540:AAF1UTsyd9BEkpIIYoeRZWe8Sel7kYZ2J38';
$apiUrl = "https://api.telegram.org/bot{$botToken}/getUpdates";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $error
    ]);
    exit;
}

http_response_code($httpCode);
echo $response;

