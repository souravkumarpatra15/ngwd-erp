<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateInternshipApplicationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'program_id'               => ['type' => 'INT', 'unsigned' => true],
            'applicant_name'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'applicant_email'          => ['type' => 'VARCHAR', 'constraint' => 150],
            'applicant_mobile'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'applicant_city_state'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'education_qualification'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'college_university'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'graduation_year'          => ['type' => 'Year'],
            'preferred_role'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'technical_skills'         => ['type' => 'TEXT', 'null' => true],
            'github_profile'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'portfolio_url'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'resume_file_path'         => ['type' => 'VARCHAR', 'constraint' => 500],
            'profile_photograph'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'laptop_available'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'internet_available'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'weekly_availability'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'motivation_statement'     => ['type' => 'LONGTEXT', 'null' => true],
            'consent_privacy'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'applied_at'               => ['type' => 'DATETIME', 'null' => true],
            'status'                   => ['type' => 'ENUM', 'constraint' => ['applied', 'under_review', 'shortlisted', 'assessment_scheduled', 'assessment_completed', 'interview_scheduled', 'interview_completed', 'selected', 'offer_sent', 'offer_accepted', 'onboarding', 'joined', 'rejected', 'withdrawn', 'on_hold'], 'default' => 'applied'],
            'reference_number'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'source'                   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'notes'                    => ['type' => 'TEXT', 'null' => true],
            'created_at'               => ['type' => 'DATETIME', 'null' => true],
            'updated_at'               => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('reference_number');
        $this->forge->createTable('internship_applications');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_internship_applications_program_id ON internship_applications(program_id)');
        $this->db->query('CREATE INDEX idx_internship_applications_status ON internship_applications(status)');
        $this->db->query('CREATE INDEX idx_internship_applications_applicant_email ON internship_applications(applicant_email)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('internship_applications', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}