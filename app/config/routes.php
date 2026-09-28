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
    'tickets/my-bucket' => 'Tickets/my_bucket',
    'tickets/create' => 'Tickets/create',
    'tickets/view/(:num)' => 'Tickets/view/$1',
    'tickets/edit/(:num)' => 'Tickets/edit/$1',
    'tickets/update-status' => 'Tickets/update_status',
    'tickets/submit-to-checker' => 'Tickets/submit_to_checker',
    'tickets/checker-action' => 'Tickets/checker_action',
    'tickets/deliver' => 'Tickets/deliver',
    'tickets/reschedule' => 'Tickets/reschedule',
    'tickets/reassign' => 'Tickets/reassign',

    'roster' => 'Roster/index',

    'tasks' => 'Tasks/index',
    'tasks/create' => 'Tasks/create',

    'recurring' => 'Recurring/index',
    'recurring/create' => 'Recurring/create',
    'recurring/trigger' => 'Recurring/trigger_scheduler',

    'hold' => 'Hold/index',
    'hold/release' => 'Hold/release',

    'tracker/input' => 'Tracker/input',
    'tracker/delivery' => 'Tracker/delivery',
    'tracker/process-updates' => 'Processupdates/index',
    'tracker/process-updates/store' => 'Processupdates/store',
    'tracker/process-updates/preview/(:num)' => 'Processupdates/preview/$1',
    'tracker/process-updates/download/(:num)' => 'Processupdates/download/$1',
    'tracker/process-updates/delete/(:num)' => 'Processupdates/delete/$1',

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
    'audit/export-excel' => 'Audit/export_excel',
    'audit/import-excel' => 'Audit/import_excel',

    'training/plan' => 'Training/plan',
    'training/add-kt-module' => 'Training/add_kt_module',
    'training/toggle-kt' => 'Training/toggle_kt',
    'training/download-kt/(:num)' => 'Training/download_kt/$1',

    'training/pkt' => 'Training/pkt',
    'training/take-test/(:num)' => 'Training/take_test/$1',
    'training/submit-test' => 'Training/submit_test',

    'training/manage-pkt' => 'Training/manage_pkt',
    'training/add-question' => 'Training/add_question',
    'training/import-questions' => 'Training/import_questions',
    'training/pkt-template' => 'Training/download_pkt_template',
    'training/create-test' => 'Training/create_test',

    'training/practice' => 'Training/practice',
    'training/submit-practice' => 'Training/submit_practice',
    'training/validate-practice' => 'Training/validate_practice',

    'training/certifications' => 'Training/certifications',
    'training/certificate/(:num)' => 'Training/certificate/$1',

    'settings' => 'Settings/index',

    'api/activities' => 'Api/get_activities',
    'api/sub-activities' => 'Api/get_sub_activities',
    'api/notifications' => 'Api/get_notifications',
    'api/mark-notification-read' => 'Api/mark_read',
];

