<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatMessageModel extends Model
{
    protected $table      = 'chat_messages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'admin_id',
        'user_id',
        'message',
        'image_url',
        'message_type',
        'is_admin',
        'is_read',
    ];

    /**
     * Recent conversations for an admin: one row per user with
     * last message + unread count, newest first.
     */
    public function conversationsForAdmin(int $adminId, int $limit = 50): array
    {
        if ($adminId <= 0) return [];
        $db = $this->db;
        $rows = $db->table($this->table . ' cm')
            ->select('cm.user_id, u.name AS user_name, MAX(cm.created_at) AS last_at')
            ->join('users u', 'u.id = cm.user_id', 'left')
            ->where('cm.admin_id', $adminId)
            ->groupBy('cm.user_id')
            ->orderBy('last_at', 'DESC')
            ->limit(max(1, $limit))
            ->get()->getResultArray();

        foreach ($rows as &$r) {
            $uid = (int) $r['user_id'];
            $last = $db->table($this->table)
                ->where('admin_id', $adminId)->where('user_id', $uid)
                ->orderBy('created_at', 'DESC')->limit(1)->get()->getRowArray();
            $r['last_message'] = $last ? $this->formatRow($last) : null;
            $r['unread_count'] = (int) $db->table($this->table)
                ->where('admin_id', $adminId)->where('user_id', $uid)
                ->where('is_admin', 0)->where('is_read', 0)
                ->countAllResults();
        }
        unset($r);
        return $rows;
    }

    /**
     * Full history between one admin and one user, oldest first.
     * Both directions — grouped so the OR can't leak other users.
     */
    public function conversation(int $adminId, int $userId, bool $forAdmin = true): array
    {
        $serve = $forAdmin ? 'admin/chat/serve' : 'portal/chat/serve';
        $rows = $this->db->table($this->table)
            ->groupStart()
                ->where('admin_id', $adminId)->where('user_id', $userId)
            ->groupEnd()
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();
        return array_map(fn($r) => $this->formatRow($r, $forAdmin, $serve), $rows);
    }

    public function markRead(int $adminId, int $userId): void
    {
        $this->db->table($this->table)
            ->where('admin_id', $adminId)->where('user_id', $userId)
            ->where('is_admin', 0)->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    /** Admin this portal user has been chatting with (latest), or null. */
    public function adminForUser(int $userId): ?int
    {
        $row = $this->db->table($this->table)
            ->select('admin_id')->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')
            ->limit(1)->get()->getRowArray();
        return $row ? (int) $row['admin_id'] : null;
    }

    /**
     * WhatsApp-bubble shape for the view/JSON.
     * is_me = sent by the viewing side ($forAdmin=true: admin viewer).
     * image_src = stream URL or null.
     */
    public function formatRow(?array $row, bool $forAdmin = true, string $serveBase = 'admin/chat/serve'): ?array
    {
        if (! $row) return null;
        $isAdminMsg = ((int) ($row['is_admin'] ?? 0)) === 1;
        $row['is_me'] = $forAdmin ? $isAdminMsg : ! $isAdminMsg;
        $row['time'] = function_exists('relativeTime')
            ? relativeTime((string) ($row['created_at'] ?? ''))
            : (string) ($row['created_at'] ?? '');
        $row['image_src'] = ! empty($row['image_url'])
            ? base_url($serveBase . '/' . (int) $row['id']) : null;
        return $row;
    }
}
