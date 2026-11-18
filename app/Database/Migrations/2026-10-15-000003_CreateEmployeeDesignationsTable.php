<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateEmployeeDesignationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'title'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'code'               => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'department_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'description'        => ['type' => 'TEXT', 'null' => true],
            'reporting_manager_title' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'employment_category' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'status'             => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'sort_order'         => ['type' => 'INT', 'default' => 0],
            'created_by'         => ['type' => 'INT', 'unsigned' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('employee_designations');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_employee_designations_client_id ON employee_designations(client_id)');
        $this->db->query('CREATE INDEX idx_employee_designations_department_id ON employee_designations(department_id)');
        $this->db->query('CREATE INDEX idx_employee_designations_status ON employee_designations(status)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('employee_designations', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}