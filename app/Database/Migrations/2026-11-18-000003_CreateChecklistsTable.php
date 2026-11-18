<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateChecklistsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'task_id'                => ['type' => 'INT', 'unsigned' => true],
            'item_text'              => ['type' => 'VARCHAR', 'constraint' => 200],
            'completed'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'completed_at'           => ['type' => 'DATETIME', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('task_id');
        $this->forge->addKey(['task_id', 'completed']);
        $this->forge->createTable('checklists');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('checklists', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}