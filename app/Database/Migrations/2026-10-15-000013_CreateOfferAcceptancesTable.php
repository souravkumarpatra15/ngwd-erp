<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateOfferAcceptancesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'offer_letter_id'      => ['type' => 'INT', 'unsigned' => true],
            'candidate_name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'candidate_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'accepted_at'          => ['type' => 'DATETIME'],
            'acceptance_type'      => ['type' => 'ENUM', 'constraint' => ['accepted', 'rejected']],
            'response_notes'       => ['type' => 'TEXT', 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('offer_acceptances');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_offer_acceptances_offer_letter_id ON offer_acceptances(offer_letter_id)');
        $this->db->query('CREATE INDEX idx_offer_acceptances_accepted_at ON offer_acceptances(accepted_at)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('offer_acceptances', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}