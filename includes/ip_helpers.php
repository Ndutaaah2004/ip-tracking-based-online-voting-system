<?php

declare(strict_types=1);

/**
 * Best-effort client IP for logging (direct TCP peer; use proxy headers only if you terminate TLS/proxy correctly).
 */
function get_client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($ip === '') {
        return 'unknown';
    }

    return strlen($ip) > 45 ? substr($ip, 0, 45) : $ip;
}
