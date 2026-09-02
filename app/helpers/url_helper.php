<?php
/**
 * URL Helper Functions
 */

function base_url(string $path = ''): string {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

function site_url(string $path = ''): string {
    return base_url($path);
}

function redirect(string $path): void {
    header("Location: " . base_url($path));
    exit;
}

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
