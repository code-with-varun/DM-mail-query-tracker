<?php
/**
 * Application Configuration File
 * CodeIgniter 3 Style Configuration
 */

// Dynamic base URL detection for XAMPP / Apache deployment
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$dir_name = dirname($script_name);
$dir_name = str_replace('\\', '/', $dir_name);
$dir_name = rtrim($dir_name, '/');

define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($dir_name ? $dir_name : ''));
define('APP_NAME', 'Mail Query Tracker');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Kolkata');

// Set application timezone
date_default_timezone_set(TIMEZONE);

// Session Security Configuration
define('SESSION_NAME', 'MQT_SESSID');
define('SESSION_TIMEOUT', 1800); // 30 minutes inactivity timeout
