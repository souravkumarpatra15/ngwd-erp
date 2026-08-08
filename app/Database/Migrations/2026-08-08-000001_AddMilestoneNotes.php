<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Adds milestone_notes — a lightweight comment thread on each milestone
 * so the agency and the client can ask/answer questions without email.
 * Mirrors the existing ticket_replies pattern (user_id + is_admin flag).
 * Run: php spark migrate
 */
class AddMilestoneNotes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'milestone_id'=> ['type' => 'INT', 'unsigned' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true],
            'message'     => ['type' => 'TEXT'],
            'is_admin'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('milestone_id');
        $this->forge->createTable('milestone_notes');
    }

    public function down()
    {
        $this->forge->dropTable('milestone_notes', true);
    }
}
