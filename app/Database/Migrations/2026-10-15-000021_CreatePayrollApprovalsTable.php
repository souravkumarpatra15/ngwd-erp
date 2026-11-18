<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePayrollApprovalsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'payroll_record_id'      => ['type' => 'INT', 'unsigned' => true],
            'employee_id'            => ['type' => 'INT', 'unsigned' => true],
            'approver_id'            => ['type' => 'INT', 'unsigned' => true],
            'approver_name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'approved_at'            => ['type' => 'DATETIME'],
            'approval_type'          => ['type' => 'ENUM', 'constraint' => ['initial', 'final'], 'default' => 'initial'],
            'approval_status'        => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'approval_notes'         => ['type' => 'TEXT', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_approvals');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_payroll_approvals_payroll_record_id ON payroll_approvals(payroll_record_id)');
        $this->db->query('CREATE INDEX idx_payroll_approvals_employee_id ON payroll_approvals(employee_id)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('payroll_approvals', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}