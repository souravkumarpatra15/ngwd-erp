<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Conversations are keyed per (integrated_number, phone_number) so the
 * same customer on two business numbers stays two conversations.
 * Drops the phone-only unique key; the composite unique from
 * 2026-10-08-000003 remains the guard (NULL-safe in MySQL/MariaDB).
 */
class RelaxWhatsappPhoneUnique extends Migration
{
    public function up()
    {
        try {
            $this->db->query('DROP INDEX uq_phone ON whatsapp_conversations');
        } catch (\Throwable $e) {
            log_message('info', 'RelaxWhatsappPhoneUnique: uq_phone already absent.');
        }
    }

    public function down()
    {
        // Only restorable when no phone appears twice.
        try {
            $this->db->query('CREATE UNIQUE INDEX uq_phone ON whatsapp_conversations (phone_number)');
        } catch (\Throwable $e) {
            log_message('error', 'RelaxWhatsappPhoneUnique down failed: {message}', ['message' => $e->getMessage()]);
        }
    }
}
