<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'name', 'email', 'password', 'role', 'client_id', 'client_role',
        'avatar', 'is_active', 'invited_at', 'last_login', 'remember_token',
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

    /** Active client users for one client organization. */
    public function clientUsers(int $clientId, bool $activeOnly = true): array
    {
        $query = $this->where('role', 'client')->where('client_id', $clientId);
        if ($activeOnly) $query->where('is_active', 1);
        return $query->orderBy('name', 'ASC')->findAll();
    }

    // ── Lookups ────────────────────────────────────────────────

    /** Find a user by email (case-insensitive) */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /** Find the first portal user linked to a client (legacy-compatible helper). */
    public function findByClientId(int $clientId): ?array
    {
        return $this->where('client_id', $clientId)->where('role', 'client')->first();
    }

    /** Find all portal users linked to a client. */
    public function findAllByClientId(int $clientId): array
    {
        return $this->clientUsers($clientId, false);
    }

    // ── Auth helpers ───────────────────────────────────────────

    /** Record last login timestamp */
    public function touchLogin(int $userId): void
    {
        $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /** Create or update the legacy primary client portal user. */
    public function syncClientUser(int $clientId, string $name, string $email, string $password = null): int
    {
        $existing = $this->findByClientId($clientId);

        if ($existing) {
            $data = ['name' => $name, 'email' => strtolower(trim($email)), 'client_role' => $existing['client_role'] ?: 'owner'];
            if ($password) $data['password'] = password_hash($password, PASSWORD_BCRYPT);
            $this->update($existing['id'], $data);
            return $existing['id'];
        }

        return $this->insert([
            'name'       => $name,
            'email'      => strtolower(trim($email)),
            'password'   => password_hash($password ?? bin2hex(random_bytes(8)), PASSWORD_BCRYPT),
            'role'       => 'client',
            'client_id'  => $clientId,
            'client_role'=> 'owner',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Create an additional client user without replacing another user. */
    public function createClientUser(int $clientId, string $name, string $email, string $password, string $clientRole = 'member'): int
    {
        $email = strtolower(trim($email));
        if ($this->where('email', $email)->first()) {
            throw new \RuntimeException('Email address is already registered.');
        }

        $this->insert([
            'name'        => trim($name),
            'email'       => $email,
            'password'    => password_hash($password, PASSWORD_DEFAULT),
            'role'        => 'client',
            'client_id'   => $clientId,
            'client_role' => $clientRole,
            'is_active'   => 1,
            'invited_at'  => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->getInsertID();
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
