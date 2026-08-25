<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Deliverable lifecycle for milestone-based client delivery. */
class AddPmsDeliverables extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('deliverables')) return;

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'project_id' => ['type' => 'INT', 'unsigned' => true],
            'milestone_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 200],
            'description' => ['type' => 'TEXT', 'null' => true],
            'owner_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'due_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft','in_progress','submitted','under_review','changes_requested','approved','rejected'], 'default' => 'draft'],
            'file_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'version' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => '1.0'],
            'submitted_at' => ['type' => 'DATETIME', 'null' => true],
            'reviewed_at' => ['type' => 'DATETIME', 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'approved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('project_id');
        $this->forge->addKey('milestone_id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('status');
        $this->forge->addKey('due_date');
        $this->forge->createTable('deliverables');
    }

    public function down()
    {
        $this->forge->dropTable('deliverables', true);
    }
}
