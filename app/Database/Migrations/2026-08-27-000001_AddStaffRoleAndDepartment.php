<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Adds a generic 'staff' login tier for internal employees who aren't
 * admin/manager, plus a `department` label (developer, designer, qa, hr,
 * support, sales_finance) used purely for permission decisions — never
 * for login access itself, so a typo or new department name added later
 * can never lock someone out the way an unwidened role ENUM could.
 * Run: php spark migrate
 */
class AddStaffRoleAndDepartment extends Migration
{
    public function up()
    {
        // Widen the login-tier enum: superadmin, admin, manager, client -> + staff
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','manager','staff','client') NOT NULL DEFAULT 'client'");

        $this->forge->addColumn('users', [
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'role',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'department');
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','manager','client') NOT NULL DEFAULT 'client'");
    }
}
