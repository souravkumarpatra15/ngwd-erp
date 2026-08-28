<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/** Run: php spark migrate */
class AddModulePermissionsAndTaskExtras extends Migration
{
    public function up()
    {
        // Per-user, per-module CRUD overrides. Works for both internal
        // staff (developer/designer/qa/hr/support) and client-portal team
        // members — same users table, same concept: does this specific
        // person have view/create/edit/delete on this specific module?
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true],
            'module'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'can_view'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_create' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_edit'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_delete' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'module']);
        $this->forge->createTable('user_module_permissions');

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'task_id'       => ['type' => 'INT', 'unsigned' => true],
            'filename'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'size'          => ['type' => 'INT', 'unsigned' => true],
            'is_image'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'uploaded_by'   => ['type' => 'INT', 'unsigned' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('task_id');
        $this->forge->createTable('task_attachments');

        $this->forge->addColumn('tasks', [
            'is_issue' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'priority'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tasks', 'is_issue');
        $this->forge->dropTable('task_attachments', true);
        $this->forge->dropTable('user_module_permissions', true);
    }
}
