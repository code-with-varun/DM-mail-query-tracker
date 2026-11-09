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

echo "[" . date('Y-m-d H:i:s') . "] Starting Mail Query Tracker Task Scheduler...\n";

try {
    $ticketModel = new Ticket_model();
    $ticketModel->autoRolloverPendingTickets();
    echo "[" . date('Y-m-d H:i:s') . "] Scheduler Completed successfully.\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
}
