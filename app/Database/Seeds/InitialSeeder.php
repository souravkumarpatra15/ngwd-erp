<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

/**
 * InitialSeeder
 * Run with:  php spark db:seed InitialSeeder
 * Creates default settings + superadmin user.
 * Change password immediately after first login!
 */
class InitialSeeder extends Seeder
{
    public function run()
    {
        // ── Default Settings ───────────────────────────────────
        $settings = [
            ['key' => 'company_name',    'value' => 'NGWebD Consulting Pvt. Ltd.', 'group' => 'company'],
            ['key' => 'company_logo',    'value' => '',                             'group' => 'company'],
            ['key' => 'company_gst',     'value' => '',                             'group' => 'company'],
            ['key' => 'company_pan',     'value' => '',                             'group' => 'company'],
            ['key' => 'company_address', 'value' => 'Your Address, City, State - PIN', 'group' => 'company'],
            ['key' => 'company_phone',   'value' => '+91 XXXXXXXXXX',               'group' => 'company'],
            ['key' => 'company_email',   'value' => 'info@ngwebd.com',              'group' => 'company'],
            ['key' => 'company_website', 'value' => 'https://ngwebd.com',           'group' => 'company'],

            ['key' => 'smtp_host',       'value' => 'smtp.gmail.com', 'group' => 'email'],
            ['key' => 'smtp_port',       'value' => '587',            'group' => 'email'],
            ['key' => 'smtp_username',   'value' => '',               'group' => 'email'],
            ['key' => 'smtp_password',   'value' => '',               'group' => 'email'],
            ['key' => 'smtp_encryption', 'value' => 'tls',            'group' => 'email'],

            ['key' => 'whatsapp_token',    'value' => '', 'group' => 'whatsapp'],
            ['key' => 'whatsapp_phone_id', 'value' => '', 'group' => 'whatsapp'],

            ['key' => 'razorpay_key',    'value' => '', 'group' => 'payment'],
            ['key' => 'razorpay_secret', 'value' => '', 'group' => 'payment'],

            ['key' => 'invoice_prefix', 'value' => 'NGWD', 'group' => 'invoice'],
            ['key' => 'tax_percent',    'value' => '18',   'group' => 'invoice'],
            ['key' => 'invoice_terms',  'value' => 'Payment is due within 15 days of invoice date. Late payments are subject to 2% monthly interest.', 'group' => 'invoice'],
            ['key' => 'proposal_terms', 'value' => 'This proposal is valid for 30 days from the date of issue. Prices are subject to change after the validity period.', 'group' => 'proposal'],
            ['key' => 'agreement_terms','value' => "1. Both parties agree to maintain confidentiality of all project information.\n2. All intellectual property created during this project will be owned by the client upon full payment.\n3. The service provider is not responsible for any losses arising from the use of the delivered product.\n4. Any disputes shall be resolved through mutual discussion.", 'group' => 'agreement'],

            ['key' => 'default_tax',    'value' => '18',  'group' => 'invoice'],
            ['key' => 'currency',       'value' => 'INR', 'group' => 'general'],
            ['key' => 'date_format',    'value' => 'd/m/Y','group' => 'general'],
        ];

        // Avoid duplicate key errors on re-seed
        $this->db->table('settings')->truncate();
        $this->db->table('settings')->insertBatch($settings);

        // ── Admin User ─────────────────────────────────────────
        // Default password: Admin@1234  ← change immediately after login
        $existing = $this->db->table('users')->where('email', 'admin@ngwebd.com')->get()->getRow();
        if (!$existing) {
            $this->db->table('users')->insert([
                'name'       => 'Super Admin',
                'email'      => 'admin@ngwebd.com',
                'password'   => password_hash('Admin@1234', PASSWORD_BCRYPT),
                'role'       => 'superadmin',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            echo "✓ Admin user created\n";
        } else {
            echo "⚠ Admin user already exists — skipped\n";
        }

        echo "\n";
        echo "══════════════════════════════════════════\n";
        echo "  Seeding complete!\n";
        echo "──────────────────────────────────────────\n";
        echo "  Login URL : /login\n";
        echo "  Email     : admin@ngwebd.com\n";
        echo "  Password  : Admin@1234\n";
        echo "  ⚠ Change password immediately!\n";
        echo "══════════════════════════════════════════\n";
    }
}
