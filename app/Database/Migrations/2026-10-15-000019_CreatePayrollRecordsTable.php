<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePayrollRecordsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'payroll_period_id'      => ['type' => 'INT', 'unsigned' => true],
            'employee_id'            => ['type' => 'INT', 'unsigned' => true],
            ' gross_salary'          => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' net_salary'           => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' gross_earnings'        => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' net_deductions'        => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' payment_date'          => ['type' => 'DATE'],
            ' payment_method'        => ['type' => 'VARCHAR', 'constraint' => 50],  // bank_transfer, check, cash, etc.
            ' payment_reference'     => ['type' => 'VARCHAR', 'constraint' => 100],
            ' status'                => ['type' => 'ENUM', 'constraint' => ['pending', 'processing', 'paid', 'failed'], 'default' => 'pending'],
            'approved_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_at'            => ['type' => 'DATETIME', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_records');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_payroll_records_employee_id ON payroll_records(employee_id)');
        $this->db->query('CREATE INDEX idx_payroll_records_payroll_period_id ON payroll_records(payroll_period_id)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('payroll_records', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}