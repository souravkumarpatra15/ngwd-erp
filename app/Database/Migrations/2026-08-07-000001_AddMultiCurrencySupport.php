<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Adds a `currency` column (3-letter ISO code, e.g. INR/USD/EUR/GBP) to
 * invoices, proposals and milestones so amounts can be shown in the
 * correct symbol for international clients. Display-only — no FX
 * conversion is performed; the number stored is already in the chosen
 * currency's units.
 * Run: php spark migrate
 */
class AddMultiCurrencySupport extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoices', [
            'currency' => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'INR', 'null' => false, 'after' => 'is_gst'],
        ]);
        $this->forge->addColumn('proposals', [
            'currency' => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'INR', 'null' => false, 'after' => 'total_amount'],
        ]);
        $this->forge->addColumn('milestones', [
            'currency' => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'INR', 'null' => false, 'after' => 'amount'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', 'currency');
        $this->forge->dropColumn('proposals', 'currency');
        $this->forge->dropColumn('milestones', 'currency');
    }
}
