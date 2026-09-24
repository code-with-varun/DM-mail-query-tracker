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
    'master/subactivities' => 'Master/subactivities',
    'master/divisions' => 'Master/divisions',
    'master/categories' => 'Master/categories',

    'employees' => 'Employees/index',
    'employees/create' => 'Employees/create',

    'contacts' => 'Contacts/index',
    'contacts/store' => 'Contacts/store',
    'contacts/update' => 'Contacts/update',
    'contacts/delete' => 'Contacts/delete',
    'contacts/import' => 'Contacts/import',
    'contacts/sample-template' => 'Contacts/download_template',

    'error-tracker' => 'Errortracker/index',
    'error-tracker/store' => 'Errortracker/store',
    'error-tracker/update' => 'Errortracker/update',
    'error-tracker/delete' => 'Errortracker/delete',
    'error-tracker/export' => 'Errortracker/export_csv',
    'error-tracker/import' => 'Errortracker/import',
    'error-tracker/sample-template' => 'Errortracker/download_template',

    'reports' => 'Reports/index',
    'reports/export' => 'Reports/export_csv',

    'audit' => 'Audit/index',
    'audit/reset' => 'Audit/reset',
    'audit/export-backup' => 'Audit/export_backup',

    'api/activities' => 'Api/get_activities',
    'api/sub-activities' => 'Api/get_sub_activities',
    'api/notifications' => 'Api/get_notifications',
    'api/mark-notification-read' => 'Api/mark_read',
];
