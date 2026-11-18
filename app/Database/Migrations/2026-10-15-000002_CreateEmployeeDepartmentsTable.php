<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateEmployeeDepartmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'code'             => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'head_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'description'      => ['type' => 'TEXT', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'sort_order'       => ['type' => 'INT', 'default' => 0],
            'created_by'       => ['type' => 'INT', 'unsigned' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('employee_departments');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_employee_departments_client_id ON employee_departments(client_id)');
        $this->db->query('CREATE INDEX idx_employee_departments_head_id ON employee_departments(head_id)');
        $this->db->query('CREATE INDEX idx_employee_departments_status ON employee_departments(status)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('employee_departments', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}