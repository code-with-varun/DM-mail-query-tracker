<?php
/**
 * CLI Windows Task Scheduler / Cron Job Handler
 * Execute via CLI: php cron/scheduler.php
 */

if (php_sapi_name() !== 'cli' && (!isset($_GET['key']) || $_GET['key'] !== 'MQT_CRON_SECRET')) {
    die("Access Denied: This script can only be run via CLI or with valid cron key.");
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Ticket_model.php';
require_once __DIR__ . '/../app/models/Recurring_model.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Mail Query Tracker Recurring Task Scheduler...\n";

try {
    $recurringModel = new Recurring_model();
    $result = $recurringModel->processScheduler();
    echo "[" . date('Y-m-d H:i:s') . "] Scheduler Completed. Generated tickets: " . $result['generated_count'] . "\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
}
