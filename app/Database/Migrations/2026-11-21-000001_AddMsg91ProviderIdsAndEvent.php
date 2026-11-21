<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add MSG91 provider identifiers and webhook event tracking.
 * - message_uuid: MSG91's unique message UUID from webhook events
 * - occurred_at: timestamp of the webhook event (from ts field)
 * - webhook_event: the eventName from MSG91 webhook
 */
class AddMsg91ProviderIdsAndEvent extends Migration
{
    public function up()
    {
        $cols = array_column($this->db->getFieldData('whatsapp_messages'), 'name');

        if (!in_array('message_uuid', $cols, true)) {
            $this->forge->addColumn('whatsapp_messages', [
                'message_uuid' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'provider_message_id'],
            ]);
        }

        if (!in_array('occurred_at', $cols, true)) {
            $this->forge->addColumn('whatsapp_messages', [
                'occurred_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'message_uuid'],
            ]);
        }

        if (!in_array('webhook_event', $cols, true)) {
            $this->forge->addColumn('whatsapp_messages', [
                'webhook_event' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'occurred_at'],
            ]);
        }

        // Add index on message_uuid for faster lookups
        try {
            $idxNames = array_map(static fn($o) => strtolower((string) ($o->name ?? '')), $this->db->getIndexData('whatsapp_messages'));
            if (!in_array('idx_message_uuid', $idxNames, true)) {
                $this->db->query('CREATE INDEX idx_message_uuid ON whatsapp_messages (message_uuid)');
            }
        } catch (\Throwable $e) {
            log_message('error', 'AddMsg91ProviderIdsAndEvent index skipped: {message}', ['message' => $e->getMessage()]);
        }
    }

    public function down()
    {
        try {
            $this->forge->dropColumn('whatsapp_messages', 'message_uuid');
        } catch (\Throwable $e) { /* ignore */ }
        try {
            $this->forge->dropColumn('whatsapp_messages', 'occurred_at');
        } catch (\Throwable $e) { /* ignore */ }
        try {
            $this->forge->dropColumn('whatsapp_messages', 'webhook_event');
        } catch (\Throwable $e) { /* ignore */ }
        try {
            $this->db->query('DROP INDEX idx_message_uuid ON whatsapp_messages');
        } catch (\Throwable $e) { /* ignore */ }
    }
}