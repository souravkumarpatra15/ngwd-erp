<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappConversationModel extends Model
{
    protected $table      = 'whatsapp_conversations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'client_id', 'lead_id', 'phone_number', 'contact_name', 'contact_type',
        'last_message_at', 'last_message_preview', 'last_message_direction',
        'unread_count', 'status',
    ];

    public function findByPhone(string $phone): ?array
    {
        $row = $this->where('phone_number', $phone)->first();
        return $row ?: null;
    }

    /**
     * Inbox list with search + filters (all|unread|clients|leads|unknown).
     */
    public function inbox(?string $search = null, string $filter = 'all', int $limit = 60): array
    {
        $b = $this->db->table($this->table);
        if ($filter === 'unread') $b->where('unread_count >', 0);
        elseif (in_array($filter, ['client', 'clients'], true)) $b->where('contact_type', 'client');
        elseif (in_array($filter, ['lead', 'leads'], true)) $b->where('contact_type', 'lead');
        elseif (in_array($filter, ['unknown'], true)) $b->where('contact_type', 'unknown');
        $search = trim((string) $search);
        if ($search !== '') {
            $b->groupStart()
                ->like('contact_name', $search)
                ->orLike('phone_number', preg_replace('/\D/', '', $search))
              ->groupEnd();
        }
        return $b->orderBy('last_message_at', 'DESC')->orderBy('id', 'DESC')->limit(max(1, $limit))->get()->getResultArray();
    }

    public function touch(int $id, string $preview, string $direction, bool $isInbound): void
    {
        $b = $this->db->table($this->table)->where('id', $id);
        if ($isInbound) $b->set('unread_count', 'unread_count+1', false);
        $b->update([
            'last_message_at' => date('Y-m-d H:i:s'),
            'last_message_preview' => mb_substr($preview, 0, 200),
            'last_message_direction' => $direction,
        ]);
    }

    public function markRead(int $id): void
    {
        $this->db->table($this->table)->where('id', $id)->update(['unread_count' => 0]);
        $this->db->table('whatsapp_messages')->where('conversation_id', $id)
            ->where('direction', 'inbound')->where('status', 'received')
            ->update(['status' => 'read', 'read_at' => date('Y-m-d H:i:s')]);
    }
}
