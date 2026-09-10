<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Project-wise access for client-portal team members. Owner/Manager
 * always see every project for their org (unchanged). Member/Viewer
 * accounts are restricted to only the projects an Owner/Manager has
 * explicitly assigned them to here.
 */
class CreateClientProjectMembers extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('client_project_members')) return;

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'project_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'project_id']);
        $this->forge->addKey('project_id');
        $this->forge->createTable('client_project_members');
    }

    public function down()
    {
        if ($this->db->tableExists('client_project_members')) {
            $this->forge->dropTable('client_project_members', true);
        }
    }
}
