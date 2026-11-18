<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class ExpandUserRolesEnum extends Migration
{
    public function up()
    {
        // Expand the role ENUM to include HRMS roles
        // Using raw SQL since CodeIgniter forge ENUM alteration is limited
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','client','hr_manager','recruitment_manager','project_manager','team_leader','permanent_employee','intern','finance_manager') DEFAULT 'client'");
    }

    public function down()
    {
        // Revert to original ENUM
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','client') DEFAULT 'client'");
    }
}