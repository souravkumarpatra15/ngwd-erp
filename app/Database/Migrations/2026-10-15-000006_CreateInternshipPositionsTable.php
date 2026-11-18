<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateInternshipPositionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'program_id'             => ['type' => 'INT', 'unsigned' => true],
            'title'                  => ['type' => 'VARCHAR', 'constraint' => 200],
            'description'            => ['type' => 'TEXT', 'null' => true],
            'required_skills'        => ['type' => 'TEXT', 'null' => true],
            'education_requirement'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'experience_years'       => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1, 'null' => true, 'default' => 0],
            'stipend_category'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'working_hours_per_week' => ['type' => 'INT'],
            'location'               => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'status'                 => ['type' => 'ENUM', 'constraint' => ['open', 'closed', 'filled'], 'default' => 'open'],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('internship_positions');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_internship_positions_program_id ON internship_positions(program_id)');
        $this->db->query('CREATE INDEX idx_internship_positions_status ON internship_positions(status)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('internship_positions', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}