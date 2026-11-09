<?php
/**
 * Complete Database Seeder for Mail Query Tracker (MQT)
 * Generates rich, realistic enterprise demo data across all 40 tables with valid references.
 */

require_once __DIR__ . '/../app/config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "=== Starting Full Database Seeding ===\n";

    // Disable Foreign Key checks temporarily for clean truncate/re-seed
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    $tablesToTruncate = [
        'audit_logs', 'notifications', 'hold_history', 'ticket_attachments',
        'ticket_comments', 'task_tickets', 'ticket_lifecycle_history',
        'ticket_lifecycle_tracker', 'delivery_tracker', 'input_tracker',
        'recurring_instances', 'recurring_templates', 'error_tracker',
        'daily_production_logs', 'monthly_scorecards', 'team_kpi_configs', 'timesheet_logs',
        'process_updates', 'practice_files', 'pkt_test_attempts',
        'pkt_tests', 'pkt_question_bank',
        'activity_certifications', 'user_training_progress', 'training_plan_trainees',
        'training_plans', 'user_sub_activities', 'user_projects',
        'sub_activities', 'activities', 'divisions', 'ticket_categories',
        'workflow_stages', 'workflows', 'process_queues', 'projects',
        'contacts', 'tickets', 'users', 'roles'
    ];

    foreach ($tablesToTruncate as $tbl) {
        $pdo->exec("TRUNCATE TABLE `$tbl`;");
    }
    echo "[✓] Tables truncated successfully.\n";

    // 1. Roles
    $roles = [
        [1, 'Super Admin', 'Full system access, global configuration, admin management'],
        [2, 'Admin (TL)', 'Team Lead, team queue & ticket management'],
        [3, 'Manager', 'Operations Manager, department oversight & analytics'],
        [4, 'Employee', 'Individual worker, assigned ticket execution'],
        [5, 'Senior Management', 'Executive oversight, cross-project analytics & SLA reporting']
    ];
    $stmt = $pdo->prepare("INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES (?, ?, ?)");
    foreach ($roles as $r) {
        $stmt->execute($r);
    }
    echo "[✓] Roles seeded (5 rows).\n";

    // 2. Users
    // Default password for all seeded users: ChangeMe@123
    $defaultPassHash = password_hash('ChangeMe@123', PASSWORD_BCRYPT);
    $users = [
        [1, 'EMP001', 'Super Admin', 'superadmin@company.com', '9999999991', $defaultPassHash, 1, 'Executive Management', null, 'Active'],
        [2, 'EMP002', 'Rajesh Sharma', 'admin@company.com', '9888888882', $defaultPassHash, 3, 'Operations Management', 1, 'Active'],
        [3, 'EMP003', 'Priya Sundaram', 'lead@company.com', '9888888883', $defaultPassHash, 2, 'Banking Operations', 2, 'Active'],
        [4, 'EMP004', 'Anand Verma', 'checker@company.com', '9888888884', $defaultPassHash, 4, 'Quality & Compliance', 3, 'Active'],
        [5, 'EMP005', 'Varun Kumar', 'maker1@company.com', '9888888885', $defaultPassHash, 4, 'Banking Operations', 3, 'Active'],
        [6, 'EMP006', 'Sneha Patel', 'maker2@company.com', '9888888886', $defaultPassHash, 4, 'Banking Operations', 3, 'Active'],
        [7, 'EMP007', 'Karthik Raja', 'user1@company.com', '9888888887', $defaultPassHash, 4, 'AP & Invoicing', 2, 'Active'],
        [8, 'EMP008', 'Divya Nair', 'user2@company.com', '9888888888', $defaultPassHash, 4, 'AP & Invoicing', 2, 'Active'],
        [9, 'EMP009', 'Vikram Malhotra', 'mgmt@company.com', '9888888889', $defaultPassHash, 5, 'Executive Management', 1, 'Active']
    ];
    $stmt = $pdo->prepare("INSERT INTO `users` (`id`, `user_code`, `full_name`, `email`, `mobile`, `password`, `role_id`, `department`, `manager_id`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmt->execute($u);
    }
    echo "[✓] Users seeded (9 active users, password: ChangeMe@123).\n";

    // 3. Projects
    $projects = [
        [1, 'IDFC Banking Operations', 'IDFC-OPS', 'IDFC First Bank mail queries, NACH rejections, reconciliation & ticket workflows', 'Active', 'ticket_workflow', 1],
        [2, 'HDFC Card Services', 'HDFC-CS', 'HDFC Bank credit card disputes, chargeback tracking, customer escalation queues', 'Active', 'ticket_workflow', 1],
        [3, 'AP & Invoice Processing', 'AP-INV', 'Daily invoice logging, 3-way matching, batch count tracking and throughput scorecards', 'Active', 'production_count', 1],
        [4, 'Payroll & HR Shared Services', 'HR-PAY', 'Employee claims, timesheet validations, payroll processing output registers', 'Active', 'production_count', 1],
        [5, 'Global Vendor Reconciliation', 'GVR-HYB', 'End-to-end vendor statements, query ticketing, batch volumes and maker-checker validation', 'Active', 'hybrid', 1]
    ];
    $stmt = $pdo->prepare("INSERT INTO `projects` (`id`, `project_name`, `code`, `description`, `status`, `operation_type`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($projects as $p) {
        $stmt->execute($p);
    }
    echo "[✓] Projects seeded (5 projects covering ticket_workflow, production_count, hybrid).\n";

    // 4. User Projects Mapping
    $userProjects = [
        // Super Admin & Admin to all
        [1, 1], [1, 2], [1, 3], [1, 4], [1, 5],
        [2, 1], [2, 2], [2, 3], [2, 4], [2, 5],
        [9, 1], [9, 2], [9, 3], [9, 4], [9, 5],
        // Lead, Checker, Varun, Sneha -> IDFC & HDFC & Hybrid
        [3, 1], [3, 2], [3, 5],
        [4, 1], [4, 2], [4, 5],
        [5, 1], [5, 2], [5, 5],
        [6, 1], [6, 2], [6, 5],
        // Karthik, Divya -> AP & HR & Hybrid
        [7, 3], [7, 4], [7, 5],
        [8, 3], [8, 4], [8, 5]
    ];
    $stmt = $pdo->prepare("INSERT INTO `user_projects` (`user_id`, `project_id`) VALUES (?, ?)");
    foreach ($userProjects as $up) {
        $stmt->execute($up);
    }
    echo "[✓] User Projects mapped.\n";

    // 5. Divisions
    $divisions = [
        [1, 1, 3, 'Retail Assets & Loans', 'RAL', 'Active'],
        [2, 1, 3, 'Corporate Banking & CMS', 'CBC', 'Active'],
        [3, 3, 3, 'Accounts Payable & Invoicing', 'APV', 'Active'],
        [4, 5, 3, 'Treasury & Settlements', 'TRS', 'Active'],
        [5, 2, 3, 'Credit Card Dispute & Chargeback', 'CCD', 'Active']
    ];
    $stmt = $pdo->prepare("INSERT INTO `divisions` (`id`, `project_id`, `team_leader_id`, `division_name`, `code`, `status`) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($divisions as $d) {
        $stmt->execute($d);
    }
    echo "[✓] Divisions seeded (5 divisions with assigned Team Leaders).\n";

    // 6. Activities
    $activities = [
        [1, 1, 'Agency Billing & Commission', 'Billing, agency payouts, and agency account reconciliation', 'Active'],
        [2, 1, 'Direct Debit & NACH Mandates', 'NACH mandate clearance, mandate rejections, and return handling', 'Active'],
        [3, 3, 'Corporate Vendor Invoicing', 'Vendor invoice receipt, 3-way matching, tax clearance & approvals', 'Active'],
        [4, 4, 'Settlements & Escrow Reconciliations', 'NEFT/RTGS escrow verification, daily clearing and Nostro adjustments', 'Active'],
        [5, 5, 'Chargeback & Dispute Investigation', 'Customer disputed transactions, chargeback representment and dispute resolution', 'Active']
    ];
    $stmt = $pdo->prepare("INSERT INTO `activities` (`id`, `division_id`, `activity_name`, `description`, `status`) VALUES (?, ?, ?, ?, ?)");
    foreach ($activities as $a) {
        $stmt->execute($a);
    }
    echo "[✓] Activities seeded (5 activities).\n";

    // 7. Workflows & Workflow Stages & Process Queues
    $processQueues = [
        [1, 'Input Queue', 'INP_Q', 'Both', '#0284c7', 'Incoming queries, emails and new data feeds awaiting initial pickup', 1, 'Active', 'fa-inbox'],
        [2, 'Maker Queue', 'MKR_Q', 'Maker', '#2563eb', 'Processing queue for maker execution, data entry, and file creation', 2, 'Active', 'fa-edit'],
        [3, 'Checker Queue', 'CHK_Q', 'Checker', '#7c3aed', 'Quality verification queue for checkers to approve or mark rework', 3, 'Active', 'fa-check-double'],
        [4, 'Error / Rework Queue', 'ERR_Q', 'Maker', '#dc2626', 'Rework loop for maker corrections based on checker error observations', 4, 'Active', 'fa-exclamation-triangle'],
        [5, 'Delivery Queue', 'DLV_Q', 'Both', '#059669', 'Dispatch and outbound communication queue for completed requests', 5, 'Active', 'fa-paper-plane'],
        [6, 'Acknowledgement Queue', 'ACK_Q', 'Both', '#d97706', 'Awaiting client / stakeholder acknowledgement receipt confirmation', 6, 'Active', 'fa-receipt'],
        [7, 'Approval Queue', 'APP_Q', 'Admin', '#9333ea', 'Managerial and executive sign-off before financial release', 7, 'Active', 'fa-stamp'],
        [8, 'Uploader Queue', 'UPL_Q', 'Maker', '#4f46e5', 'Host system upload, core banking integration & clearing entry', 8, 'Active', 'fa-upload'],
        [9, 'Dump Sharing Queue', 'DMP_Q', 'Both', '#0891b2', 'Automated data dump extraction and partner repository sharing', 9, 'Active', 'fa-database'],
        [10, 'Consol Vendor Tracker', 'CON_Q', 'Both', '#475569', 'Vendor consolidated tracker updates and SLA register sync', 10, 'Active', 'fa-table'],
        [11, 'Closed Queue', 'CLS_Q', 'Both', '#10b981', 'Final closed and archived lifecycle queries', 11, 'Active', 'fa-archive']
    ];
    $stmt = $pdo->prepare("INSERT INTO `process_queues` (`id`, `queue_name`, `queue_code`, `assigned_role`, `badge_color`, `description`, `display_order`, `status`, `icon`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($processQueues as $pq) {
        $stmt->execute($pq);
    }
    echo "[✓] Process Queues seeded (11 queues).\n";

    // Workflows
    $workflows = [
        [1, 'Standard 12-Stage Banking Workflow', 1, 1, 'Full enterprise maker-checker-approval lifecycle with audit history', 1, 'Active', 1],
        [2, 'Expedited Query Workflow', 2, 2, 'Fast-track 4-stage resolution for high priority queries', 0, 'Active', 1],
        [3, 'Vendor Invoice Clearance Workflow', 3, 3, '3-way match validation, checker signoff and ERP upload', 0, 'Active', 1],
        [4, 'Card Chargeback Investigation Flow', 4, 5, 'Dispute intake, evidence verification and representment', 0, 'Active', 1]
    ];
    $stmt = $pdo->prepare("INSERT INTO `workflows` (`id`, `workflow_name`, `category_id`, `sub_activity_id`, `description`, `is_default`, `status`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($workflows as $wf) {
        $stmt->execute($wf);
    }
    echo "[✓] Workflows seeded.\n";

    // Workflow Stages for Workflow 1 (Standard 12-Stage)
    $stages = [
        [1, 1, 1, 'Input Received', 'Input', 'Initial mail query receipt and logging', 'Both', 50, 100, '2'],
        [2, 1, 2, 'Maker Queue', 'Maker', 'Primary data extraction and query response preparation', 'Maker', 180, 100, '3'],
        [3, 1, 3, 'Checker Queue', 'Checker', 'Quality audit and four-eyes review', 'Checker', 310, 100, '4,5'],
        [4, 1, 4, 'Error Log & Rework', 'ErrorLog', 'Discrepancy logged and returned to maker', 'Maker', 310, 220, '2'],
        [5, 1, 5, 'Delivery Outbound', 'Delivery', 'Dispatched response to agency / customer', 'Both', 440, 100, '6'],
        [6, 1, 6, 'Acknowledgement', 'Ack', 'Verification of delivery acknowledgement receipt', 'Both', 570, 100, '7'],
        [7, 1, 7, 'Approval Queue', 'Approval', 'Supervisor / Team Lead authorization', 'Admin', 700, 100, '8'],
        [8, 1, 8, 'Uploader Queue', 'Uploader', 'Core banking system upload & batch clearing', 'Maker', 830, 100, '9'],
        [9, 1, 9, 'Dump Sharing', 'DumpShare', 'Consolidated audit dump shared to client portal', 'Both', 960, 100, '10'],
        [10, 1, 10, 'Consol Vendor Tracker', 'ConsolVendor', 'Vendor reconciliation register updated', 'Both', 1090, 100, '11'],
        [11, 1, 11, 'Closed & Archived', 'Closed', 'Ticket closed within SLA threshold', 'Both', 1220, 100, '']
    ];
    $stmt = $pdo->prepare("INSERT INTO `workflow_stages` (`id`, `workflow_id`, `stage_order`, `stage_name`, `queue_type`, `description`, `assigned_role`, `pos_x`, `pos_y`, `connected_to`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($stages as $st) {
        $stmt->execute($st);
    }
    echo "[✓] Workflow Stages seeded.\n";

    // 8. Categories
    $categories = [
        [1, 'High Priority Financial Impact', 'high-priority-financial', 'Active', 1],
        [2, 'Routine Operational Query', 'routine-operational', 'Active', 2],
        [3, 'Regulatory & Compliance Escalation', 'regulatory-compliance', 'Active', 1],
        [4, 'System Integration & File Discrepancy', 'system-discrepancy', 'Active', 3],
        [5, 'Chargeback & Dispute Inquiry', 'chargeback-inquiry', 'Active', 4]
    ];
    $stmt = $pdo->prepare("INSERT INTO `ticket_categories` (`id`, `category_name`, `category_slug`, `status`, `workflow_id`) VALUES (?, ?, ?, ?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "[✓] Categories seeded (5 categories).\n";

    // 9. Sub Activities
    $subActivities = [
        [1, 1, 1, 'Agency Commission Query', 24, 1.50, 5, 4, 1, 5, 'Active', 'INP_Q,MKR_Q,CHK_Q,ERR_Q,DLV_Q,ACK_Q,APP_Q,UPL_Q,DMP_Q,CON_Q,CLS_Q'],
        [2, 1, 1, 'Agency Billing Reconciliation', 48, 2.00, 5, 4, 1, 10, 'Active', 'INP_Q,MKR_Q,CHK_Q,ERR_Q,DLV_Q,ACK_Q,CLS_Q'],
        [3, 2, 1, 'NACH Mandate Rejection Handling', 12, 1.00, 6, 4, 2, 2, 'Active', 'INP_Q,MKR_Q,CHK_Q,DLV_Q,CLS_Q'],
        [4, 2, 1, 'NACH Return Memo Verification', 24, 1.25, 6, 3, 2, 4, 'Active', 'INP_Q,MKR_Q,CHK_Q,DLV_Q,CLS_Q'],
        [5, 3, 3, 'Vendor Tax Invoice Approval', 48, 2.00, 7, 3, 3, 15, 'Active', 'INP_Q,MKR_Q,CHK_Q,APP_Q,UPL_Q,CLS_Q'],
        [6, 3, 3, 'PO to Invoice 3-Way Match', 36, 1.75, 8, 3, 3, 18, 'Active', 'INP_Q,MKR_Q,CHK_Q,APP_Q,CLS_Q'],
        [7, 4, 4, 'NEFT / RTGS Escrow Query', 8, 2.50, 5, 4, 1, 1, 'Active', 'INP_Q,MKR_Q,CHK_Q,APP_Q,CLS_Q'],
        [8, 4, 4, 'Nostro Account Adjustment', 24, 3.00, 6, 4, 1, 20, 'Active', 'INP_Q,MKR_Q,CHK_Q,APP_Q,UPL_Q,CLS_Q'],
        [9, 5, 5, 'Credit Card Fraud Chargeback', 24, 1.80, 6, 4, 4, 7, 'Active', 'INP_Q,MKR_Q,CHK_Q,DLV_Q,CLS_Q'],
        [10, 5, 5, 'Customer Dispute Representment', 48, 2.20, 5, 3, 4, 12, 'Active', 'INP_Q,MKR_Q,CHK_Q,DLV_Q,CLS_Q']
    ];
    $stmt = $pdo->prepare("INSERT INTO `sub_activities` (`id`, `activity_id`, `division_id`, `sub_activity_name`, `default_tat_hours`, `point_weight`, `default_user_id`, `default_checker_id`, `workflow_id`, `default_occurrence_day`, `status`, `enabled_queues`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($subActivities as $sa) {
        $stmt->execute($sa);
    }
    echo "[✓] Sub Activities seeded (10 sub-activities).\n";

    // 10. User Sub Activities (Maker / Checker Mapping)
    $userSubActivities = [
        [5, 1, 'Maker'], [5, 2, 'Maker'], [5, 7, 'Maker'], [5, 10, 'Maker'],
        [6, 3, 'Maker'], [6, 4, 'Maker'], [6, 8, 'Maker'], [6, 9, 'Maker'],
        [7, 5, 'Maker'], [7, 6, 'Maker'],
        [8, 5, 'Maker'], [8, 6, 'Maker'],
        [4, 1, 'Checker'], [4, 2, 'Checker'], [4, 3, 'Checker'], [4, 7, 'Checker'], [4, 8, 'Checker'], [4, 9, 'Checker'],
        [3, 4, 'Checker'], [3, 5, 'Checker'], [3, 6, 'Checker'], [3, 10, 'Checker']
    ];
    $stmt = $pdo->prepare("INSERT INTO `user_sub_activities` (`user_id`, `sub_activity_id`, `role_type`) VALUES (?, ?, ?)");
    foreach ($userSubActivities as $usa) {
        $stmt->execute($usa);
    }
    echo "[✓] User Sub Activities assigned.\n";

    // 11. Tickets
    // Create 18 realistic tickets spanning all statuses, priorities, TAT conditions
    $tickets = [
        // 1: New - Unassigned
        [1, 'MQT-2026-1001', 'Query Ticket', '2026-10-09 09:30:00', '2026-10-09', 'ops.lead@idfcbank.com', 'Discrepancy in Agency Commission Invoice - Branch 402', 1, 1, 1, 1, 1, 1, 'New', 'Input Received', 'High', '2026-10-10 09:30:00', 'Due Today', null, 4, null, null, 'Requires priority agency billing investigation', null, 'AGC-4029', 'Rajesh Sharma', null, 2, '2026-10'],
        // 2: Assigned - Varun (Maker 1)
        [2, 'MQT-2026-1002', 'Query Ticket', '2026-10-09 11:15:00', '2026-10-09', 'settlements@hdfcbank.com', 'NACH Return Code E03 - Mandate Data Correction Request', 1, 2, 3, 2, 2, 2, 'Assigned', 'Maker Queue', 'Medium', '2026-10-10 11:15:00', 'Within TAT', 5, 4, null, null, 'Assigned to Varun for clearing sheet validation', null, 'AGC-1102', 'Priya Sundaram', null, 3, '2026-10'],
        // 3: In Progress - Varun
        [3, 'MQT-2026-1003', 'Query Ticket', '2026-10-08 14:00:00', '2026-10-08', 'vendor.billing@tcs.com', 'Vendor Tax Invoice Q3 Approval - PO#88921', 3, 3, 5, 3, 3, 1, 'In Progress', 'Maker Queue', 'High', '2026-10-10 14:00:00', 'Within TAT', 5, 3, null, null, '3-way matching verified, pending GST tax clearance attachment', null, 'VND-TCS-09', 'Rajesh Sharma', null, 2, '2026-10'],
        // 4: Pending - Waiting for Customer
        [4, 'MQT-2026-1004', 'Query Ticket', '2026-10-07 10:00:00', '2026-10-07', 'client.desk@kotak.com', 'Clarification required for Escrow Account 40992 Balance Diff', 4, 4, 7, 1, 7, 3, 'Waiting for Customer', 'Maker Queue', 'Critical', '2026-10-08 10:00:00', 'Overdue', 5, 4, null, null, 'Sent query back to client asking for stamped statement', null, 'KOTAK-992', 'Rajesh Sharma', 'Awaiting original bank stamped transaction statement from Kotak branch', 2, '2026-10'],
        // 5: On Hold - Held for System Reconciliation
        [5, 'MQT-2026-1005', 'Query Ticket', '2026-10-06 16:20:00', '2026-10-06', 'fin.audit@rbi.org.in', 'RBI NACH Mandate Compliance Audit Query - Batch #9920', 1, 2, 4, 1, 4, 3, 'On Hold', 'Checker Queue', 'Critical', '2026-10-07 16:20:00', 'Overdue', 6, 4, null, null, 'Ticket held pending core banking batch log recovery', null, 'RBI-AUD-20', 'Rajesh Sharma', 'System audit batch under maintenance by IT support team', 1, '2026-10'],
        // 6: Released - Back to In Progress
        [6, 'MQT-2026-1006', 'Query Ticket', '2026-10-08 11:00:00', '2026-10-08', 'card.disputes@visa.com', 'Fraud Chargeback Case Ref #CB-889021 Merchant Reversal', 5, 5, 9, 4, 5, 5, 'Released', 'Maker Queue', 'High', '2026-10-10 11:00:00', 'Due Today', 6, 4, null, null, 'Released from hold after receiving merchant POS slip', null, 'VISA-8890', 'Priya Sundaram', null, 3, '2026-10'],
        // 7: Completed - Checker Verified
        [7, 'MQT-2026-1007', 'Query Ticket', '2026-10-08 09:00:00', '2026-10-08', 'corp.finance@infosys.com', 'Quarterly Commission Settlement Certificate Dispatch', 1, 1, 2, 1, 10, 2, 'Completed', 'Delivery Outbound', 'Medium', '2026-10-10 09:00:00', 'Within TAT', 5, 4, 5, '2026-10-09 17:30:00', 'All numbers verified against ledger. Certificate issued.', 'uploads/certificates/INFY_Q3.pdf', 'AGC-INF-01', 'Rajesh Sharma', null, 2, '2026-10'],
        // 8: Closed
        [8, 'MQT-2026-1008', 'Query Ticket', '2026-10-05 10:00:00', '2026-10-05', 'billing@idfcfirst.com', 'September 2026 Vendor Billing Master Sheet Sign-off', 1, 1, 1, 1, 11, 2, 'Closed', 'Closed & Archived', 'Low', '2026-10-06 10:00:00', 'Within TAT', 5, 4, 5, '2026-10-06 09:15:00', 'Successfully processed, acknowledged and archived.', 'uploads/closure/IDFC_SEP_SIGN.pdf', 'IDFC-7718', 'Rajesh Sharma', null, 2, '2026-10'],
        // 9: Task Ticket - Sneha
        [9, 'MQT-2026-1009', 'Task Ticket', '2026-10-09 10:00:00', '2026-10-09', 'internal.audit@company.com', 'Execute Daily Nostro Account Adjustment Reconciliation', 4, 4, 8, 1, 8, 1, 'In Progress', 'Maker Queue', 'High', '2026-10-10 18:00:00', 'Within TAT', 6, 4, null, null, 'Internal task for treasury balancing', null, 'TRS-092', 'Priya Sundaram', null, 3, '2026-10'],
        // 10: Task Ticket - Karthik
        [10, 'MQT-2026-1010', 'Task Ticket', '2026-10-09 08:30:00', '2026-10-09', 'operations@company.com', 'Monthly GST Input Tax Credit Ledger 3-Way Match', 3, 3, 6, 3, 6, 4, 'Assigned', 'Maker Queue', 'Medium', '2026-10-11 18:00:00', 'Within TAT', 7, 3, null, null, 'Reconciliation of GSTR-2B with ERP purchase invoices', null, 'AP-GST-20', 'Rajesh Sharma', null, 2, '2026-10'],
        // 11: Varun My Bucket Query (Overdue Demo)
        [11, 'MQT-2026-1011', 'Query Ticket', '2026-10-06 09:00:00', '2026-10-06', 'branch.manager@idfc.com', 'Urgent Escalation: Uncredited Commission for Agency AG-991', 1, 1, 1, 1, 2, 1, 'In Progress', 'Maker Queue', 'Critical', '2026-10-07 09:00:00', 'Overdue', 5, 4, null, null, 'Maker is reviewing bank settlement logs', null, 'AG-991', 'Rajesh Sharma', null, 2, '2026-10'],
        // 12: Sneha My Bucket Query (Due Today Demo)
        [12, 'MQT-2026-1012', 'Query Ticket', '2026-10-09 15:30:00', '2026-10-09', 'disputes@mastercard.com', 'Chargeback Pre-Arbitration Notice #MC-99201', 5, 5, 10, 4, 3, 5, 'Assigned', 'Checker Queue', 'High', '2026-10-10 17:00:00', 'Due Today', 6, 3, null, null, 'Pending Checker approval for representment pack', null, 'MC-99201', 'Priya Sundaram', null, 3, '2026-10'],
        // 13: Divya AP Query
        [13, 'MQT-2026-1013', 'Query Ticket', '2026-10-09 14:00:00', '2026-10-09', 'vendor.support@wipro.com', 'Invoice #WP-2026-881 Payment Status Inquiry', 3, 3, 5, 3, 2, 2, 'Assigned', 'Maker Queue', 'Medium', '2026-10-11 14:00:00', 'Within TAT', 8, 3, null, null, 'Under verification with accounts payable clearance team', null, 'VND-WIP-08', 'Rajesh Sharma', null, 2, '2026-10'],
        // 14: Waiting for Internal Team
        [14, 'MQT-2026-1014', 'Query Ticket', '2026-10-08 16:00:00', '2026-10-08', 'clearing@axisbank.com', 'NACH Return Memo Clearing Discrepancy File #AX-441', 1, 2, 4, 2, 4, 4, 'Waiting for Internal Team', 'Maker Queue', 'Medium', '2026-10-10 16:00:00', 'Due Today', 5, 4, null, null, 'Awaiting IT confirmation regarding missing XML return tags', null, 'AXIS-441', 'Priya Sundaram', 'Pending IT database dump query execution for batch 441', 3, '2026-10'],
        // 15: Closed Ticket
        [15, 'MQT-2026-1015', 'Query Ticket', '2026-10-04 11:00:00', '2026-10-04', 'finance@reliance.com', 'Corporate Escrow Settlement Confirmation Letter', 4, 4, 7, 1, 11, 1, 'Closed', 'Closed & Archived', 'Medium', '2026-10-05 11:00:00', 'Within TAT', 6, 4, 6, '2026-10-05 09:40:00', 'Dispatched signed confirmation to corporate desk', 'uploads/escrow/REL_CONF_OCT.pdf', 'REL-ESC-01', 'Rajesh Sharma', null, 2, '2026-10']
    ];

    $stmt = $pdo->prepare("INSERT INTO `tickets` (
        `id`, `ticket_number`, `ticket_type`, `received_datetime`, `scheduled_date`,
        `from_address`, `subject`, `division_id`, `activity_id`, `sub_activity_id`,
        `workflow_id`, `current_stage_id`, `category_id`, `status`, `stage`,
        `priority`, `tat_datetime`, `tat_status`, `allocated_to`, `checker_id`,
        `replied_by`, `replied_datetime`, `remarks`, `closure_attachment`,
        `agency_code`, `manager_name`, `pending_reason`, `created_by`, `billing_month`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($tickets as $t) {
        $stmt->execute($t);
    }
    echo "[✓] Tickets seeded (15 comprehensive tickets across all states).\n";

    // 12. Task Tickets
    $taskTickets = [
        [1, 9, 'Execute Daily Nostro Account Adjustment Reconciliation', 'Download daily Nostro statements, verify FX rates and record balance variances', 'High', '2026-10-10 18:00:00', 'In progress with treasury desk', null, null, 'In Progress'],
        [2, 10, 'Monthly GST Input Tax Credit Ledger 3-Way Match', 'Perform automated reconciliation between GSTR-2B JSON and ERP AP ledger', 'Medium', '2026-10-11 18:00:00', null, null, null, 'Pending']
    ];
    $stmt = $pdo->prepare("INSERT INTO `task_tickets` (`id`, `ticket_id`, `task_title`, `description`, `priority`, `due_date`, `completion_remarks`, `completion_date`, `attachment_path`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($taskTickets as $tt) {
        $stmt->execute($tt);
    }
    echo "[✓] Task Tickets seeded.\n";

    // 13. Ticket Comments
    $ticketComments = [
        [1, 1, 2, 'Ticket created from operations intake inbox. Routing to Retail Assets team.'],
        [2, 2, 3, 'Assigned to Varun for clearing sheet validation and return reason check.'],
        [3, 2, 5, 'Reviewed mandate XML. Reason code E03 indicates signature mismatch on paper mandate.'],
        [4, 3, 5, 'Verified 3-way match against SAP PO #88921. Quantity and unit pricing match.'],
        [5, 4, 5, 'Discrepancy of INR 4,250 found between bank statement and sub-ledger. Waiting for client branch clarification.'],
        [6, 5, 2, 'Placing ticket on hold as the core banking system batch logs are undergoing scheduled maintenance.'],
        [7, 6, 6, 'Received customer signed declaration form. Releasing ticket and preparing representment bundle.'],
        [8, 7, 5, 'Uploaded signed settlement certificate. Forwarded to checker for four-eyes approval.'],
        [9, 7, 4, 'Audit verification completed. All ledger totals match. Approved for outbound dispatch.'],
        [10, 11, 5, 'Checked transaction ID #TXN-99812. Tracing inter-bank NEFT clearing acknowledgment.']
    ];
    $stmt = $pdo->prepare("INSERT INTO `ticket_comments` (`id`, `ticket_id`, `user_id`, `comment`) VALUES (?, ?, ?, ?)");
    foreach ($ticketComments as $tc) {
        $stmt->execute($tc);
    }
    echo "[✓] Ticket Comments seeded.\n";

    // 14. Ticket Attachments
    $attachments = [
        [1, 1, 'Commission_Invoice_Branch_402.pdf', 'uploads/sample_commission_invoice.pdf', 245890, 2],
        [2, 2, 'NACH_Return_Memo_E03.xlsx', 'uploads/sample_nach_return.xlsx', 89210, 3],
        [3, 3, 'TCS_Vendor_Invoice_Q3.pdf', 'uploads/sample_vendor_invoice.pdf', 512000, 5],
        [4, 5, 'RBI_Circular_NACH_Mandates.pdf', 'uploads/rbi_guidelines.pdf', 1024000, 1],
        [5, 7, 'INFY_Commission_Certificate_Signed.pdf', 'uploads/infy_cert.pdf', 345000, 5]
    ];
    $stmt = $pdo->prepare("INSERT INTO `ticket_attachments` (`id`, `ticket_id`, `file_name`, `file_path`, `file_size`, `uploaded_by`) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($attachments as $att) {
        $stmt->execute($att);
    }
    echo "[✓] Ticket Attachments seeded.\n";

    // 15. Hold History
    $holdHistory = [
        [1, 5, 'System Maintenance: Core banking batch ledger query unavailable', '2026-10-06 16:20:00', '2026-10-11 18:00:00', 2, null, null, 'Held under IT ticket #IT-99201 for database maintenance'],
        [2, 6, 'Awaiting Client Document: Signed dispute declaration form', '2026-10-06 10:00:00', '2026-10-08 11:00:00', 3, '2026-10-08 11:00:00', 3, 'Merchant POS transaction copy received from Visa gateway']
    ];
    $stmt = $pdo->prepare("INSERT INTO `hold_history` (`id`, `ticket_id`, `hold_reason`, `hold_date`, `expected_release_date`, `held_by`, `release_date`, `released_by`, `remarks`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($holdHistory as $hh) {
        $stmt->execute($hh);
    }
    echo "[✓] Hold History seeded.\n";

    // 16. Ticket Lifecycle Tracker & History
    $lifecycleTracker = [
        [
            1, 1, '2026-10', 'MQT-2026-1001', 'Query Ticket', 1, 1, 1, 'Retail Assets & Loans', 'Agency Billing & Commission', 'Agency Commission Query',
            5, 4, 'Input Received', '2026-10-09 09:30:00', 'uploads/tickets/inbox_402.eml', null, null, null, null, null, null, null, null, 0, null,
            null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 'Initial intake received'
        ],
        [
            2, 2, '2026-10', 'MQT-2026-1002', 'Query Ticket', 1, 2, 3, 'Retail Assets & Loans', 'Direct Debit & NACH Mandates', 'NACH Mandate Rejection Handling',
            5, 4, 'Maker Queue', '2026-10-09 11:15:00', 'uploads/tickets/nach_1102.eml', '2026-10-09 11:30:00', null, null, null, null, null, null, null, 0, null,
            null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 'Maker actively analyzing error codes'
        ],
        [
            3, 3, '2026-10', 'MQT-2026-1003', 'Query Ticket', 3, 3, 5, 'Accounts Payable & Invoicing', 'Corporate Vendor Invoicing', 'Vendor Tax Invoice Approval',
            5, 3, 'Checker Queue', '2026-10-08 14:00:00', 'uploads/tickets/inv_tcs.pdf', '2026-10-08 14:30:00', '2026-10-09 10:00:00', 'uploads/maker/3way_match.xlsx',
            '2026-10-09 10:15:00', null, null, null, null, 0, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 'Submitted to Checker for review'
        ],
        [
            4, 7, '2026-10', 'MQT-2026-1007', 'Query Ticket', 1, 1, 2, 'Retail Assets & Loans', 'Agency Billing & Commission', 'Agency Billing Reconciliation',
            5, 4, 'Delivery', '2026-10-08 09:00:00', 'uploads/tickets/infy_req.eml', '2026-10-08 09:30:00', '2026-10-08 17:00:00', 'uploads/maker/infy_recon.xlsx',
            '2026-10-09 09:00:00', '2026-10-09 15:00:00', 'uploads/checker/infy_approved.xlsx', null, null, 0, null, '2026-10-09 17:30:00', 'uploads/delivery/INFY_Q3_dispatch.pdf',
            null, null, null, null, null, null, null, null, null, null, null, null, null, null, 'Dispatched to Infosys Treasury Desk'
        ],
        [
            5, 8, '2026-10', 'MQT-2026-1008', 'Query Ticket', 1, 1, 1, 'Retail Assets & Loans', 'Agency Billing & Commission', 'Agency Commission Query',
            5, 4, 'Closed', '2026-10-05 10:00:00', 'uploads/tickets/idfc_sep.eml', '2026-10-05 10:15:00', '2026-10-05 16:00:00', 'uploads/maker/idfc_sep.xlsx',
            '2026-10-05 16:30:00', '2026-10-06 08:30:00', 'uploads/checker/idfc_signed.xlsx', null, null, 0, null, '2026-10-06 09:00:00', 'uploads/delivery/IDFC_dispatch.pdf',
            '2026-10-06 09:10:00', 'uploads/ack/IDFC_ack.msg', '2026-10-06 09:12:00', '2026-10-06 09:13:00', 'uploads/appr/IDFC_appr.pdf', '2026-10-06 09:14:00', '2026-10-06 09:14:30', 'uploads/upl/IDFC_core.csv',
            '2026-10-06 09:14:45', 'uploads/dump/IDFC_dump.zip', '2026-10-06 09:14:55', 'Consolidated Vendor Register updated', '2026-10-06 09:15:00', 2, 'Closed within 24h SLA'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO `ticket_lifecycle_tracker` (
        `id`, `ticket_id`, `billing_month`, `ticket_number`, `ticket_type`,
        `division_id`, `activity_id`, `sub_activity_id`, `division_name`, `activity_name`, `sub_activity_name`,
        `maker_id`, `checker_id`, `current_stage`,
        `input_received_at`, `input_received_attachment`,
        `maker_queue_at`, `maker_completed_at`, `maker_attachment`,
        `checker_queue_at`, `checker_completed_at`, `checker_attachment`,
        `error_log_at`, `error_description`, `rework_loop_count`, `rework_last_at`,
        `delivery_at`, `delivery_attachment`,
        `acknowledgement_at`, `acknowledgement_attachment`,
        `approval_queue_at`, `approved_at`, `approval_attachment`,
        `uploader_queue_at`, `uploaded_at`, `uploader_attachment`,
        `dump_sharing_at`, `dump_attachment`,
        `consol_vendor_at`, `consol_vendor_remarks`,
        `closed_at`, `closed_by`, `remarks`
    ) VALUES (" . implode(',', array_fill(0, 43, '?')) . ")");

    foreach ($lifecycleTracker as $lt) {
        $stmt->execute($lt);
    }
    echo "[✓] Ticket Lifecycle Tracker seeded.\n";

    // Lifecycle History
    $lifecycleHistory = [
        [1, 5, 8, 'Input Received', 2, '2026-10-05 10:00:00', 'uploads/tickets/idfc_sep.eml', 'idfc_sep.eml', 'Ticket logged from inbound mailbox'],
        [2, 5, 8, 'Maker Queue', 5, '2026-10-05 16:00:00', 'uploads/maker/idfc_sep.xlsx', 'idfc_sep.xlsx', 'Maker completed invoice calculations'],
        [3, 5, 8, 'Checker Queue', 4, '2026-10-06 08:30:00', 'uploads/checker/idfc_signed.xlsx', 'idfc_signed.xlsx', 'Checker verified and approved with 100% score'],
        [4, 5, 8, 'Delivery', 5, '2026-10-06 09:00:00', 'uploads/delivery/IDFC_dispatch.pdf', 'IDFC_dispatch.pdf', 'Dispatched to IDFC Finance Desk'],
        [5, 5, 8, 'Acknowledgement', 2, '2026-10-06 09:10:00', 'uploads/ack/IDFC_ack.msg', 'IDFC_ack.msg', 'Client delivery confirmation received'],
        [6, 5, 8, 'Approval Queue', 2, '2026-10-06 09:13:00', 'uploads/appr/IDFC_appr.pdf', 'IDFC_appr.pdf', 'Manager signoff completed'],
        [7, 5, 8, 'Closed', 2, '2026-10-06 09:15:00', null, null, 'Ticket closed successfully']
    ];
    $stmt = $pdo->prepare("INSERT INTO `ticket_lifecycle_history` (`id`, `lifecycle_id`, `ticket_id`, `stage_name`, `performed_by`, `performed_at`, `attachment_path`, `attachment_name`, `remarks`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($lifecycleHistory as $lh) {
        $stmt->execute($lh);
    }
    echo "[✓] Ticket Lifecycle History seeded.\n";

    // 17. Input Tracker & Delivery Tracker
    $inputRecords = [
        [1, 'Email Feeds', '2026-10-09 08:30:00', 'ops@idfcfirst.com', 'DOC-IDFC-99120', 5, 'Assigned', 'Inward billing file received for clearing', 2],
        [2, 'SFTP Portal', '2026-10-09 09:00:00', 'gateway@hdfcbank.com', 'DOC-HDFC-NACH-88', 6, 'In Progress', 'Daily NACH rejection summary batch', 3],
        [3, 'Vendor Portal', '2026-10-09 10:15:00', 'finance@infosys.com', 'DOC-INFY-OCT-01', 7, 'Completed', 'Infosys October vendor claims batch', 2],
        [4, 'Courier / Hardcopy', '2026-10-08 14:00:00', 'branch.south@kotak.com', 'DOC-KTK-HC-44', 8, 'Received', 'Original stamped mandate dispute physical letters', 2]
    ];
    $stmt = $pdo->prepare("INSERT INTO `input_tracker` (`id`, `source`, `received_date`, `received_from`, `document_reference`, `assigned_to`, `status`, `remarks`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($inputRecords as $ir) {
        $stmt->execute($ir);
    }
    echo "[✓] Input Tracker seeded.\n";

    $deliveryRecords = [
        [1, 'DEL-2026-001', 7, 'finance@infosys.com', '2026-10-09 17:30:00', 'Email', 'Pending', 'Commission Settlement Certificate dispatched', 'uploads/certificates/INFY_Q3.pdf', 5],
        [2, 'DEL-2026-002', 8, 'billing@idfcfirst.com', '2026-10-06 09:00:00', 'Portal Upload', 'Yes', 'September Vendor Billing Master Sheet uploaded to client SFTP', 'uploads/closure/IDFC_SEP_SIGN.pdf', 5],
        [3, 'DEL-2026-003', 15, 'finance@reliance.com', '2026-10-05 09:40:00', 'Email', 'Yes', 'Signed Corporate Escrow Confirmation Letter', 'uploads/escrow/REL_CONF_OCT.pdf', 6]
    ];
    $stmt = $pdo->prepare("INSERT INTO `delivery_tracker` (`id`, `delivery_number`, `ticket_id`, `delivered_to`, `delivery_date`, `delivery_mode`, `ack_received`, `remarks`, `attachment_path`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($deliveryRecords as $dr) {
        $stmt->execute($dr);
    }
    echo "[✓] Delivery Tracker seeded.\n";

    // 18. Error Tracker (Maker-Checker Audit Discrepancies)
    $errorTracker = [
        [1, '2026-10-01', '2026-10-08', 'Tax Calculation Formula Mismatch', 'Maker omitted 18% GST component on agency line item #4.', 'Recalculated line item with standard GST formula and updated ledger.', 'Internal', 5, 4, 4],
        [2, '2026-10-01', '2026-10-07', 'Incorrect NACH Return Reason Code', 'Maker logged code R01 (Insufficient Funds) instead of E03 (Signature Mismatch).', 'Corrected code in clearing sheet and notified partner bank.', 'Internal', 6, 4, 4],
        [3, '2026-09-01', '2026-09-28', 'Missing Vendor GSTIN in Tax Invoice', 'Vendor provided tax invoice with legacy PAN instead of active GSTIN.', 'Contacted vendor SPOC and obtained revised tax invoice with valid GSTIN.', 'External', 7, 3, 3],
        [4, '2026-09-01', '2026-09-25', 'Nostro Ledger Posting Date Discrepancy', 'Transaction posted on T+2 instead of value date T+0.', 'Passed reversing journal entry with correct value date.', 'Internal', 6, 4, 2]
    ];
    $stmt = $pdo->prepare("INSERT INTO `error_tracker` (`id`, `billing_month`, `checking_month`, `error_observation`, `error_description`, `resolution_solution`, `error_type`, `maker_id`, `checker_id`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($errorTracker as $et) {
        $stmt->execute($et);
    }
    echo "[✓] Error Tracker seeded.\n";

    // 19. Process Updates & SOPs
    $processUpdates = [
        [1, 'Updated Standard Operating Procedure for NACH Return Codes (Rev 3.2)', 1, 2, 3, '2026-10', 'Effective 01-Oct-2026: NPCI has introduced new sub-codes for e-Mandate rejections. All makers must verify the sub-code before logging dispute tickets.', 'NACH_SOP_v3.2.pdf', 'uploads/sop/NACH_SOP_v3.2.pdf', 'pdf', 845000, 2, '2026-10-01 10:00:00'],
        [2, 'Revised GST Invoice Verification Thresholds & 3-Way Matching Rules', 3, 3, 5, '2026-10', 'All invoices above INR 5,00,000 now require mandatory dual-checker verification and automated GSTR-2B API validation.', 'GST_Verification_Policy_Oct2026.pdf', 'uploads/sop/GST_Verification_Policy.pdf', 'pdf', 1240000, 2, '2026-10-02 11:30:00'],
        [3, 'IDFC First Bank Agency Payout Escalation Matrix & TAT Guidelines', 1, 1, 1, '2026-09', 'Updated escalation SPOC contact numbers and holiday calendar for Q3 agency commission settlements.', 'IDFC_Payout_Escalation_Matrix.xlsx', 'uploads/sop/IDFC_Escalation.xlsx', 'xlsx', 420000, 3, '2026-09-28 14:00:00']
    ];
    $stmt = $pdo->prepare("INSERT INTO `process_updates` (`id`, `title`, `division_id`, `activity_id`, `sub_activity_id`, `billing_month`, `notes`, `file_name`, `file_path`, `file_ext`, `file_size`, `created_by`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($processUpdates as $pu) {
        $stmt->execute($pu);
    }
    echo "[✓] Process Updates seeded.\n";

    // 20. Contacts Directory
    $contacts = [
        [1, 'IDFC First Bank - Central Operations SPOC', '022-61234567', 'central.ops@idfcfirst.com', 'Client', 'Primary contact for agency billing and clearing disputes', 1],
        [2, 'HDFC Bank - Card Settlements Desk', '022-68901234', 'card.settlements@hdfcbank.com', 'Client', 'Escalations for chargeback representment and pre-arbitration', 1],
        [3, 'TCS Financial Solutions - ERP Integration Team', '044-24567890', 'support.banking@tcs.com', 'Third Party', 'Technical vendor for SAP 3-way match connectors', 2],
        [4, 'Internal IT Infrastructure & Database Helpdesk', '1800-419-0099', 'it.helpdesk@company.com', 'Internal', 'Internal database, core server and network support', 1],
        [5, 'Kotak Mahindra Bank - Escrow Clearing Cell', '022-67890123', 'escrow.ops@kotak.com', 'Client', 'Escrow account audit statements and reconciliation inquiries', 2]
    ];
    $stmt = $pdo->prepare("INSERT INTO `contacts` (`id`, `name`, `contact_number`, `email`, `organisation_type`, `remarks`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($contacts as $c) {
        $stmt->execute($c);
    }
    echo "[✓] Contacts Directory seeded.\n";

    // 21. Recurring Templates & Instances
    $recurringTemplates = [
        [1, 'Monthly IDFC End-of-Day Agency Commission Recon', 'Automated ticket generation on 5th of every month for agency commission register', 1, 1, 5, 'Monthly', 5, 'High', '2026-01-01', null, 1, '2026-10-05 06:00:00', 2],
        [2, 'Weekly NACH Return Code Healthcheck & Scrub', 'Weekly batch run every Monday for pending return codes', 2, 3, 6, 'Weekly', 1, 'Medium', '2026-01-01', null, 1, '2026-10-06 06:00:00', 3],
        [3, 'Monthly Vendor Tax Invoice Audit Sample Run', 'Monthly audit run for 10% sample vendor invoices', 3, 5, 7, 'Monthly', 10, 'High', '2026-01-01', null, 1, '2026-09-10 06:00:00', 2]
    ];
    $stmt = $pdo->prepare("INSERT INTO `recurring_templates` (`id`, `template_name`, `description`, `activity_id`, `sub_activity_id`, `assigned_user_id`, `frequency`, `due_day`, `priority`, `start_date`, `end_date`, `is_active`, `last_generated_at`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($recurringTemplates as $rt) {
        $stmt->execute($rt);
    }
    echo "[✓] Recurring Templates seeded.\n";

    $recurringInstances = [
        [1, 1, 8, '2026-10-05 06:00:00', 'Generated']
    ];
    $stmt = $pdo->prepare("INSERT INTO `recurring_instances` (`id`, `template_id`, `ticket_id`, `generated_date`, `status`) VALUES (?, ?, ?, ?, ?)");
    foreach ($recurringInstances as $ri) {
        $stmt->execute($ri);
    }
    echo "[✓] Recurring Instances seeded.\n";

    // 22. Daily Production Logs (Past 7 days for multiple users)
    $productionLogs = [
        // Varun (User 5)
        [1, 5, 1, 1, '2026-10-05', 45, 40, 8.00, 0, 0, 'Present', 'Target achieved smoothly', 5],
        [2, 5, 1, 1, '2026-10-06', 42, 40, 8.00, 1, 0, 'Present', '1 calculation rework fixed', 5],
        [3, 5, 1, 2, '2026-10-07', 38, 35, 8.00, 0, 0, 'Present', 'Handled complex reconciliation files', 5],
        [4, 5, 1, 1, '2026-10-08', 48, 40, 8.50, 0, 0, 'Present', 'High volume intake handled', 5],
        [5, 5, 1, 1, '2026-10-09', 44, 40, 8.00, 0, 0, 'Present', 'All tasks cleared before cutoff', 5],

        // Sneha (User 6)
        [6, 6, 1, 3, '2026-10-05', 52, 50, 8.00, 0, 0, 'Present', 'NACH mandates cleared', 6],
        [7, 6, 1, 3, '2026-10-06', 50, 50, 8.00, 0, 0, 'Present', 'On time throughput', 6],
        [8, 6, 1, 4, '2026-10-07', 48, 45, 8.00, 1, 0, 'Present', 'Return memo scrubbed', 6],
        [9, 6, 2, 9, '2026-10-08', 32, 30, 8.00, 0, 0, 'Present', 'Fraud chargeback investigations completed', 6],
        [10, 6, 2, 9, '2026-10-09', 35, 30, 8.00, 0, 0, 'Present', 'Excellent throughput', 6],

        // Karthik (User 7 - AP & Invoicing)
        [11, 7, 3, 5, '2026-10-05', 65, 60, 8.00, 0, 0, 'Present', 'Vendor invoices processed', 7],
        [12, 7, 3, 5, '2026-10-06', 62, 60, 8.00, 0, 0, 'Present', '3-way match cleared', 7],
        [13, 7, 3, 6, '2026-10-07', 58, 55, 8.00, 0, 0, 'Present', 'GST matching completed', 7],
        [14, 7, 3, 5, '2026-10-08', 70, 60, 8.50, 1, 0, 'Present', 'Peak batch volume', 7],
        [15, 7, 3, 5, '2026-10-09', 64, 60, 8.00, 0, 0, 'Present', 'Logged and approved', 7],

        // Divya (User 8 - AP & Invoicing)
        [16, 8, 3, 5, '2026-10-05', 60, 60, 8.00, 0, 0, 'Present', 'Standard shift output', 8],
        [17, 8, 3, 5, '2026-10-06', 64, 60, 8.00, 0, 0, 'Present', 'Target exceeded', 8],
        [18, 8, 3, 6, '2026-10-07', 56, 55, 8.00, 0, 0, 'Present', 'GST reconciliation cleared', 8],
        [19, 8, 3, 5, '2026-10-08', 62, 60, 8.00, 0, 0, 'Present', 'Good accuracy', 8],
        [20, 8, 3, 5, '2026-10-09', 61, 60, 8.00, 0, 0, 'Present', 'Logged and finalized', 8]
    ];

    $stmt = $pdo->prepare("INSERT INTO `daily_production_logs` (
        `id`, `user_id`, `project_id`, `sub_activity_id`, `log_date`,
        `processed_count`, `target_count`, `worked_hours`, `internal_errors`,
        `external_errors`, `shift_status`, `remarks`, `created_by`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($productionLogs as $pl) {
        $stmt->execute($pl);
    }
    echo "[✓] Daily Production Logs seeded (20 shift logs across makers).\n";

    // 23. Monthly Scorecards (Datamatics Ledger & Lifecycle Snapshot)
    $scorecards = [
        [
            1, 5, 1, '2026-09', 24.0, 22.0, 2.0, 880, 1150.00, 52.27, 450.00, 116.16, 2, 20.00,
            1, 20.00, 99.89, 0, 30.00, 100.00, 10.00, 95.00, 9.17, 10.00, 99.17,
            'Star Performer', 'Exceptional Quality & TAT', 1, 'Outstanding performance in Q3 agency commission settlements.',
            'Exceptional speed and zero client complaints during peak reconciliation cycle.',
            'Star of the Month', 'Awarded for highest turnaround accuracy & flawless execution.',
            'Verified', 3, '2026-10-01 10:30:00', null, null
        ],
        [
            2, 6, 1, '2026-09', 24.0, 25.0, -1.0, 950, 1220.00, 48.80, 450.00, 108.44, 2, 20.00,
            2, 15.00, 99.79, 0, 30.00, 100.00, 10.00, 92.00, 10.00, 10.00, 85.00,
            'Consistent Exceeder', 'Very Good Quality', 2, 'Great throughput on NACH mandate returns and chargeback tracking.',
            'Volunteered for additional weekend shift to clear pending client backlog (Overtime logged: -1.0 leave).',
            'Extra Miler Award', 'Extra shift support and flawless mandate clearance.',
            'Locked', 3, '2026-10-01 11:00:00', 2, '2026-10-02 09:15:00'
        ],
        [
            3, 7, 3, '2026-09', 24.0, 23.5, 0.5, 1350, 1620.00, 68.94, 500.00, 137.88, 3, 30.00,
            1, 20.00, 99.93, 0, 30.00, 100.00, 10.00, 90.00, 9.79, 10.00, 99.79,
            'Star Performer', 'High Production Leader', 1, 'Processed highest invoice batch volume in AP team.',
            'Tier-3 Production Scale achieved with 137.88% target benchmark achievement.',
            'Volume Champion', 'Exceeded tier-3 target volume across all working days.',
            'Draft', null, null, null, null
        ],
        [
            4, 8, 3, '2026-09', 24.0, 21.0, 3.0, 1180, 1300.00, 61.90, 500.00, 123.80, 3, 30.00,
            3, 15.00, 99.75, 1, 20.00, 99.92, 8.00, 88.00, 8.75, 10.00, 83.75,
            'Standard', 'Meets Expectations', 2, 'Consistent output and reliable quality matching.',
            'Good performance overall; minor external discrepancy identified in batch #402.',
            null, null,
            'Draft', null, null, null, null
        ]
    ];
    $stmt = $pdo->prepare("INSERT INTO `monthly_scorecards` (
        `id`, `user_id`, `project_id`, `year_month`, `working_days`, `present_days`, `leave_days`,
        `processed_count`, `total_points`, `avg_points`, `target_points`, `points_pct`, `production_scale`, `production_score`,
        `internal_errors`, `int_accuracy_score`, `int_accuracy_pct`, `external_errors`, `ext_accuracy_score`, `ext_accuracy_pct`,
        `pkt_score`, `pkt_pct`, `attendance_score`, `timesheet_score`, `total_score`,
        `grade_name`, `grade_rating`, `team_rank`, `manager_remarks`, `special_note`,
        `recognition_type`, `recognition_desc`, `status`, `verified_by`, `verified_at`, `locked_by`, `locked_at`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($scorecards as $sc) {
        $stmt->execute($sc);
    }
    echo "[✓] Monthly Scorecards seeded (4 realistic operational snapshots with lifecycle & awards).\n";

    // 23b. Timesheet Logs (Daily Effort Tracking)
    $timesheetLogs = [
        [1, 5, 1, 1, 1, '2026-10-05', 8.00, 'Processed batch commissions and fee reconciliations for agent network.', 'Approved', 3],
        [2, 5, 1, 1, 1, '2026-10-06', 8.00, 'Handled agency dispute queries and recalculation sheets.', 'Approved', 3],
        [3, 5, 1, 1, 2, '2026-10-07', 8.00, 'Reconciled high volume statement of accounts with bank feeds.', 'Approved', 3],
        [4, 5, 1, 1, 1, '2026-10-08', 8.50, 'Escalated variance tickets and cleared pending approvals.', 'Approved', 3],
        [5, 5, 1, 1, 1, '2026-10-09', 8.00, 'Weekly batch reconciliation close and report generation.', 'Approved', 3],

        [6, 6, 1, 2, 3, '2026-10-05', 8.00, 'Scrubbed NACH mandates and registered clearing files.', 'Approved', 3],
        [7, 6, 1, 2, 3, '2026-10-06', 8.00, 'Investigation of NPCI return codes and unfreeze requests.', 'Approved', 3],
        [8, 6, 1, 2, 4, '2026-10-07', 8.00, 'Processed chargeback representment bundles for cards.', 'Approved', 3],
        [9, 6, 1, 2, 3, '2026-10-08', 8.00, 'Daily mandate clearance and customer communication logs.', 'Approved', 3],
        [10, 6, 1, 2, 3, '2026-10-09', 8.00, 'Mandate status reporting and weekend shift planning.', 'Approved', 3],

        [11, 7, 3, 3, 5, '2026-10-05', 8.00, 'Logged vendor invoices into ERP and validated PO attachments.', 'Submitted', null],
        [12, 7, 3, 3, 5, '2026-10-06', 8.00, '3-Way invoice matching for heavy equipment purchases.', 'Submitted', null],
        [13, 7, 3, 3, 6, '2026-10-07', 8.00, 'GST input tax credit reconciliation against GSTR-2B.', 'Submitted', null],
        [14, 7, 3, 3, 5, '2026-10-08', 8.50, 'High priority vendor payment processing before weekend cycle.', 'Submitted', null],
        [15, 7, 3, 3, 5, '2026-10-09', 8.00, 'Weekly invoice log consolidation and vendor queries.', 'Submitted', null],

        [16, 8, 3, 3, 5, '2026-10-05', 8.00, 'Vendor invoice intake and initial optical character verification.', 'Submitted', null],
        [17, 8, 3, 3, 5, '2026-10-06', 8.00, 'PO line item matching and quantity clearance.', 'Submitted', null],
        [18, 8, 3, 3, 6, '2026-10-07', 8.00, 'Vendor TDS and GST credit verification.', 'Submitted', null],
        [19, 8, 3, 3, 5, '2026-10-08', 8.00, 'Cleared batch mismatch errors and communicated with procurement.', 'Submitted', null],
        [20, 8, 3, 3, 5, '2026-10-09', 8.00, 'Weekend AP balance ledger reconciliation.', 'Submitted', null]
    ];
    $stmt = $pdo->prepare("INSERT INTO `timesheet_logs` (
        `id`, `user_id`, `project_id`, `activity_id`, `sub_activity_id`, `log_date`, `hours_spent`, `task_description`, `status`, `approved_by`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($timesheetLogs as $ts) {
        $stmt->execute($ts);
    }
    echo "[✓] Timesheet Logs seeded (20 effort logs across makers).\n";

    // 24. Team KPI Configs
    $kpiConfigs = [
        [1, 1, 'sla_adherence', 'SLA TAT Adherence', 30, 98.00, 'Percentage of tickets closed within agreed SLA hours'],
        [2, 1, 'first_time_right', 'First Time Right (FTR %)', 25, 95.00, 'Percentage of outputs approved without rework'],
        [3, 1, 'production_volume', 'Daily Target Volume Output', 25, 100.00, 'Target count achievement percentage'],
        [4, 1, 'pkt_compliance', 'PKT & Assessment Score', 20, 85.00, 'Mandatory monthly knowledge test compliance'],

        [5, 3, 'invoice_accuracy', '3-Way Match Accuracy', 35, 99.00, 'Zero invoice discrepancy rate'],
        [6, 3, 'daily_batch_volume', 'Daily Invoice Throughput', 35, 100.00, 'Standard 60 invoices / day / FTE target'],
        [7, 3, 'gst_reconciliation', 'GST 2B Match Rate', 30, 98.00, 'Input tax credit ledger alignment']
    ];
    $stmt = $pdo->prepare("INSERT INTO `team_kpi_configs` (`id`, `project_id`, `kpi_key`, `label`, `weight_points`, `target_pct`, `description`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($kpiConfigs as $kpi) {
        $stmt->execute($kpi);
    }
    echo "[✓] Team KPI Configurations seeded.\n";

    // 25. Training & PKT System
    // Training Plans
    $trainingPlans = [
        [1, 5, 2, json_encode([5, 6]), 1, 'Comprehensive Training on Agency Commission Reconciliation', 'Detailed module on fee calculation formulas, GST components, and reconciliation registers.', '2026-10-04 10:00:00', 'https://meet.google.com/xyz-mqt-demo', 'https://meet.google.com/xyz-mqt-demo', 'uploads/training/Agency_Commission_KT.pdf', 'Agency_Commission_KT.pdf', 'Completed', 2],
        [2, 6, 3, json_encode([6]), 3, 'NACH e-Mandate Return Code Handling & Dispute Resolution', 'Masterclass on NPCI return code hierarchy, dispute documentation, and reversal flows.', '2026-10-03 14:00:00', 'https://meet.google.com/nach-demo-trg', 'https://meet.google.com/nach-demo-trg', 'uploads/training/NACH_Dispute_KT.pdf', 'NACH_Dispute_KT.pdf', 'Completed', 3],
        [3, 7, 2, json_encode([7, 8]), 5, '3-Way Match Invoice Processing & GST Validation', 'Standard SAP AP module training for vendor invoice logging and credit note clearance.', '2026-10-12 11:00:00', 'https://meet.google.com/gst-inv-trg', 'https://meet.google.com/gst-inv-trg', 'uploads/training/SAP_AP_KT.pdf', 'SAP_AP_KT.pdf', 'Scheduled', 2]
    ];
    $stmt = $pdo->prepare("INSERT INTO `training_plans` (`id`, `user_id`, `trainer_id`, `trainees_json`, `sub_activity_id`, `title`, `description`, `training_date`, `meeting_url`, `meeting_link`, `kt_document_path`, `kt_document_name`, `status`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($trainingPlans as $tp) {
        $stmt->execute($tp);
    }
    echo "[✓] Training Plans seeded.\n";

    // Training Plan Trainees
    $trainingTrainees = [
        [1, 1, 5, 1, '2026-10-04 11:30:00'],
        [2, 1, 6, 1, '2026-10-04 11:45:00'],
        [3, 2, 6, 1, '2026-10-03 16:00:00'],
        [4, 3, 7, 0, null],
        [5, 3, 8, 0, null]
    ];
    $stmt = $pdo->prepare("INSERT INTO `training_plan_trainees` (`id`, `training_plan_id`, `user_id`, `is_acknowledged`, `acknowledged_at`) VALUES (?, ?, ?, ?, ?)");
    foreach ($trainingTrainees as $tt) {
        $stmt->execute($tt);
    }
    echo "[✓] Training Plan Trainees assigned.\n";

    // User Training Progress
    $userTrainingProgress = [
        [1, 5, 1, 1, 1, '2026-10-04 11:30:00', '2026-10-04 11:30:00', 'Understood commission calculation formula and ledger matching'],
        [2, 6, 1, 1, 1, '2026-10-04 11:45:00', '2026-10-04 11:45:00', 'Reviewed reconciliation procedures and sample cases'],
        [3, 6, 2, 3, 1, '2026-10-03 16:00:00', '2026-10-03 16:00:00', 'Clear on NPCI return code classifications']
    ];
    $stmt = $pdo->prepare("INSERT INTO `user_training_progress` (`id`, `user_id`, `training_plan_id`, `sub_activity_id`, `is_learned`, `learned_at`, `acknowledged_at`, `acknowledgement_remarks`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($userTrainingProgress as $utp) {
        $stmt->execute($utp);
    }
    echo "[✓] User Training Progress seeded.\n";

    // PKT Question Bank (5 high-quality questions per sub-activity)
    $qStmt = $pdo->prepare("INSERT INTO `pkt_question_bank` (`sub_activity_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    for ($saId = 1; $saId <= 10; $saId++) {
        $questions = [
            ["What is the primary document required before initiating processing for Sub-Activity #$saId?", "Signed source ledger/statement with authorized approval", "Unsigned handwritten note", "Blank template form", "Informal chat screenshot", "A", 2],
            ["What is the standard SLA turn-around time (TAT) policy for Sub-Activity #$saId deliverables?", "Within the assigned shift SLA cutoff window", "Within 30 calendar days", "No SLA applies", "End of financial year only", "A", 2],
            ["If an error or discrepancy is detected during validation of Sub-Activity #$saId, what is the mandatory next step?", "Log error observation details and route to Checker for rework review", "Silently overwrite incorrect numbers", "Delete failing entries without audit trace", "Mark ticket closed immediately", "A", 2],
            ["Which regulatory standard governs data privacy and retention for Sub-Activity #$saId records?", "Enterprise Data Protection & ISO/IEC 27001 compliance standards", "Public domain guidelines", "No standard applies", "Personal discretion of associate", "A", 2],
            ["Who is authorized to give final four-eyes quality sign-off on Sub-Activity #$saId?", "Assigned Quality Checker / Team Lead", "External third-party visitor", "Trainee associate", "Unverified customer", "A", 2]
        ];
        foreach ($questions as $q) {
            $qStmt->execute([$saId, $q[0], $q[1], $q[2], $q[3], $q[4], $q[5], $q[6]]);
        }
    }
    echo "[✓] PKT Question Bank seeded (50 domain questions across 10 sub-activities).\n";

    // PKT Tests (Ensure tests exist for sub activities)
    $pktTests = [
        [1, 'PKT Assessment: Agency Commission & Billing Verification', 1, 5, 70, 20, 'Published', 2],
        [2, 'PKT Assessment: Agency Billing Reconciliation', 2, 5, 70, 20, 'Published', 2],
        [3, 'PKT Assessment: NACH Mandates & Return Resolution', 3, 5, 70, 20, 'Published', 2],
        [4, 'PKT Assessment: NACH Return Memo Verification', 4, 5, 70, 20, 'Published', 2],
        [5, 'PKT Assessment: Vendor Invoice 3-Way Match & GST Rules', 5, 5, 70, 20, 'Published', 2],
        [6, 'PKT Assessment: PO to Invoice 3-Way Match', 6, 5, 70, 20, 'Published', 2],
        [7, 'PKT Assessment: Escrow Reconciliations & Treasury Operations', 7, 5, 70, 20, 'Published', 2],
        [8, 'PKT Assessment: Nostro Account Adjustments', 8, 5, 70, 20, 'Published', 2],
        [9, 'PKT Assessment: Credit Card Fraud & Dispute Investigations', 9, 5, 70, 20, 'Published', 2],
        [10, 'PKT Assessment: Customer Dispute Representment Flow', 10, 5, 70, 20, 'Published', 2]
    ];
    $stmt = $pdo->prepare("INSERT INTO `pkt_tests` (`id`, `test_title`, `sub_activity_id`, `total_questions`, `passing_score`, `time_limit_minutes`, `status`, `created_by`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($pktTests as $pt) {
        $stmt->execute($pt);
    }
    echo "[✓] PKT Tests seeded (10 tests published).\n";

    // PKT Test Attempts
    $pktAttempts = [
        [1, 1, 5, 1, 5, 5, 100.00, 1, '2026-10-04 12:00:00', '{"1":"A","2":"B","3":"C","4":"A","5":"D"}'],
        [2, 2, 6, 3, 5, 4, 80.00, 1, '2026-10-03 16:30:00', '{"1":"B","2":"A","3":"C","4":"D","5":"A"}'],
        [3, 3, 7, 5, 5, 5, 100.00, 1, '2026-09-29 15:00:00', '{"1":"C","2":"A","3":"D","4":"B","5":"A"}']
    ];
    $stmt = $pdo->prepare("INSERT INTO `pkt_test_attempts` (`id`, `test_id`, `user_id`, `sub_activity_id`, `total_questions`, `correct_answers`, `score_percentage`, `passed`, `attempt_date`, `answers_json`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($pktAttempts as $pa) {
        $stmt->execute($pa);
    }
    echo "[✓] PKT Test Attempts seeded.\n";

    // Activity Certifications
    $certifications = [
        [1, 5, 1, 1, 1, 100.00, 1, 'Certified', '2026-10-04 14:00:00', 'CERT-MQT-2026-001'],
        [2, 6, 3, 1, 1, 80.00, 1, 'Certified', '2026-10-03 17:00:00', 'CERT-MQT-2026-002'],
        [3, 7, 5, 1, 1, 100.00, 1, 'Certified', '2026-09-29 16:00:00', 'CERT-MQT-2026-003'],
        [4, 8, 5, 1, 0, 60.00, 0, 'In Progress', null, null]
    ];
    $stmt = $pdo->prepare("INSERT INTO `activity_certifications` (`id`, `user_id`, `sub_activity_id`, `training_completed`, `pkt_passed`, `pkt_score`, `practice_passed`, `status`, `certified_at`, `certificate_code`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($certifications as $cert) {
        $stmt->execute($cert);
    }
    echo "[✓] Activity Certifications seeded.\n";

    // Practice Files
    $practiceFiles = [
        [1, 5, 1, 1, 1, 'Agency Commission Sample Batch #401 Practice Exercise', 'uploads/practice/varun_batch401.xlsx', 'varun_batch401.xlsx', 'Completed all calculations accurately', 4, 'Approved', null, null, 'Internal', '2026-10-04 12:30:00', '2026-10-04 13:45:00'],
        [2, 6, 1, 2, 3, 'NACH Return Code Scrubbing Exercise', 'uploads/practice/sneha_nach_prac.xlsx', 'sneha_nach_prac.xlsx', 'Verified all 20 return reason mappings', 4, 'Approved', null, null, 'Internal', '2026-10-03 16:45:00', '2026-10-03 16:55:00']
    ];
    $stmt = $pdo->prepare("INSERT INTO `practice_files` (`id`, `user_id`, `division_id`, `activity_id`, `sub_activity_id`, `task_title`, `file_path`, `file_name`, `remarks`, `checker_id`, `status`, `error_observation`, `error_description`, `error_type`, `submitted_at`, `validated_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($practiceFiles as $pf) {
        $stmt->execute($pf);
    }
    echo "[✓] Practice Files seeded.\n";

    // 26. Notifications
    $notifications = [
        [1, 5, 'New Ticket Assigned: MQT-2026-1002', 'You have been allocated a new NACH Return Code query. Due: Today.', '/tickets/view/2', 0],
        [2, 5, 'Escalation Alert: MQT-2026-1011 Overdue', 'Ticket MQT-2026-1011 has exceeded SLA TAT. Please take immediate action.', '/tickets/view/11', 0],
        [3, 6, 'Quality Review Approved', 'Checker Anand Verma has approved your chargeback investigation packet for Ticket MQT-2026-1006.', '/tickets/view/6', 1],
        [4, 2, 'Monthly Production Report Ready', 'September 2026 scorecards and team throughput analytics are now published.', '/reports/daywise', 0],
        [5, 4, 'Checker Review Queue Pending', 'There are 3 tickets waiting for your four-eyes quality review in IDFC Banking project.', '/tickets/all', 0]
    ];
    $stmt = $pdo->prepare("INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `is_read`) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($notifications as $n) {
        $stmt->execute($n);
    }
    echo "[✓] Notifications seeded.\n";

    // 27. Audit Logs
    $auditLogs = [
        [1, 1, 'Authentication', 'User Login', null, 'User EMP001 logged in successfully', '127.0.0.1'],
        [2, 2, 'Tickets', 'Ticket Created', null, 'Created Ticket MQT-2026-1001 for IDFC Agency Commission', '127.0.0.1'],
        [3, 3, 'Tickets', 'Status Updated', 'Assigned', 'Status updated to In Progress for Ticket MQT-2026-1003', '127.0.0.1'],
        [4, 2, 'Hold', 'Ticket Held', 'In Progress', 'Held Ticket MQT-2026-1005 due to core banking maintenance', '127.0.0.1'],
        [5, 4, 'Quality', 'Error Logged', null, 'Logged tax calculation mismatch error for maker Varun Kumar', '127.0.0.1'],
        [6, 1, 'Master', 'Project Configured', null, 'Configured operational model for IDFC Banking Operations (ticket_workflow)', '127.0.0.1']
    ];
    $stmt = $pdo->prepare("INSERT INTO `audit_logs` (`id`, `user_id`, `module`, `action`, `old_values`, `new_values`, `ip_address`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($auditLogs as $al) {
        $stmt->execute($al);
    }
    echo "[✓] Audit Logs seeded.\n";

    // 28. Settings
    $settings = [
        ['app_title', 'Mail Query Tracker (MQT) Enterprise', 'Application display title'],
        ['company_name', 'Global Operations Services Pvt Ltd', 'Company organization name'],
        ['default_tat_hours', '24', 'Global default SLA TAT hours'],
        ['session_timeout_minutes', '60', 'Inactivity session logout timeout in minutes'],
        ['enable_email_notifications', '1', 'Master switch for email alerts'],
        ['theme_color', '#2563eb', 'Primary accent brand color'],
        ['maker_checker_enabled', '1', 'Enable mandatory four-eyes review on financial tickets'],
        ['maintenance_mode', '0', 'System maintenance mode switch']
    ];
    $stmt = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `description` = VALUES(`description`)");
    foreach ($settings as $s) {
        $stmt->execute($s);
    }
    echo "[✓] Settings seeded.\n";

    // 29. Datamatics Annual Achievers Awards (AAA) & DM-ISPARK Recognition Hub
    require_once __DIR__ . '/../app/core/Database.php';
    require_once __DIR__ . '/../app/core/Model.php';
    require_once __DIR__ . '/../app/models/Award_model.php';
    $awardModel = new Award_model();
    $awardModel->seedAwardsMaster();
    $awardModel->seedScenarioWinners();
    echo "[✓] Datamatics AAA Awards Master & Scenario Winners seeded.\n";

    // Re-enable Foreign Key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n=== Database Seeding Completed Successfully! ===\n";
} catch (Exception $e) {
    echo "\n[ERROR] Seeding failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
