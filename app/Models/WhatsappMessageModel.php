<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappMessageModel extends Model
{
    protected $table      = 'whatsapp_messages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'conversation_id', 'provider_message_id', 'phone_number', 'direction',
        'message_type', 'message_text', 'template_name', 'template_params',
        'media_url', 'media_type', 'created_by', 'status',
        'error_code', 'error_message', 'raw_payload',
        'sent_at', 'delivered_at', 'read_at',
    ];

    public function history(int $conversationId, int $afterId = 0, int $limit = 200): array
    {
        $rows = $this->db->table($this->table)->where('conversation_id', $conversationId)
            ->where('id >', max(0, $afterId))
            ->orderBy('id', 'ASC')->limit(max(1, min(500, $limit)))
            ->get()->getResultArray();
        return array_map([$this, 'formatRow'], $rows);
    }

    public function findByProviderId(string $pid): ?array
    {
        if ($pid === '') return null;
        $row = $this->where('provider_message_id', $pid)->first();
        return $row ?: null;
    }

    /** WhatsApp-bubble shape. is_me = sent by the CRM side. */
    public function formatRow(?array $row): ?array
    {
        if (! $row) return null;
        $row['is_me'] = ($row['direction'] ?? '') === 'outbound';
        $row['time'] = function_exists('relativeTime')
            ? relativeTime((string) ($row['created_at'] ?? '')) : (string) ($row['created_at'] ?? '');
        $row['clock'] = ! empty($row['created_at']) && strtotime((string) $row['created_at']) !== false
            ? date('h:i A', strtotime((string) $row['created_at'])) : '';
        $row['day'] = ! empty($row['created_at']) && strtotime((string) $row['created_at']) !== false
            ? date('d M Y', strtotime((string) $row['created_at'])) : '';
        $params = json_decode((string) ($row['template_params'] ?? ''), true);
        $row['template_params'] = is_array($params) ? $params : [];
        return $row;
    }

    public function setStatus(int $id, string $status, array $extra = []): void
    {
        $this->db->table($this->table)->where('id', $id)->update(['status' => $status] + $extra);
    }
}
