<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateSalaryPaymentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'payroll_record_id'      => ['type' => 'INT', 'unsigned' => true],
            'employee_id'            => ['type' => 'INT', 'unsigned' => true],
            'payment_date'           => ['type' => 'DATE'],
            'payment_method'         => ['type' => 'VARCHAR', 'constraint' => 50],  // bank_transfer, check, cash, etc.
            'payment_reference'      => ['type' => 'VARCHAR', 'constraint' => 100],
            ' amount'                => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ' status'                => ['type' => 'ENUM', 'constraint' => ['pending', 'processing', 'completed', 'failed', 'refunded'], 'default' => 'pending'],
            ' transaction_id'        => ['type' => 'VARCHAR', 'constraint' => 100],
            ' created_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('salary_payments');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_salary_payments_employee_id ON salary_payments(employee_id)');
        $this->db->query('CREATE INDEX idx_salary_payments_payroll_record_id ON salary_payments(payroll_record_id)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('salary_payments', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}