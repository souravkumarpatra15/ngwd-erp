<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInterviewsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'application_id'       => ['type' => 'INT', 'unsigned' => true],
            'interview_round'      => ['type' => 'INT', 'default' => 1],
            'interviewer_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'interviewer_name'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],

            'interview_date'       => ['type' => 'DATE'],
            'start_time'           => ['type' => 'TIME'],
            'end_time'             => ['type' => 'TIME'],
            'timezone'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => 'UTC'],

            'interview_type'       => [
                'type'       => 'ENUM',
                'constraint' => ['technical', 'hr', 'culture_add', 'technical_pair', 'panel'],
                'default'    => 'technical'
            ],

            'meeting_link'         => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'instructions'         => ['type' => 'TEXT', 'null' => true],
            'internal_notes'       => ['type' => 'TEXT', 'null' => true],

            'interview_status'     => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'completed', 'cancelled', 'rescheduled'],
                'default'    => 'scheduled'
            ],

            'evaluation_score'     => ['type' => 'DECIMAL', 'precision' => 3, 'scale' => 1, 'null' => true],

            'final_recommendation' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'strongly_recommended',
                    'recommended',
                    'hold',
                    'not_recommended'
                ],
                'null'       => true
            ],

            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('interviews');

        // Indexes
        $this->db->query('CREATE INDEX idx_interviews_application_id ON interviews(application_id)');
        $this->db->query('CREATE INDEX idx_interviews_interview_date ON interviews(interview_date)');
        $this->db->query('CREATE INDEX idx_interviews_interview_status ON interviews(interview_status)');
    }

    public function down()
    {
        $this->forge->dropTable('interviews', true);
    }
}