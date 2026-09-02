<?php
/**
 * Mail Query Tracker (MQT)
 * Single Entry Point - Front Controller Pattern
 */

// Load Configuration
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';

// Load Core Engine Classes
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Session.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Router.php';

// Load Helper Functions
require_once __DIR__ . '/app/helpers/url_helper.php';
require_once __DIR__ . '/app/helpers/auth_helper.php';
require_once __DIR__ . '/app/helpers/tat_helper.php';

// Initialize Security Session
Session::init();

// Dispatch Route Request
Router::dispatch();
