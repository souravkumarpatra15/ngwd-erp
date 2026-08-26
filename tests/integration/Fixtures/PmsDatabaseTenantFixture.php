<?php

namespace Tests\Integration\Fixtures;

use App\Models\ProjectMemberModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Creates isolated PMS identities against the actual CI database schema.
 *
 * No fixed IDs are assumed. Records are cleaned up by the caller so this
 * fixture can run safely against a shared CI database.
 */
final class PmsDatabaseTenantFixture
{
    private BaseConnection $db;
    private array $ids = ['users' => [], 'projects' => []];

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }

    public function create(): array
    {
        $suffix = bin2hex(random_bytes(6));

        $clientA = 900000 + random_int(1, 9999);
        $clientB = $clientA + 1;

        $userA = $this->insertUser("PMS Tenant A Developer", "pms-a-{$suffix}@example.test", $clientA);
        $userB = $this->insertUser("PMS Tenant B Developer", "pms-b-{$suffix}@example.test", $clientB);
        $projectA = $this->insertProject("PMS-A-{$suffix}", $clientA, $userA);
        $projectB = $this->insertProject("PMS-B-{$suffix}", $clientB, $userB);

        (new ProjectMemberModel())->addMember($projectA, $userA, 'developer', 'edit');
        (new ProjectMemberModel())->addMember($projectB, $userB, 'developer', 'edit');

        $this->ids = [
            'client_a' => $clientA,
            'client_b' => $clientB,
            'developer_a' => $userA,
            'developer_b' => $userB,
            'project_a' => $projectA,
            'project_b' => $projectB,
        ];

        return $this->ids;
    }

    public function cleanup(): void
    {
        if ($this->ids === []) {
            return;
        }

        $this->db->table('project_members')->whereIn('project_id', [$this->ids['project_a'], $this->ids['project_b']])->delete();
        $this->db->table('projects')->whereIn('id', [$this->ids['project_a'], $this->ids['project_b']])->delete();
        $this->db->table('users')->whereIn('id', [$this->ids['developer_a'], $this->ids['developer_b']])->delete();
        $this->ids = [];
    }

    private function insertUser(string $name, string $email, int $clientId): int
    {
        $this->db->table('users')->insert([
            'name' => $name,
            'email' => $email,
            'password' => password_hash('PMS-test-only', PASSWORD_DEFAULT),
            'role' => 'client',
            'client_id' => $clientId,
            'client_role' => 'member',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    private function insertProject(string $number, int $clientId, int $createdBy): int
    {
        $this->db->table('projects')->insert([
            'project_number' => $number,
            'client_id' => $clientId,
            'name' => "Tenant Isolation {$number}",
            'type' => 'software',
            'description' => 'Automated PMS tenant-isolation test fixture.',
            'budget' => 0,
            'advance_paid' => 0,
            'total_paid' => 0,
            'status' => 'development',
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }
}
