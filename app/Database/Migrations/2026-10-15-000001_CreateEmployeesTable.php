<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        // Employees table - extends the user concept for HRMS
        $this->forge->addField([
            'id'                        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'                   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'employee_id'               => ['type' => 'VARCHAR', 'constraint' => 20],  // Unique employee number
            'department_id'             => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'designation_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'employment_type'           => ['type' => 'ENUM', 'constraint' => ['permanent', 'full_time', 'part_time', 'contract', 'probation', 'internship'], 'default' => 'permanent'],
            'employment_status'         => ['type' => 'ENUM', 'constraint' => ['draft', 'active', 'on_leave', 'probation', 'notice_period', 'resigned', 'terminated', 'inactive'], 'default' => 'draft'],
            'joining_date'              => ['type' => 'DATE'],
            'probation_end_date'        => ['type' => 'DATE', 'null' => true],
            'termination_date'          => ['type' => 'DATE', 'null' => true],
            'last_working_date'         => ['type' => 'DATE', 'null' => true],
            'profile_photo'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'emergency_contact_name'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'emergency_contact_phone'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'emergency_contact_relation'=> ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'address_line1'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'address_line2'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'city'                      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'state'                     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'pincode'                   => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'country'                   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => 'India'],
            'blood_group'               => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'marital_status'            => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'nationality'               => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => 'Indian'],
            'pan_number'                => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => true],
            'aadhar_number'             => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'unique' => true],
            'bank_name'                 => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'bank_account_number'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'bank_ifsc'                 => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => true],
            'designation_at_joining'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'department_at_joining'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'resignation_reason'        => ['type' => 'TEXT', 'null' => true],
            'notes'                     => ['type' => 'TEXT', 'null' => true],
            'created_by'                => ['type' => 'INT', 'unsigned' => true],
            'created_at'                => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('employee_id');
        $this->forge->addUniqueKey('aadhar_number');
        $this->forge->createTable('employees');
        // Add indexes after table creation using db query (forge addIndex may have compatibility issues)
        $this->db->query('CREATE INDEX idx_employees_user_id ON employees(user_id)');
        $this->db->query('CREATE INDEX idx_employees_department_id ON employees(department_id)');
        $this->db->query('CREATE INDEX idx_employees_designation_id ON employees(designation_id)');
        $this->db->query('CREATE INDEX idx_employees_employment_status ON employees(employment_status)');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->forge->dropTable('employees', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}