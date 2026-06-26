<?php
namespace App\Models;

use CodeIgniter\Model;

class ActivityModel extends Model
{
    protected $table        = 'activities';
    protected $primaryKey   = 'id';
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $allowedFields = [
        'user_id', 'module', 'module_id', 'action', 'description', 'ip_address',
    ];

    // ── Recent activity feed (admin dashboard) ─────────────────
    public function getRecent(int $limit = 20): array
    {
        return $this->db->table('activities')
            ->select('activities.*, users.name as user_name, users.avatar as user_avatar, users.role as user_role')
            ->join('users', 'users.id = activities.user_id', 'left')
            ->orderBy('activities.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    // ── Activity for a specific module record ──────────────────
    public function getForRecord(string $module, int $moduleId): array
    {
        return $this->db->table('activities')
            ->select('activities.*, users.name as user_name, users.role as user_role')
            ->join('users', 'users.id = activities.user_id', 'left')
            ->where('activities.module', $module)
            ->where('activities.module_id', $moduleId)
            ->orderBy('activities.created_at', 'DESC')
            ->get()->getResultArray();
    }

    // ── Activity by a specific user ────────────────────────────
    public function getForUser(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    // ── Log shorthand (static-style helper) ───────────────────
    public static function log(int $userId, string $module, int $moduleId, string $action, string $description = '', string $ip = ''): void
    {
        (new self())->insert([
            'user_id'     => $userId,
            'module'      => $module,
            'module_id'   => $moduleId,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $ip ?: \Config\Services::request()->getIPAddress(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
