<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateAttendanceTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employee_id'          => ['type' => 'INT', 'unsigned' => true],
            'check_in'             => ['type' => 'DATETIME', 'null' => true],
            'check_out'            => ['type' => 'DATETIME', 'null' => true],
            'attendance_date'      => ['type' => 'DATE'],
            'status'               => ['type' => 'ENUM', 'constraint' => ['present', 'absent', 'half_day', 'leave', 'holiday', 'week_off', 'pending_approval'], 'default' => 'pending_approval'],
            'work_mode'            => ['type' => 'ENUM', 'constraint' => ['office', 'work_from_home'], 'null' => true, 'default' => 'office'],
            'remarks'              => ['type' => 'TEXT', 'null' => true],
            'manager_remarks'      => ['type' => 'TEXT', 'null' => true],
            'total_duration_minutes' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addIndex('employee_id');
        $this->forge->addIndex('attendance_date');
        $this->forge->addIndex('status');
        $this->forge->createTable('attendance');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('attendance', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}