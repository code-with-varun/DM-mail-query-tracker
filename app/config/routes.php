<?php
/**
 * Routes Configuration
 * CodeIgniter 3 Style Route Mappings
 */

$routes = [
    '' => 'Auth/login',
    'login' => 'Auth/login',
    'logout' => 'Auth/logout',
    'profile' => 'Auth/profile',
    'change-password' => 'Auth/change_password',

    'dashboard' => 'Dashboard/index',

    'tickets' => 'Tickets/index',
    'tickets/create' => 'Tickets/create',
    'tickets/view/(:num)' => 'Tickets/view/$1',
    'tickets/edit/(:num)' => 'Tickets/edit/$1',
    'tickets/update-status' => 'Tickets/update_status',

    'tasks' => 'Tasks/index',
    'tasks/create' => 'Tasks/create',

    'recurring' => 'Recurring/index',
    'recurring/create' => 'Recurring/create',
    'recurring/trigger' => 'Recurring/trigger_scheduler',

    'hold' => 'Hold/index',
    'hold/release' => 'Hold/release',

    'tracker/input' => 'Tracker/input',
    'tracker/delivery' => 'Tracker/delivery',

    'master/activities' => 'Master/activities',
    'master/divisions' => 'Master/divisions',

    'employees' => 'Employees/index',
    'employees/create' => 'Employees/create',

    'reports' => 'Reports/index',
    'reports/export' => 'Reports/export_csv',

    'audit' => 'Audit/index',

    'api/sub-activities' => 'Api/get_sub_activities',
    'api/notifications' => 'Api/get_notifications',
    'api/mark-notification-read' => 'Api/mark_read',
];
