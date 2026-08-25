<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPmsTaskCollaboration extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('task_comments')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'task_id' => ['type' => 'INT', 'unsigned' => true],
                'user_id' => ['type' => 'INT', 'unsigned' => true],
                'body' => ['type' => 'TEXT'],
                'is_internal' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('task_id');
            $this->forge->addKey('user_id');
            $this->forge->createTable('task_comments', true);
        }

        if (! $this->db->tableExists('task_subtasks')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'task_id' => ['type' => 'INT', 'unsigned' => true],
                'title' => ['type' => 'VARCHAR', 'constraint' => 255],
                'is_completed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'sort_order' => ['type' => 'INT', 'default' => 0],
                'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('task_id');
            $this->forge->createTable('task_subtasks', true);
        }

        if (! $this->db->tableExists('task_activities')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'task_id' => ['type' => 'INT', 'unsigned' => true],
                'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'action' => ['type' => 'VARCHAR', 'constraint' => 100],
                'old_value' => ['type' => 'TEXT', 'null' => true],
                'new_value' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('task_id');
            $this->forge->addKey('user_id');
            $this->forge->createTable('task_activities', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('task_activities', true);
        $this->forge->dropTable('task_subtasks', true);
        $this->forge->dropTable('task_comments', true);
    }
}
