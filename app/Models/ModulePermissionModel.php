<?php
namespace App\Models;
use CodeIgniter\Model;

class ModulePermissionModel extends Model
{
    protected $table = 'user_module_permissions';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'module', 'can_view', 'can_create', 'can_edit', 'can_delete'];

    public function forUser(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();
        return array_column($rows, null, 'module');
    }

    public function upsert(int $userId, string $module, array $perms): void
    {
        $existing = $this->where('user_id', $userId)->where('module', $module)->first();
        $data = [
            'user_id'    => $userId,
            'module'     => $module,
            'can_view'   => (int) ($perms['can_view'] ?? 0),
            'can_create' => (int) ($perms['can_create'] ?? 0),
            'can_edit'   => (int) ($perms['can_edit'] ?? 0),
            'can_delete' => (int) ($perms['can_delete'] ?? 0),
        ];
        if ($existing) $this->update($existing['id'], $data);
        else $this->insert($data);
    }
}
