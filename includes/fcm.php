<?php
/**
 * FCM HTTP v1 helper — sends push notifications to all registered Android devices.
 * Requires: fcm_service_account.json in the project root (downloaded from Firebase Console).
 * Tokens are stored in: fcm_tokens.json in the project root.
 */

function _fcm_base64url(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Exchange a Service Account for a short-lived OAuth2 access token.
 * Result is cached in .fcm_token_cache for up to 50 minutes.
 */
function fcm_get_access_token(array $sa): ?string {
    $cache = __DIR__ . '/../.fcm_token_cache';
    if (file_exists($cache)) {
        $c = json_decode(file_get_contents($cache), true);
        if (!empty($c['token']) && ($c['expires'] ?? 0) > time() + 60) {
            return $c['token'];
        }
    }

    // Build JWT assertion
    $header  = _fcm_base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $now     = time();
    $payload = _fcm_base64url(json_encode([
        'iss'   => $sa['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'iat'   => $now,
        'exp'   => $now + 3600,
    ]));

    $unsigned = $header . '.' . $payload;
    $key = openssl_pkey_get_private($sa['private_key']);
    if (!$key) return null;
    openssl_sign($unsigned, $sig, $key, OPENSSL_ALGO_SHA256);
    $jwt = $unsigned . '.' . _fcm_base64url($sig);

    // Exchange for access token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
    ]);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (empty($res['access_token'])) return null;

    file_put_contents($cache, json_encode([
        'token'   => $res['access_token'],
        'expires' => $now + ($res['expires_in'] ?? 3600),
    ]));

    return $res['access_token'];
}

/**
 * Send a push notification to all registered device tokens.
 * Automatically removes tokens that FCM reports as invalid.
 */
function fcm_send(string $title, string $body, array $data = []): bool {
    $saPath = __DIR__ . '/../fcm_service_account.json';
    if (!file_exists($saPath)) return false;

    $sa = json_decode(file_get_contents($saPath), true);
    if (empty($sa['project_id']) || empty($sa['client_email'])) return false;

    $tokensFile = __DIR__ . '/../fcm_tokens.json';
    $tokens = file_exists($tokensFile)
        ? (json_decode(file_get_contents($tokensFile), true) ?? [])
        : [];
    if (empty($tokens)) return false;

    $accessToken = fcm_get_access_token($sa);
    if (!$accessToken) return false;

    $url   = "https://fcm.googleapis.com/v1/projects/{$sa['project_id']}/messages:send";
    $stale = [];
    $ok    = false;

    foreach (array_unique($tokens) as $token) {
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => compact('title', 'body'),
                'data'         => array_map('strval', $data),
                'android'      => [
                    'priority'     => 'high',
                    'notification' => [
                        'sound'      => 'default',
                        'channel_id' => 'readymart_orders',
                    ],
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200) {
            $ok = true;
        } elseif (in_array($code, [400, 404])) {
            $stale[] = $token;  // unregistered / invalid token
        }
    }

    if (!empty($stale)) {
        $tokens = array_values(array_diff($tokens, $stale));
        file_put_contents($tokensFile, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    return $ok;
}
