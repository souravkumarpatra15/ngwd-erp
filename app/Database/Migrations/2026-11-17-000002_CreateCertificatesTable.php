<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateCertificatesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employee_id'            => ['type' => 'INT', 'unsigned' => true],
            'certificate_number'     => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'intern_name'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'intern_designation'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'department'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'internship_start_date'  => ['type' => 'DATE'],
            'internship_end_date'    => ['type' => 'DATE'],
            'completion_date'        => ['type' => 'DATE'],
            'qr_code_hash'           => ['type' => 'VARCHAR', 'constraint' => 64], // SHA256 hex
            'verification_url_hash'  => ['type' => 'VARCHAR', 'constraint' => 64], // unique hash for URL
            'status'                 => ['type' => 'ENUM', 'constraint' => ['active', 'revoked', 'expired'], 'default' => 'active'],
            'issued_by'              => ['type' => 'INT', 'unsigned' => true],
            'issued_at'              => ['type' => 'DATETIME'],
            'revoked_at'             => ['type' => 'DATETIME', 'null' => true],
            'revocation_reason'      => ['type' => 'TEXT', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addIndex('certificate_number');
        $this->forge->addIndex('employee_id');
        $this->forge->addIndex('qr_code_hash');
        $this->forge->addIndex('verification_url_hash');
        $this->forge->createTable('certificates');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('certificates', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}