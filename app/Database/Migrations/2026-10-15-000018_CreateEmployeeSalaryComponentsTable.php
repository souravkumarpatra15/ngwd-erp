<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateEmployeeSalaryComponentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employee_id'            => ['type' => 'INT', 'unsigned' => true],
            'payroll_period_id'      => ['type' => 'INT', 'unsigned' => true],
            'component_id'           => ['type' => 'INT', 'unsigned' => true],
            'value'                  => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            'effective_from'         => ['type' => 'DATE'],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('employee_salary_components');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_employee_salary_components_employee_id ON employee_salary_components(employee_id)');
        $this->db->query('CREATE INDEX idx_employee_salary_components_payroll_period_id ON employee_salary_components(payroll_period_id)');
        $this->db->query('CREATE INDEX idx_employee_salary_components_component_id ON employee_salary_components(component_id)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('employee_salary_components', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}