<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Multi-currency follow-up: projects.budget / advance_paid / total_paid
 * were still shown with a hardcoded ₹ (visible to the client on the
 * project detail page too), even after invoices/proposals/milestones
 * got a currency column. This closes that gap.
 * Run: php spark migrate
 */
class AddProjectCurrency extends Migration
{
    public function up()
    {
        $this->forge->addColumn('projects', [
            'currency' => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'INR', 'null' => false, 'after' => 'budget'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('projects', 'currency');
    }
}
