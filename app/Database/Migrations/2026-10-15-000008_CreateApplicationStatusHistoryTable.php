<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApplicationStatusHistoryTable extends Migration
{
    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `application_status_history` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `application_id` BIGINT UNSIGNED NOT NULL,

            `previous_status` ENUM(
                'applied','under_review','shortlisted',
                'assessment_scheduled','assessment_completed',
                'interview_scheduled','interview_completed',
                'selected','offer_sent','offer_accepted',
                'onboarding','joined','rejected','withdrawn','on_hold'
            ) DEFAULT NULL,

            `new_status` ENUM(
                'applied','under_review','shortlisted',
                'assessment_scheduled','assessment_completed',
                'interview_scheduled','interview_completed',
                'selected','offer_sent','offer_accepted',
                'onboarding','joined','rejected','withdrawn','on_hold'
            ) NOT NULL,

            `changed_by` BIGINT UNSIGNED NOT NULL,
            `changed_by_name` VARCHAR(100) DEFAULT NULL,
            `change_reason` VARCHAR(100) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `changed_at` DATETIME DEFAULT NULL,

            PRIMARY KEY (`id`),

            KEY `idx_application_status_history_application_id` (`application_id`),
            KEY `idx_application_status_history_changed_at` (`changed_at`)

        ) ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_general_ci;";

        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `application_status_history`');
    }
}