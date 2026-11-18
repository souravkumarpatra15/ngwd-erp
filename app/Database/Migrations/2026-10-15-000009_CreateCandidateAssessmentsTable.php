<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCandidateAssessmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'application_id'      => ['type' => 'INT', 'unsigned' => true],

            'technical_knowledge' => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'problem_solving'     => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'communication'       => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'learning_attitude'   => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'logical_thinking'    => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'availability'        => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],
            'overall_score'       => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1],

            'strengths'           => ['type' => 'TEXT', 'null' => true],
            'improvement_areas'   => ['type' => 'TEXT', 'null' => true],

            'recommendation'      => [
                'type'       => 'ENUM',
                'constraint' => [
                    'strongly_recommended',
                    'recommended',
                    'hold',
                    'not_recommended'
                ],
            ],

            'assessed_by'         => ['type' => 'INT', 'unsigned' => true],
            'assessed_at'         => ['type' => 'DATETIME', 'null' => true],

            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('candidate_assessments');

        $this->db->query('CREATE INDEX idx_candidate_assessments_application_id ON candidate_assessments(application_id)');
        $this->db->query('CREATE INDEX idx_candidate_assessments_recommendation ON candidate_assessments(recommendation)');
    }

    public function down()
    {
        $this->forge->dropTable('candidate_assessments', true);
    }
}