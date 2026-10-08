-- =====================================================================
-- NGWD-ERP : Chat + WhatsApp webhook — manual SQL (run on LIVE database)
-- Date: 2026-10-08
-- Run this in phpMyAdmin (or mysql CLI) on the LIVE database.
-- All statements are idempotent (safe to run twice).
-- =====================================================================

-- 1) Chat messages table (admin <-> user WhatsApp-style chat) ----------
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id`     INT NOT NULL,
    `user_id`      INT NOT NULL,
    `message`      TEXT NULL,
    `image_url`    VARCHAR(255) DEFAULT NULL,
    `message_type` VARCHAR(20) DEFAULT 'text',
    `is_admin`     TINYINT(1) DEFAULT 1,
    `is_read`      TINYINT(1) DEFAULT 0,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_admin_user` (`admin_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) WhatsApp webhook shared secret (for POST webhook/whatsapp) --------
-- Generate a random secret and store it here, then set the SAME value
-- as ?secret=... (or X-Webhook-Secret header) in the MSG91 inbound URL.
-- Example inbound URL: https://crm.ngwebd.com/webhook/whatsapp?secret=PASTE_SECRET_HERE
INSERT INTO `settings` (`key`, `value`, `group`)
SELECT 'whatsapp_webhook_secret', 'CHANGE_ME_TO_A_LONG_RANDOM_STRING', 'whatsapp'
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'whatsapp_webhook_secret');

-- 3) Make sure MSG91 WhatsApp settings keys exist (no overwrite) --------
INSERT INTO `settings` (`key`, `value`, `group`)
SELECT 'msg91_namespace', '', 'whatsapp'
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'msg91_namespace');

INSERT INTO `settings` (`key`, `value`, `group`)
SELECT 'msg91_session_base_url', 'https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message', 'whatsapp'
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'msg91_session_base_url');

-- 4) Canonical WhatsApp conversation store ------------------------------
CREATE TABLE IF NOT EXISTS `whatsapp_conversations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NULL,
    `lead_id` INT NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    `contact_name` VARCHAR(150) NULL,
    `contact_type` VARCHAR(20) DEFAULT 'unknown',
    `last_message_at` TIMESTAMP NULL,
    `last_message_preview` VARCHAR(255) NULL,
    `last_message_direction` VARCHAR(10) NULL,
    `unread_count` INT DEFAULT 0,
    `status` VARCHAR(20) DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_phone` (`phone_number`),
    INDEX `idx_wc_client` (`client_id`),
    INDEX `idx_wc_lead` (`lead_id`),
    INDEX `idx_wc_last` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `whatsapp_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT NOT NULL,
    `provider_message_id` VARCHAR(150) NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    `direction` VARCHAR(10) NOT NULL,
    `message_type` VARCHAR(20) DEFAULT 'text',
    `message_text` TEXT NULL,
    `template_name` VARCHAR(120) NULL,
    `template_params` TEXT NULL,
    `media_url` VARCHAR(500) NULL,
    `media_type` VARCHAR(30) NULL,
    `created_by` INT NULL,
    `status` VARCHAR(20) DEFAULT 'queued',
    `error_code` VARCHAR(50) NULL,
    `error_message` VARCHAR(500) NULL,
    `sent_at` TIMESTAMP NULL,
    `delivered_at` TIMESTAMP NULL,
    `read_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_wm_conv` (`conversation_id`),
    INDEX `idx_wm_provider` (`provider_message_id`),
    INDEX `idx_wm_phone` (`phone_number`),
    INDEX `idx_wm_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) Mark the chat migration as done (so `php spark migrate` skips it) --
-- Only needed if the table was created via THIS sql file instead of spark.
-- The version string must match app/Database/Migrations/2026-10-08-000001_CreateChatMessagesTable.php
INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-10-08-000001', 'App\\Database\\Migrations\\CreateChatMessagesTable', 'default', 'App', UNIX_TIMESTAMP(), 4
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `version` = '2026-10-08-000001');

INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-10-08-000002', 'App\\Database\\Migrations\\CreateWhatsappConversations', 'default', 'App', UNIX_TIMESTAMP(), 4
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `version` = '2026-10-08-000002');

-- 6) Webhook hardening columns (migration 2026-10-08-000003) ---------------
-- Conversations keyed per integrated business number:
ALTER TABLE `whatsapp_conversations`
    ADD COLUMN IF NOT EXISTS `integrated_number` VARCHAR(20) NULL AFTER `contact_type`;
-- (MySQL < 8.0.13 has no IF NOT EXISTS for ADD COLUMN — on older servers,
-- run the plain ADD COLUMN and ignore "duplicate column" errors.)
-- CREATE UNIQUE INDEX `uq_integrated_phone` ON `whatsapp_conversations` (`integrated_number`, `phone_number`);
-- Raw provider event per message (debugging + future media rendering):
ALTER TABLE `whatsapp_messages`
    ADD COLUMN IF NOT EXISTS `raw_payload` TEXT NULL AFTER `error_message`;

INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-10-08-000003', 'App\\Database\\Migrations\\AddWhatsappWebhookColumns', 'default', 'App', UNIX_TIMESTAMP(), 5
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `version` = '2026-10-08-000003');

-- 7) Verify --------------------------------------------------------------
-- SELECT * FROM `chat_messages` LIMIT 1;
-- SELECT * FROM `whatsapp_conversations` LIMIT 5;
-- SELECT `key`, LEFT(`value`, 12) FROM `settings` WHERE `group` = 'whatsapp';
