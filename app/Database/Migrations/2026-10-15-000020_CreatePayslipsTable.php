<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePayslipsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'payroll_record_id'      => ['type' => 'INT', 'unsigned' => true],
            'employee_id'            => ['type' => 'INT', 'unsigned' => true],
            'payroll_period_id'      => ['type' => 'INT', 'unsigned' => true],
            'employee_name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'employee_id_display'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'designation'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'department'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'salary_month'           => ['type' => 'VARCHAR', 'constraint' => 20],  // e.g., 'January 2024'
            'paid_days'              => ['type' => 'INT'],
            ' basic_salary'          => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' hra'                   => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' conveyance'            => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' special_allowance'     => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' other_allowances'      => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' incentives'            => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' bonus'                 => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' gross_earnings'        => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' pf'                    => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' ESI'                   => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' professional_tax'      => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' tds'                   => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' other_deductions'      => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' leave_with_pay'        => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' gross_deductions'      => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' net_salary'            => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' payment_date'          => ['type' => 'DATE'],
            ' payment_method'        => ['type' => 'VARCHAR', 'constraint' => 50],
            ' payment_reference'     => ['type' => 'VARCHAR', 'constraint' => 100],
            ' status'                => ['type' => 'ENUM', 'constraint' => ['draft', 'generated', 'approved', 'paid'], 'default' => 'draft'],
            ' generated_at'           => ['type' => 'DATETIME'],
            ' approved_at'           => ['type' => 'DATETIME', 'null' => true],
            ' created_at'             => ['type' => 'DATETIME', 'null' => true],
            ' updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payslips');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_payslips_employee_id ON payslips(employee_id)');
        $this->db->query('CREATE INDEX idx_payslips_payroll_period_id ON payslips(payroll_period_id)');
        $this->db->query('CREATE INDEX idx_payslips_status ON payslips(status)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('payslips', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}