<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateOnboardingChecklistsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employee_id'            => ['type' => 'INT', 'unsigned' => true],
            'checklist_item'         => ['type' => 'VARCHAR', 'constraint' => 200],
            'completed'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'completed_at'           => ['type' => 'DATETIME', 'null' => true],
            'completed_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'order_index'            => ['type' => 'INT', 'default' => 0],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('onboarding_checklists');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_onboarding_checklists_employee_id ON onboarding_checklists(employee_id)');
        $this->db->query('CREATE INDEX idx_onboarding_checklists_order_index ON onboarding_checklists(order_index)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('onboarding_checklists', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}