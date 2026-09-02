<?php
/**
 * PHP Built-In Development Server Router Script
 * Enables running live preview from any directory without copy-pasting to htdocs
 * Command: C:\xampp\php\php.exe -S localhost:8080 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $uri;

// Serve existing static asset files directly (CSS, JS, fonts, images)
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Forward all virtual routes to Front Controller
$_GET['url'] = ltrim($uri, '/');
require_once __DIR__ . '/index.php';
