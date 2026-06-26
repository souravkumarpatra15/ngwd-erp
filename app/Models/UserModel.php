<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'name', 'email', 'password', 'role', 'client_id',
        'avatar', 'is_active', 'last_login', 'remember_token',
    ];

    // ── Scopes ─────────────────────────────────────────────────

    /** All non-client admin-side users */
    public function admins()
    {
        return $this->whereIn('role', ['superadmin', 'admin', 'manager']);
    }

    /** Only active users */
    public function active()
    {
        return $this->where('is_active', 1);
    }

    /** Client portal users linked to a client record */
    public function clients()
    {
        return $this->where('role', 'client')->whereNotNull('client_id');
    }

    // ── Lookups ────────────────────────────────────────────────

    /** Find a user by email (case-insensitive) */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /** Find the portal user linked to a client_id */
    public function findByClientId(int $clientId): ?array
    {
        return $this->where('client_id', $clientId)->where('role', 'client')->first();
    }

    // ── Auth helpers ───────────────────────────────────────────

    /** Record last login timestamp */
    public function touchLogin(int $userId): void
    {
        $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /** Create or update a client portal user when a client is added/updated */
    public function syncClientUser(int $clientId, string $name, string $email, string $password = null): int
    {
        $existing = $this->findByClientId($clientId);

        if ($existing) {
            $data = ['name' => $name, 'email' => $email];
            if ($password) $data['password'] = password_hash($password, PASSWORD_BCRYPT);
            $this->update($existing['id'], $data);
            return $existing['id'];
        }

        return $this->insert([
            'name'       => $name,
            'email'      => $email,
            'password'   => password_hash($password ?? bin2hex(random_bytes(8)), PASSWORD_BCRYPT),
            'role'       => 'client',
            'client_id'  => $clientId,
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Counts ─────────────────────────────────────────────────

    public function countAdmins(): int
    {
        return $this->admins()->countAllResults(false);
    }

    public function countActiveClients(): int
    {
        return $this->clients()->active()->countAllResults(false);
    }
}
