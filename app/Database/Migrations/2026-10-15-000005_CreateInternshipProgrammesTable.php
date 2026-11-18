<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateInternshipProgrammesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'                => ['type' => 'VARCHAR', 'constraint' => 200],
            'description'          => ['type' => 'LONGTEXT', 'null' => true],
            'duration_months'      => ['type' => 'INT'],
            'start_date'           => ['type' => 'DATE'],
            'end_date'             => ['type' => 'DATE'],
            'mode'                 => ['type' => 'ENUM', 'constraint' => ['remote', 'hybrid', 'office'], 'default' => 'remote'],
            'weekly_hours'         => ['type' => 'INT'],
            'compensation_category'=> ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'eligibility_criteria' => ['type' => 'TEXT', 'null' => true],
            'batch_size_min'       => ['type' => 'INT', 'default' => 3],
            'batch_size_max'       => ['type' => 'INT', 'default' => 5],
            'application_deadline' => ['type' => 'DATE'],
            'status'               => ['type' => 'ENUM', 'constraint' => ['draft', 'active', 'closed', 'completed'], 'default' => 'active'],
            'public_application_url'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'           => ['type' => 'INT', 'unsigned' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('internship_programs');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_internship_programs_status ON internship_programs(status)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('internship_programs', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}