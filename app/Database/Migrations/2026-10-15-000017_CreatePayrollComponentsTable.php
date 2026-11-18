<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePayrollComponentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'component_name'         => ['type' => 'VARCHAR', 'constraint' => 100],  // Basic, HRA, Conveyance, etc.
            'component_type'         => ['type' => 'ENUM', 'constraint' => ['earning', 'deduction'], 'default' => 'earning'],
            'is_statutory'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'configurable_rate'      => ['type' => 'DECIMAL', 'precision' => 5, 'scale' => 2, 'default' => 0],
            'default_rate'           => ['type' => 'DECIMAL', 'precision' => 5, 'scale' => 2, 'default' => 0],
            'applicable_threshold'   => ['type' => 'DECIMAL', 'precision' => 10, 'scale' => 2, 'null' => true],
            'description'            => ['type' => 'TEXT', 'null' => true],
            'sort_order'             => ['type' => 'INT', 'default' => 0],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payroll_components');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('payroll_components', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}