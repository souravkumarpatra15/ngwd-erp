<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMilestoneDueTime extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('due_time', 'milestones')) {
            $this->forge->addColumn('milestones', [
                'due_time' => ['type' => 'TIME', 'null' => true, 'after' => 'due_date'],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('due_time', 'milestones')) {
            $this->forge->dropColumn('milestones', 'due_time');
        }
    }
}
