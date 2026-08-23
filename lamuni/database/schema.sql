-- Lamuni Online Loan Management System
-- Database Schema
-- Engine: InnoDB | Charset: utf8mb4 (required for Hausa diacritics: ƙ ɓ ɗ)

CREATE DATABASE IF NOT EXISTS `lamuni`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `lamuni`;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120)    NOT NULL,
  `email`         VARCHAR(191)    NOT NULL,
  `phone`         CHAR(11)        NOT NULL COMMENT '11-digit phone number (demo field)',
  `bvn`           CHAR(11)                 COMMENT '11-digit BVN (demo/mock — not verified)',
  `password_hash` VARCHAR(255)    NOT NULL,
  `role`          ENUM('borrower','admin') NOT NULL DEFAULT 'borrower',
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: loans
-- --------------------------------------------------------
CREATE TABLE `loans` (
  `id`             INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED      NOT NULL,
  `amount`         DECIMAL(12,2)     NOT NULL,
  `duration_months` TINYINT UNSIGNED NOT NULL COMMENT 'Repayment period in months',
  `purpose`        TEXT              NOT NULL,
  `status`         ENUM('Pending','Approved','Rejected','Active','Completed')
                                     NOT NULL DEFAULT 'Pending',
  `applied_at`     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `decision_at`    DATETIME                   DEFAULT NULL,
  `decision_by`    INT UNSIGNED               DEFAULT NULL COMMENT 'Admin user id',
  `decision_note`  TEXT                       DEFAULT NULL,
  `balance`        DECIMAL(12,2)              DEFAULT NULL COMMENT 'Outstanding balance after disbursement',
  `monthly_due`    DECIMAL(12,2)              DEFAULT NULL COMMENT 'Monthly repayment amount',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_loans_user`     FOREIGN KEY (`user_id`)    REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loans_decision` FOREIGN KEY (`decision_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: repayments
-- --------------------------------------------------------
CREATE TABLE `repayments` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `loan_id`        INT UNSIGNED   NOT NULL,
  `amount`         DECIMAL(12,2)  NOT NULL,
  `paid_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance_after`  DECIMAL(12,2)  NOT NULL COMMENT 'Loan balance after this payment',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_repayments_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: approvals
-- --------------------------------------------------------
CREATE TABLE `approvals` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `loan_id`     INT UNSIGNED   NOT NULL,
  `admin_id`    INT UNSIGNED   NOT NULL,
  `decision`    ENUM('Approved','Rejected') NOT NULL,
  `decided_at`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note`        TEXT                    DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_approvals_loan`  FOREIGN KEY (`loan_id`)  REFERENCES `loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_approvals_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: notifications
-- --------------------------------------------------------
CREATE TABLE `notifications` (
  `id`         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED   NOT NULL,
  `message`    TEXT           NOT NULL,
  `is_read`    TINYINT(1)     NOT NULL DEFAULT 0,
  `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: audit_log
-- --------------------------------------------------------
CREATE TABLE `audit_log` (
  `id`         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED            DEFAULT NULL COMMENT 'NULL for system-generated events',
  `action`     VARCHAR(500)   NOT NULL,
  `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
