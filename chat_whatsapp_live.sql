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

-- 4) Mark the chat migration as done (so `php spark migrate` skips it) --
-- Only needed if the table was created via THIS sql file instead of spark.
-- The version string must match app/Database/Migrations/2026-10-08-000001_CreateChatMessagesTable.php
INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-10-08-000001', 'App\\Database\\Migrations\\CreateChatMessagesTable', 'default', 'App', UNIX_TIMESTAMP(), 4
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `version` = '2026-10-08-000001');

-- 5) Verify --------------------------------------------------------------
-- SELECT * FROM `chat_messages` LIMIT 1;
-- SELECT `key`, LEFT(`value`, 12) FROM `settings` WHERE `group` = 'whatsapp';
