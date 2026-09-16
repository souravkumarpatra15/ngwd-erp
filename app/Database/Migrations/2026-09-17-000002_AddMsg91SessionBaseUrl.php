<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds the MSG91 session API base URL key (insert-if-missing). */
class AddMsg91SessionBaseUrl extends Migration
{
    public function up()
    {
        $exists = $this->db->table('settings')->where('key', 'msg91_session_base_url')->countAllResults();
        if (!$exists) {
            $this->db->table('settings')->insert([
                'key' => 'msg91_session_base_url',
                'value' => 'https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message',
                'group' => 'whatsapp',
            ]);
        }
    }

    public function down()
    {
        $this->db->table('settings')->where('key', 'msg91_session_base_url')->delete();
    }
}
