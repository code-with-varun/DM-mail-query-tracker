-- ====================================================
-- Mail Query Tracker (MQT) - Database Schema DDL & Seed
-- Target Engine: MySQL / MariaDB (PHP 8.2 & XAMPP Compatible)
-- Database Name: mqt_db
-- ====================================================

CREATE DATABASE IF NOT EXISTS `mqt_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mqt_db`;

-- ----------------------------------------------------
-- Table: roles
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
(1, 'Super Admin', 'Full system access, global configuration, admin management'),
(2, 'Admin', 'Manager / Team Lead, employee & team ticket management'),
(3, 'Employee', 'Individual worker, assigned ticket execution')
ON DUPLICATE KEY UPDATE `role_name` = VALUES(`role_name`);

-- ----------------------------------------------------
-- Table: users
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_code` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `mobile` VARCHAR(20) NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` INT NOT NULL,
  `department` VARCHAR(100) DEFAULT 'Operations',
  `manager_id` INT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `last_login` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE RESTRICT,
  FOREIGN KEY (`manager_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_role` (`role_id`),
  INDEX `idx_user_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default Super Admin & Admin accounts (Default password for both: ChangeMe@123)
INSERT INTO `users` (`id`, `user_code`, `full_name`, `email`, `mobile`, `password`, `role_id`, `department`, `manager_id`, `status`) VALUES
(1, 'EMP001', 'Super Admin', 'superadmin@company.com', '9999999999', '$2y$10$FOxEKZtWl/a3yDL6L38TqeGPVz5Ee4O2IGqPExAz6.gK6oFOk/rQ6', 1, 'Management', NULL, 'Active'),
(2, 'EMP002', 'System Admin', 'admin@company.com', '9888888888', '$2y$10$FOxEKZtWl/a3yDL6L38TqeGPVz5Ee4O2IGqPExAz6.gK6oFOk/rQ6', 2, 'Operations', 1, 'Active')
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`);

-- ----------------------------------------------------
-- Table: divisions
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `divisions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `division_name` VARCHAR(100) NOT NULL UNIQUE,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `divisions` (`id`, `division_name`, `code`, `status`) VALUES
(1, 'Billing & Finance', 'BF', 'Active'),
(2, 'Vendor Management', 'VM', 'Active'),
(3, 'Internal Operations', 'IO', 'Active'),
(4, 'Agency Services', 'AS', 'Active')
ON DUPLICATE KEY UPDATE `division_name` = VALUES(`division_name`);

-- ----------------------------------------------------
-- Table: activities
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `activities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `division_id` INT NULL,
  `activity_name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`division_id`) REFERENCES `divisions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `activities` (`id`, `division_id`, `activity_name`, `description`, `status`) VALUES
(1, 1, 'Agency Billing', 'Billing, payments, and agency account reconciliation', 'Active'),
(2, 2, 'Empanelment', 'Vendor registrations, contracts, and documentation', 'Active'),
(3, 3, 'Inhouse', 'Internal support, process clarification, system issues', 'Active'),
(4, 4, 'Vendor', 'Vendor support, contract queries, invoice queries', 'Active')
ON DUPLICATE KEY UPDATE `activity_name` = VALUES(`activity_name`);

-- ----------------------------------------------------
-- Table: sub_activities
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sub_activities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `activity_id` INT NOT NULL,
  `division_id` INT NULL,
  `sub_activity_name` VARCHAR(100) NOT NULL,
  `default_tat_hours` INT DEFAULT 24,
  `default_user_id` INT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`activity_id`) REFERENCES `activities`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`division_id`) REFERENCES `divisions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`default_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_subact_activity` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sub_activities` (`id`, `activity_id`, `division_id`, `sub_activity_name`, `default_tat_hours`, `default_user_id`) VALUES
(1, 1, 1, 'Billing Query', 24, 2),
(2, 1, 1, 'Payment Query', 12, 2),
(3, 1, 1, 'Reconciliation', 48, 2),
(4, 2, 2, 'Vendor Registration', 48, 2),
(5, 2, 2, 'Approval Query', 24, 2),
(6, 2, 2, 'Documentation', 36, 2),
(7, 3, 3, 'Internal Support', 8, 2),
(8, 3, 3, 'System Issue', 4, 2),
(9, 3, 3, 'Process Clarification', 12, 2),
(10, 4, 4, 'Vendor Support', 24, 2),
(11, 4, 4, 'Contract Query', 48, 2),
(12, 4, 4, 'Invoice Query', 24, 2)
ON DUPLICATE KEY UPDATE `sub_activity_name` = VALUES(`sub_activity_name`);

-- ----------------------------------------------------
-- Table: tickets
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_number` VARCHAR(50) NOT NULL UNIQUE,
  `ticket_type` ENUM('Query Ticket', 'Task Ticket') DEFAULT 'Query Ticket',
  `received_datetime` DATETIME NOT NULL,
  `from_address` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `division_id` INT NULL,
  `activity_id` INT NOT NULL,
  `sub_activity_id` INT NOT NULL,
  `status` ENUM(
    'New', 'Assigned', 'In Progress', 'Pending', 
    'Waiting for Customer', 'Waiting for Internal Team', 
    'On Hold', 'Released', 'Completed', 'Closed', 'Cancelled'
  ) DEFAULT 'New',
  `priority` ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
  `tat_datetime` DATETIME NOT NULL,
  `tat_status` ENUM('Within TAT', 'Due Today', 'Overdue') DEFAULT 'Within TAT',
  `allocated_to` INT NULL,
  `replied_by` INT NULL,
  `replied_datetime` DATETIME NULL,
  `remarks` TEXT NULL,
  `agency_code` VARCHAR(50) NULL,
  `manager_name` VARCHAR(100) NULL,
  `pending_reason` TEXT NULL,
  `created_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`division_id`) REFERENCES `divisions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`activity_id`) REFERENCES `activities`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`sub_activity_id`) REFERENCES `sub_activities`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`allocated_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`replied_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_ticket_number` (`ticket_number`),
  INDEX `idx_ticket_status` (`status`),
  INDEX `idx_ticket_tat_datetime` (`tat_datetime`),
  INDEX `idx_ticket_allocated` (`allocated_to`),
  INDEX `idx_ticket_created_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: task_tickets
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `task_tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL UNIQUE,
  `task_title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `priority` ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
  `due_date` DATETIME NOT NULL,
  `completion_remarks` TEXT NULL,
  `completion_date` DATETIME NULL,
  `attachment_path` VARCHAR(255) NULL,
  `status` VARCHAR(50) DEFAULT 'Pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: ticket_comments
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `comment` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: ticket_attachments
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_attachments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT NULL,
  `uploaded_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: recurring_templates
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `recurring_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `template_name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `activity_id` INT NOT NULL,
  `sub_activity_id` INT NOT NULL,
  `assigned_user_id` INT NOT NULL,
  `frequency` ENUM('Daily', 'Weekly', 'Monthly', 'Quarterly', 'Half Yearly', 'Yearly') NOT NULL DEFAULT 'Monthly',
  `due_day` INT DEFAULT 5,
  `priority` ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_generated_at` DATETIME NULL,
  `created_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`activity_id`) REFERENCES `activities`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`sub_activity_id`) REFERENCES `sub_activities`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`assigned_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: recurring_instances
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `recurring_instances` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `template_id` INT NOT NULL,
  `ticket_id` INT NOT NULL,
  `generated_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(50) DEFAULT 'Generated',
  FOREIGN KEY (`template_id`) REFERENCES `recurring_templates`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: input_tracker
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `input_tracker` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `source` VARCHAR(100) NOT NULL,
  `received_date` DATETIME NOT NULL,
  `received_from` VARCHAR(150) NOT NULL,
  `document_reference` VARCHAR(100) NOT NULL,
  `assigned_to` INT NULL,
  `status` VARCHAR(50) DEFAULT 'Received',
  `remarks` TEXT NULL,
  `created_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: delivery_tracker
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_tracker` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `delivery_number` VARCHAR(50) NOT NULL UNIQUE,
  `ticket_id` INT NULL,
  `delivered_to` VARCHAR(150) NOT NULL,
  `delivery_date` DATETIME NOT NULL,
  `delivery_mode` ENUM('Email', 'Courier', 'Portal Upload', 'Hand Delivery') NOT NULL DEFAULT 'Email',
  `ack_received` ENUM('Yes', 'No', 'Pending') DEFAULT 'Pending',
  `remarks` TEXT NULL,
  `attachment_path` VARCHAR(255) NULL,
  `created_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: hold_history
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hold_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `hold_reason` VARCHAR(255) NOT NULL,
  `hold_date` DATETIME NOT NULL,
  `expected_release_date` DATETIME NULL,
  `held_by` INT NOT NULL,
  `release_date` DATETIME NULL,
  `released_by` INT NULL,
  `remarks` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`held_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`released_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: notifications
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_notif_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: audit_logs
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `module` VARCHAR(50) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `old_values` TEXT NULL,
  `new_values` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_audit_module` (`module`),
  INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- Table: settings
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('app_title', 'Mail Query Tracker', 'Application display title'),
('company_name', 'Enterprise Systems Inc', 'Company organization name'),
('default_tat_hours', '24', 'Global default SLA TAT hours'),
('session_timeout_minutes', '30', 'Inactivity session logout timeout in minutes')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
