<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateOnboardingDocumentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employee_id'            => ['type' => 'INT', 'unsigned' => true],
            'document_name'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'file_path'              => ['type' => 'VARCHAR', 'constraint' => 500],
            'file_type'              => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'document_category'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'uploaded_at'            => ['type' => 'DATETIME', 'null' => true],
            'uploaded_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status'                 => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('onboarding_documents');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_onboarding_documents_employee_id ON onboarding_documents(employee_id)');
        $this->db->query('CREATE INDEX idx_onboarding_documents_status ON onboarding_documents(status)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('onboarding_documents', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}