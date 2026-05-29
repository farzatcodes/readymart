<?php
/**
 * Format a Bangladesh phone number for tel: links.
 * 01XXXXXXXXX → +8801XXXXXXXXX
 */
function bd_tel(string $phone): string {
    $digits = preg_replace('/\D/', '', trim($phone));
    if ($digits === '') return '';
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        return '+880' . substr($digits, 1);
    }
    if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
        return '+880' . $digits;
    }
    if (str_starts_with($digits, '880')) {
        return '+' . $digits;
    }
    return '+' . $digits;
}

/**
 * Generate (and persist in session) a CSRF token.
 */
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field (echo directly into a form).
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Verify the CSRF token on POST — dies with 403 on mismatch.
 */
function csrf_verify(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted)) {
        http_response_code(403);
        die('Security token mismatch. Please go back and try again.');
    }
}
