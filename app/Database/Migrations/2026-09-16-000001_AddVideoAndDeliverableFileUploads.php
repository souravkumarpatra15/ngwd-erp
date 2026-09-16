<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds video flag to task attachments + deliverable file uploads (images, videos, pdf, zip, doc, excel). */
class AddVideoAndDeliverableFileUploads extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('task_attachments') && !$this->db->fieldExists('is_video', 'task_attachments')) {
            $this->forge->addColumn('task_attachments', [
                'is_video' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'is_image'],
            ]);
        }

        if (!$this->db->tableExists('deliverable_files')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'deliverable_id'=> ['type' => 'INT', 'unsigned' => true],
                'filename'      => ['type' => 'VARCHAR', 'constraint' => 255],
                'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
                'mime_type'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'size'          => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'is_image'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'is_video'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'uploaded_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('deliverable_id');
            $this->forge->createTable('deliverable_files');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('deliverable_files')) $this->forge->dropTable('deliverable_files', true);
        if ($this->db->tableExists('task_attachments') && $this->db->fieldExists('is_video', 'task_attachments')) {
            $this->forge->dropColumn('task_attachments', 'is_video');
        }
    }
}
