<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Adds password_resets table and expands users.role enum
 * to support multiple admin roles.
 * Run: php spark migrate
 */
class AddPasswordResetsAndAdminRoles extends Migration
{
    public function up()
    {
        // ── password_resets ────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 150],
            'token'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
            'used'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('token');
        $this->forge->addKey('email');
        $this->forge->createTable('password_resets');

        // ── Expand users.role enum ─────────────────────────────
        // Original: superadmin, client
        // New:      superadmin, admin, manager, client
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','manager','client') NOT NULL DEFAULT 'client'");
    }

    public function down()
    {
        $this->forge->dropTable('password_resets', true);
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','client') NOT NULL DEFAULT 'client'");
    }
}
