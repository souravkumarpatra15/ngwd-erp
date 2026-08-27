<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds the currency actually used by each payment. No FX conversion is performed. */
class AddPaymentCurrency extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payments', [
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 3,
                'default' => 'INR',
                'null' => false,
                'after' => 'amount',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payments', 'currency');
    }
}
