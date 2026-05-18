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
