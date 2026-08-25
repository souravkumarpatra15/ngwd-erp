<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeliverableApprovals extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('deliverable_approvals')) return;

        $this->forge->addField([
            'id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'deliverable_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'project_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'user_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'action' => ['type'=>'VARCHAR','constraint'=>40],
            'comment' => ['type'=>'TEXT','null'=>true],
            'created_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['deliverable_id','created_at']);
        $this->forge->addKey(['project_id','created_at']);
        $this->forge->createTable('deliverable_approvals');
    }

    public function down()
    {
        if ($this->db->tableExists('deliverable_approvals')) $this->forge->dropTable('deliverable_approvals', true);
    }
}
