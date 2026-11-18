<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateOfferLettersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'application_id'        => ['type' => 'INT', 'unsigned' => true],
            'candidate_name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'candidate_address'     => ['type' => 'TEXT', 'null' => true],
            'position'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'program_name'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'duration'              => ['type' => 'VARCHAR', 'constraint' => 100],
            'start_date'            => ['type' => 'DATE'],
            'end_date'              => ['type' => 'DATE'],
            'work_mode'             => ['type' => 'ENUM', 'constraint' => ['remote', 'hybrid', 'office'], 'default' => 'remote'],
            'weekly_commitment'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'compensation_disclosure'=> ['type' => 'TEXT', 'null' => true],
            'learning_objectives'   => ['type' => 'LONGTEXT', 'null' => true],
            'terms_conditions'      => ['type' => 'LONGTEXT', 'null' => true],
            'authorized_signatory'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'company_branding'      => ['type' => 'TEXT', 'null' => true],
            'offer_reference'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'issue_date'            => ['type' => 'DATE'],
            'acceptance_deadline'  => ['type' => 'DATE'],
            'status'                => ['type' => 'ENUM', 'constraint' => ['draft', 'issued', 'accepted', 'rejected'], 'default' => 'draft'],
            'pdf_path'              => ['type' => 'VARCHAR', 'constraint' => 500],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('offer_letters');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_offer_letters_application_id ON offer_letters(application_id)');
        $this->db->query('CREATE INDEX idx_offer_letters_status ON offer_letters(status)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('offer_letters', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}