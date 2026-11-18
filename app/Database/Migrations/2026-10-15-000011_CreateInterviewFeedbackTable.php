<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateInterviewFeedbackTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'interview_id'           => ['type' => 'INT', 'unsigned' => true],
            'reviewer_id'            => ['type' => 'INT', 'unsigned' => true],
            'reviewer_name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'technical_knowledge'    => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'problem_solving'        => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'communication'          => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'learning_attitude'      => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'logical_thinking'       => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'availability'           => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'overall_score'          => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'strengths'              => ['type' => 'TEXT', 'null' => true],
            'improvement_areas'      => ['type' => 'TEXT', 'null' => true],
            'recommendation'         => ['type' => 'ENUM', 'constraint' => ['strongly_recommended', 'recommended', 'hold', 'not_recommended']],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('interview_feedback');
        // Add indexes after table creation using db query
        $this->db->query('CREATE INDEX idx_interview_feedback_interview_id ON interview_feedback(interview_id)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('interview_feedback', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}