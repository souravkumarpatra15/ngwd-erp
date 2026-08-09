<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Tasks currently only roll up to a project — there's no way to say
 * "this task/change/issue belongs to milestone 2's delivery." Adding an
 * optional milestone_id so changes, notes, and issues raised around a
 * milestone can be tracked task-wise instead of just dumped at the
 * project level.
 * Run: php spark migrate
 */
class AddTaskMilestoneLink extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tasks', [
            'milestone_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'project_id'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tasks', 'milestone_id');
    }
}
