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

function sanitize(?string $data): string {
    if ($data === null) {
        return '';
    }
    $clean = str_replace(chr(0), '', $data);
    $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim($clean);
}
