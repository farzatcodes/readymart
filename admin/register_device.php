<?php
/**
 * Called by the Android app whenever FCM issues a new device token.
 * Stores the token in fcm_tokens.json (deduplicates automatically).
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$token = trim($_POST['token'] ?? '');
if (empty($token)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'token is required']);
    exit;
}

$file   = '../fcm_tokens.json';
$tokens = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];

if (!in_array($token, $tokens, true)) {
    $tokens[] = $token;
    file_put_contents($file, json_encode(array_values($tokens), JSON_PRETTY_PRINT));
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'registered' => count($tokens)]);
