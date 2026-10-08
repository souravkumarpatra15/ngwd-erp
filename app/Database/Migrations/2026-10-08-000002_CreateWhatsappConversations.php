<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Canonical WhatsApp conversation store (phone-keyed, client/lead linked).
 * Replaces the ad-hoc chat_messages flow — that table is left untouched
 * so no production history is destroyed; its rows are copied here once.
 */
class CreateWhatsappConversations extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS whatsapp_conversations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NULL,
                lead_id INT NULL,
                phone_number VARCHAR(20) NOT NULL,
                contact_name VARCHAR(150) NULL,
                contact_type VARCHAR(20) DEFAULT 'unknown',
                last_message_at TIMESTAMP NULL,
                last_message_preview VARCHAR(255) NULL,
                last_message_direction VARCHAR(10) NULL,
                unread_count INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'open',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_phone (phone_number),
                INDEX idx_client (client_id),
                INDEX idx_lead (lead_id),
                INDEX idx_last_message (last_message_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS whatsapp_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                provider_message_id VARCHAR(150) NULL,
                phone_number VARCHAR(20) NOT NULL,
                direction VARCHAR(10) NOT NULL,
                message_type VARCHAR(20) DEFAULT 'text',
                message_text TEXT NULL,
                template_name VARCHAR(120) NULL,
                template_params TEXT NULL,
                media_url VARCHAR(500) NULL,
                media_type VARCHAR(30) NULL,
                created_by INT NULL,
                status VARCHAR(20) DEFAULT 'queued',
                error_code VARCHAR(50) NULL,
                error_message VARCHAR(500) NULL,
                sent_at TIMESTAMP NULL,
                delivered_at TIMESTAMP NULL,
                read_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_conversation (conversation_id),
                INDEX idx_provider (provider_message_id),
                INDEX idx_phone (phone_number),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->migrateLegacyChat();
    }

    /** Best-effort copy of legacy chat_messages rows (runs only on empty target). */
    protected function migrateLegacyChat(): void
    {
        try {
            if (! $this->db->tableExists('chat_messages')) return;
            if ((int) $this->db->table('whatsapp_messages')->countAllResults() > 0) return;

            $rows = $this->db->table('chat_messages')->orderBy('created_at', 'ASC')->orderBy('id', 'ASC')->get()->getResultArray();
            foreach ($rows as $r) {
                $user = $this->db->table('users')->where('id', (int) ($r['user_id'] ?? 0))->get()->getRowArray();
                $phone = '';
                $clientId = null;
                if ($user && ! empty($user['client_id'])) {
                    $clientId = (int) $user['client_id'];
                    $c = $this->db->table('clients')->where('id', $clientId)->get()->getRowArray();
                    $phone = preg_replace('/\D/', '', (string) ($c['whatsapp'] ?? $c['phone'] ?? ''));
                    if (strlen($phone) === 10) $phone = '91' . $phone;
                }
                if ($phone === '') continue; // cannot key a conversation without a phone
                $conv = $this->db->table('whatsapp_conversations')->where('phone_number', $phone)->get()->getRowArray();
                if (! $conv) {
                    $this->db->table('whatsapp_conversations')->insert([
                        'client_id' => $clientId, 'phone_number' => $phone,
                        'contact_name' => $user['name'] ?? null, 'contact_type' => $clientId ? 'client' : 'unknown',
                    ]);
                    $convId = (int) $this->db->insertID();
                } else {
                    $convId = (int) $conv['id'];
                }
                $outbound = ((int) ($r['is_admin'] ?? 0)) === 1;
                $this->db->table('whatsapp_messages')->insert([
                    'conversation_id' => $convId, 'phone_number' => $phone,
                    'direction' => $outbound ? 'outbound' : 'inbound',
                    'message_type' => ! empty($r['image_url']) ? 'image' : 'text',
                    'message_text' => $r['message'] ?? null, 'media_url' => $r['image_url'] ?? null,
                    'created_by' => $outbound ? ($r['admin_id'] ?? null) : null,
                    'status' => $outbound ? 'sent' : 'received',
                    'created_at' => $r['created_at'] ?? date('Y-m-d H:i:s'),
                ]);
                $this->db->table('whatsapp_conversations')->where('id', $convId)->update([
                    'last_message_at' => $r['created_at'] ?? date('Y-m-d H:i:s'),
                    'last_message_preview' => mb_substr((string) ($r['message'] ?? '[image]'), 0, 200),
                    'last_message_direction' => $outbound ? 'outbound' : 'inbound',
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Legacy chat migration skipped: {message}', ['message' => $e->getMessage()]);
        }
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS whatsapp_messages');
        $this->db->query('DROP TABLE IF EXISTS whatsapp_conversations');
    }
}
