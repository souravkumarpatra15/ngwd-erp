<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * PMS membership foundation.
 *
 * Keeps the existing users.client_id relationship for backward compatibility,
 * while adding a client-specific role and project-level team membership.
 * No existing production rows are deleted or rewritten.
 */
class AddPmsMemberships extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('client_role', 'users')) {
            $this->forge->addColumn('users', [
                'client_role' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'after' => 'client_id',
                ],
            ]);
        }

        if (!$this->db->fieldExists('invited_at', 'users')) {
            $this->forge->addColumn('users', [
                'invited_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'is_active',
                ],
            ]);
        }

        if (!$this->db->tableExists('project_members')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'project_id' => ['type' => 'INT', 'unsigned' => true],
                'user_id' => ['type' => 'INT', 'unsigned' => true],
                'project_role' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'member'],
                'access_level' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'edit'],
                'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'joined_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['project_id', 'user_id']);
            $this->forge->addKey('project_id');
            $this->forge->addKey('user_id');
            $this->forge->addKey('is_active');
            $this->forge->createTable('project_members');
        }

        // Existing client users remain valid. Give legacy users a sensible role.
        $this->db->query("UPDATE users SET client_role = 'owner' WHERE role = 'client' AND client_role IS NULL AND client_id IS NOT NULL");
    }

    public function down()
    {
        if ($this->db->tableExists('project_members')) {
            $this->forge->dropTable('project_members', true);
        }
        if ($this->db->fieldExists('invited_at', 'users')) {
            $this->forge->dropColumn('users', 'invited_at');
        }
        if ($this->db->fieldExists('client_role', 'users')) {
            $this->forge->dropColumn('users', 'client_role');
        }
    }
}
