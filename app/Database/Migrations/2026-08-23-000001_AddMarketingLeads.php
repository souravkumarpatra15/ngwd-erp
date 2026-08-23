<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Marketing Leads module — leads a client's ad campaigns (Facebook/Instagram/
 * Google Ads etc.) generate for THEIR own business, distinct from the
 * agency's own sales `leads` table. Admin adds these manually or via CSV
 * upload against a client + project/campaign; the client views them in the
 * portal and can call/WhatsApp/email their prospects directly.
 * Run: php spark migrate
 */
class AddMarketingLeads extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'     => ['type' => 'INT', 'unsigned' => true],
            'project_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'campaign_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'platform'      => ['type' => 'ENUM', 'constraint' => ['facebook', 'instagram', 'google_ads', 'other'], 'default' => 'facebook'],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'whatsapp'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'city'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'requirement'   => ['type' => 'TEXT', 'null' => true],
            'notes'         => ['type' => 'TEXT', 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['new', 'contacted', 'interested', 'not_interested', 'converted', 'junk'], 'default' => 'new'],
            'lead_date'     => ['type' => 'DATE', 'null' => true],
            'created_by'    => ['type' => 'INT', 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('client_id');
        $this->forge->addKey('project_id');
        $this->forge->addKey('status');
        $this->forge->addKey('lead_date');
        $this->forge->createTable('marketing_leads');
    }

    public function down()
    {
        $this->forge->dropTable('marketing_leads', true);
    }
}
