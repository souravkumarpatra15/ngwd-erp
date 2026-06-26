<?php
namespace App\Models;
use CodeIgniter\Model;
class SettingModel extends Model {
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['key','value','group'];
    protected static $cache = [];
    public function getAllSettings(): array {
        if (!empty(self::$cache)) return self::$cache;
        foreach ($this->findAll() as $row) self::$cache[$row['key']] = $row['value'];
        return self::$cache;
    }
    public function getSetting(string $key, $default = null) {
        return $this->getAllSettings()[$key] ?? $default;
    }
    public function saveSetting(string $key, $value): bool {
        $existing = $this->where('key', $key)->first();
        if ($existing) return $this->update($existing['id'], ['value' => $value]);
        return (bool) $this->insert(['key' => $key, 'value' => $value]);
    }
    public function saveGroup(string $group, array $data): void {
        foreach ($data as $key => $value) $this->saveSetting($key, $value);
        self::$cache = [];
    }
}
