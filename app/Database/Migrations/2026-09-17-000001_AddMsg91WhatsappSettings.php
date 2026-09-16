<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds MSG91 WhatsApp settings keys (insert-if-missing, never overwrites). */
class AddMsg91WhatsappSettings extends Migration
{
    public function up()
    {
        $defaults = [
            ['key' => 'whatsapp_provider',        'value' => 'msg91', 'group' => 'whatsapp'],
            ['key' => 'msg91_authkey',            'value' => '',      'group' => 'whatsapp'],
            ['key' => 'msg91_integrated_number',  'value' => '',      'group' => 'whatsapp'],
            ['key' => 'msg91_namespace',          'value' => '',      'group' => 'whatsapp'],
            ['key' => 'msg91_language',           'value' => 'en',    'group' => 'whatsapp'],
            ['key' => 'msg91_base_url',           'value' => 'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message', 'group' => 'whatsapp'],
            ['key' => 'msg91_session_base_url',   'value' => 'https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message', 'group' => 'whatsapp'],
        ];
        $existing = array_column($this->db->table('settings')->select('key')->get()->getResultArray(), 'key');
        foreach ($defaults as $row) {
            if (!in_array($row['key'], $existing, true)) $this->db->table('settings')->insert($row);
        }
    }

    public function down()
    {
        $this->db->table('settings')
            ->whereIn('key', ['whatsapp_provider', 'msg91_authkey', 'msg91_integrated_number', 'msg91_namespace', 'msg91_language', 'msg91_base_url', 'msg91_session_base_url'])
            ->delete();
    }
}
