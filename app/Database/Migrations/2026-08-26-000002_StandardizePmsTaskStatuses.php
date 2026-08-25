<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Standardize PMS task statuses while preserving existing task records. */
class StandardizePmsTaskStatuses extends Migration
{
    public function up()
    {
        // Widen the enum first so all target values are accepted during mapping.
        $this->db->query("ALTER TABLE tasks MODIFY COLUMN status ENUM('todo','in_progress','code_review','qa','client_review','blocked','done','cancelled','review','completed','hold') NOT NULL DEFAULT 'todo'");

        // Preserve the meaning of existing production statuses.
        $this->db->query("UPDATE tasks SET status = 'code_review' WHERE status = 'review'");
        $this->db->query("UPDATE tasks SET status = 'done' WHERE status = 'completed'");
        $this->db->query("UPDATE tasks SET status = 'blocked' WHERE status = 'hold'");

        // Remove legacy values from the enum after data has been normalized.
        $this->db->query("ALTER TABLE tasks MODIFY COLUMN status ENUM('todo','in_progress','code_review','qa','client_review','blocked','done','cancelled') NOT NULL DEFAULT 'todo'");
    }

    public function down()
    {
        // Map the standardized workflow back to the legacy production vocabulary.
        $this->db->query("ALTER TABLE tasks MODIFY COLUMN status ENUM('todo','in_progress','code_review','qa','client_review','blocked','done','cancelled','review','completed','hold') NOT NULL DEFAULT 'todo'");
        $this->db->query("UPDATE tasks SET status = 'review' WHERE status IN ('code_review','qa','client_review')");
        $this->db->query("UPDATE tasks SET status = 'completed' WHERE status = 'done'");
        $this->db->query("UPDATE tasks SET status = 'hold' WHERE status = 'blocked'");
        $this->db->query("ALTER TABLE tasks MODIFY COLUMN status ENUM('todo','in_progress','review','completed','hold') NOT NULL DEFAULT 'todo'");
    }
}
