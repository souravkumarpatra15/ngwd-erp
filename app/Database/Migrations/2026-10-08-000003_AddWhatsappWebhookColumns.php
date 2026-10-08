<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Webhook hardening: per-integrated-number conversations + raw payloads.
 * - whatsapp_conversations.integrated_number: same customer on two
 *   business numbers stays two conversations.
 * - whatsapp_messages.raw_payload: full provider event for debugging
 *   and future media-type rendering.
 */
class AddWhatsappWebhookColumns extends Migration
{
    public function up()
    {
        $convCols = array_column($this->db->getFieldData('whatsapp_conversations'), 'name');
        if (! in_array('integrated_number', $convCols, true)) {
            $this->forge->addColumn('whatsapp_conversations', [
                'integrated_number' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'contact_type'],
            ]);
            $this->db->query('CREATE UNIQUE INDEX uq_integrated_phone ON whatsapp_conversations (integrated_number, phone_number)');
        }
        $msgCols = array_column($this->db->getFieldData('whatsapp_messages'), 'name');
        if (! in_array('raw_payload', $msgCols, true)) {
            $this->forge->addColumn('whatsapp_messages', [
                'raw_payload' => ['type' => 'TEXT', 'null' => true, 'after' => 'error_message'],
            ]);
        }
    }

    public function down()
    {
        try { $this->db->query('DROP INDEX uq_integrated_phone ON whatsapp_conversations'); } catch (\Throwable $e) { /* ignore */ }
        try { $this->forge->dropColumn('whatsapp_conversations', 'integrated_number'); } catch (\Throwable $e) { /* ignore */ }
        try { $this->forge->dropColumn('whatsapp_messages', 'raw_payload'); } catch (\Throwable $e) { /* ignore */ }
    }
}
