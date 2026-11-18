<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePayrollPeriodsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'                   => ['type' => 'VARCHAR', 'constraint' => 50],
            'period'                 => ['type' => 'VARCHAR', 'constraint' => 20],  // e.g., 'January 2024'
            'start_date'             => ['type' => 'DATE'],
            'end_date'               => ['type' => 'DATE'],
            'status'                 => ['type' => 'ENUM', 'constraint' => ['draft', 'processing', 'finalized', 'paid'], 'default' => 'draft'],
            'processed_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'processed_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_periods');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('payroll_periods', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}