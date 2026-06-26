<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateAllTables extends Migration
{
    public function up()
    {
        // Users
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'name'=>['type'=>'VARCHAR','constraint'=>100],'email'=>['type'=>'VARCHAR','constraint'=>150],'password'=>['type'=>'VARCHAR','constraint'=>255],'role'=>['type'=>'ENUM','constraint'=>['superadmin','client'],'default'=>'client'],'client_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'avatar'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'is_active'=>['type'=>'TINYINT','constraint'=>1,'default'=>1],'last_login'=>['type'=>'DATETIME','null'=>true],'remember_token'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('email');
        $this->forge->createTable('users');

        // Settings
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'key'=>['type'=>'VARCHAR','constraint'=>100],'value'=>['type'=>'TEXT','null'=>true],'group'=>['type'=>'VARCHAR','constraint'=>50,'default'=>'general'],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('key');
        $this->forge->createTable('settings');

        // Leads
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'lead_number'=>['type'=>'VARCHAR','constraint'=>20],'name'=>['type'=>'VARCHAR','constraint'=>100],'company_name'=>['type'=>'VARCHAR','constraint'=>150,'null'=>true],'mobile'=>['type'=>'VARCHAR','constraint'=>20],'whatsapp'=>['type'=>'VARCHAR','constraint'=>20,'null'=>true],'email'=>['type'=>'VARCHAR','constraint'=>150,'null'=>true],'address'=>['type'=>'TEXT','null'=>true],'source'=>['type'=>'ENUM','constraint'=>['facebook','instagram','whatsapp','google_ads','website','phone','referral','linkedin','manual'],'default'=>'manual'],'budget'=>['type'=>'DECIMAL','constraint'=>'12,2','null'=>true],'requirement'=>['type'=>'TEXT','null'=>true],'notes'=>['type'=>'TEXT','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['new','contacted','follow_up','proposal_sent','negotiation','converted','lost'],'default'=>'new'],'follow_up_date'=>['type'=>'DATE','null'=>true],'assigned_date'=>['type'=>'DATE','null'=>true],'converted_client_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true],'deleted_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('lead_number'); $this->forge->addKey('status'); $this->forge->addKey('follow_up_date');
        $this->forge->createTable('leads');

        // Clients
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'client_number'=>['type'=>'VARCHAR','constraint'=>20],'name'=>['type'=>'VARCHAR','constraint'=>100],'company_name'=>['type'=>'VARCHAR','constraint'=>150,'null'=>true],'phone'=>['type'=>'VARCHAR','constraint'=>20],'whatsapp'=>['type'=>'VARCHAR','constraint'=>20,'null'=>true],'email'=>['type'=>'VARCHAR','constraint'=>150,'null'=>true],'gst_number'=>['type'=>'VARCHAR','constraint'=>20,'null'=>true],'pan_number'=>['type'=>'VARCHAR','constraint'=>15,'null'=>true],'address'=>['type'=>'TEXT','null'=>true],'city'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],'state'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],'pincode'=>['type'=>'VARCHAR','constraint'=>10,'null'=>true],'website'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'notes'=>['type'=>'TEXT','null'=>true],'lead_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'is_active'=>['type'=>'TINYINT','constraint'=>1,'default'=>1],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true],'deleted_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('client_number');
        $this->forge->createTable('clients');

        // Projects
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'project_number'=>['type'=>'VARCHAR','constraint'=>20],'client_id'=>['type'=>'INT','unsigned'=>true],'name'=>['type'=>'VARCHAR','constraint'=>200],'type'=>['type'=>'ENUM','constraint'=>['website','ecommerce','mobile_app','software','crm','erp','seo','digital_marketing','hosting','domain']],'description'=>['type'=>'TEXT','null'=>true],'start_date'=>['type'=>'DATE','null'=>true],'delivery_date'=>['type'=>'DATE','null'=>true],'budget'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'advance_paid'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'total_paid'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'status'=>['type'=>'ENUM','constraint'=>['pending','development','testing','revision','completed','on_hold','cancelled'],'default'=>'pending'],'notes'=>['type'=>'TEXT','null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true],'deleted_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('project_number'); $this->forge->addKey('client_id');
        $this->forge->createTable('projects');

        // Milestones
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'project_id'=>['type'=>'INT','unsigned'=>true],'title'=>['type'=>'VARCHAR','constraint'=>200],'description'=>['type'=>'TEXT','null'=>true],'amount'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'due_date'=>['type'=>'DATE','null'=>true],'completed_date'=>['type'=>'DATE','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['pending','in_progress','completed','paid'],'default'=>'pending'],'sort_order'=>['type'=>'INT','default'=>0],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addKey('project_id');
        $this->forge->createTable('milestones');

        // Proposals
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'proposal_number'=>['type'=>'VARCHAR','constraint'=>20],'client_id'=>['type'=>'INT','unsigned'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'lead_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'title'=>['type'=>'VARCHAR','constraint'=>200],'introduction'=>['type'=>'LONGTEXT','null'=>true],'project_overview'=>['type'=>'LONGTEXT','null'=>true],'scope_of_work'=>['type'=>'LONGTEXT','null'=>true],'deliverables'=>['type'=>'LONGTEXT','null'=>true],'timeline'=>['type'=>'LONGTEXT','null'=>true],'pricing'=>['type'=>'LONGTEXT','null'=>true],'terms'=>['type'=>'LONGTEXT','null'=>true],'total_amount'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'valid_until'=>['type'=>'DATE','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['draft','sent','accepted','rejected'],'default'=>'draft'],'sent_at'=>['type'=>'DATETIME','null'=>true],'accepted_at'=>['type'=>'DATETIME','null'=>true],'notes'=>['type'=>'TEXT','null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('proposal_number');
        $this->forge->createTable('proposals');

        // Agreements
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'agreement_number'=>['type'=>'VARCHAR','constraint'=>20],'client_id'=>['type'=>'INT','unsigned'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'proposal_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'title'=>['type'=>'VARCHAR','constraint'=>200],'client_information'=>['type'=>'LONGTEXT','null'=>true],'project_information'=>['type'=>'LONGTEXT','null'=>true],'deliverables'=>['type'=>'LONGTEXT','null'=>true],'timeline'=>['type'=>'LONGTEXT','null'=>true],'payment_terms'=>['type'=>'LONGTEXT','null'=>true],'support_terms'=>['type'=>'LONGTEXT','null'=>true],'cancellation_terms'=>['type'=>'LONGTEXT','null'=>true],'terms_conditions'=>['type'=>'LONGTEXT','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['draft','sent','signed','rejected'],'default'=>'draft'],'sent_at'=>['type'=>'DATETIME','null'=>true],'signed_at'=>['type'=>'DATETIME','null'=>true],'signature_ip'=>['type'=>'VARCHAR','constraint'=>45,'null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('agreement_number');
        $this->forge->createTable('agreements');

        // Invoices
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'invoice_number'=>['type'=>'VARCHAR','constraint'=>30],'client_id'=>['type'=>'INT','unsigned'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'milestone_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'invoice_date'=>['type'=>'DATE'],'due_date'=>['type'=>'DATE'],'subtotal'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'tax_percent'=>['type'=>'DECIMAL','constraint'=>'5,2','default'=>0],'tax_amount'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'discount'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'total'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'paid_amount'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'balance_due'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'is_gst'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],'notes'=>['type'=>'TEXT','null'=>true],'terms'=>['type'=>'TEXT','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['draft','sent','paid','partial','overdue','cancelled'],'default'=>'draft'],'sent_at'=>['type'=>'DATETIME','null'=>true],'paid_at'=>['type'=>'DATETIME','null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('invoice_number'); $this->forge->addKey('client_id');
        $this->forge->createTable('invoices');

        // Invoice Items
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'invoice_id'=>['type'=>'INT','unsigned'=>true],'description'=>['type'=>'VARCHAR','constraint'=>500],'quantity'=>['type'=>'DECIMAL','constraint'=>'10,2','default'=>1],'unit_price'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'total'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'sort_order'=>['type'=>'INT','default'=>0]]);
        $this->forge->addKey('id',true); $this->forge->addKey('invoice_id');
        $this->forge->createTable('invoice_items');

        // Payments
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'payment_number'=>['type'=>'VARCHAR','constraint'=>20],'client_id'=>['type'=>'INT','unsigned'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'invoice_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'milestone_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'amount'=>['type'=>'DECIMAL','constraint'=>'12,2'],'method'=>['type'=>'ENUM','constraint'=>['razorpay','upi','bank_transfer','cash','cheque']],'transaction_id'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'razorpay_order_id'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'razorpay_payment_id'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'payment_date'=>['type'=>'DATE'],'notes'=>['type'=>'TEXT','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['pending','completed','failed','refunded'],'default'=>'completed'],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('payment_number'); $this->forge->addKey('client_id');
        $this->forge->createTable('payments');

        // Domains
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'client_id'=>['type'=>'INT','unsigned'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'domain_name'=>['type'=>'VARCHAR','constraint'=>255],'registrar'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'registration_date'=>['type'=>'DATE','null'=>true],'expiry_date'=>['type'=>'DATE'],'cost'=>['type'=>'DECIMAL','constraint'=>'10,2','default'=>0],'renewal_cost'=>['type'=>'DECIMAL','constraint'=>'10,2','default'=>0],'auto_renew'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],'notes'=>['type'=>'TEXT','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['active','expiring_soon','expired'],'default'=>'active'],'last_reminder_sent'=>['type'=>'DATETIME','null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addKey('client_id'); $this->forge->addKey('expiry_date');
        $this->forge->createTable('domains');

        // Hostings
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'client_id'=>['type'=>'INT','unsigned'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'provider'=>['type'=>'VARCHAR','constraint'=>100],'package'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'server_ip'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],'server_details'=>['type'=>'TEXT','null'=>true],'username'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'control_panel_url'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'purchase_date'=>['type'=>'DATE','null'=>true],'expiry_date'=>['type'=>'DATE'],'cost'=>['type'=>'DECIMAL','constraint'=>'10,2','default'=>0],'renewal_cost'=>['type'=>'DECIMAL','constraint'=>'10,2','default'=>0],'notes'=>['type'=>'TEXT','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['active','expiring_soon','expired'],'default'=>'active'],'last_reminder_sent'=>['type'=>'DATETIME','null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addKey('client_id');
        $this->forge->createTable('hostings');

        // Documents
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'client_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'category'=>['type'=>'ENUM','constraint'=>['proposal','agreement','invoice','contract','screenshot','client_file','project_file','other'],'default'=>'other'],'title'=>['type'=>'VARCHAR','constraint'=>200],'file_name'=>['type'=>'VARCHAR','constraint'=>255],'file_path'=>['type'=>'VARCHAR','constraint'=>500],'file_size'=>['type'=>'INT','null'=>true],'file_type'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],'reference_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'reference_type'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],'notes'=>['type'=>'TEXT','null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addKey('client_id');
        $this->forge->createTable('documents');

        // Tasks
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'title'=>['type'=>'VARCHAR','constraint'=>200],'description'=>['type'=>'TEXT','null'=>true],'priority'=>['type'=>'ENUM','constraint'=>['low','medium','high','urgent'],'default'=>'medium'],'due_date'=>['type'=>'DATE','null'=>true],'completed_date'=>['type'=>'DATE','null'=>true],'status'=>['type'=>'ENUM','constraint'=>['todo','in_progress','review','completed','hold'],'default'=>'todo'],'sort_order'=>['type'=>'INT','default'=>0],'notes'=>['type'=>'TEXT','null'=>true],'assigned_to'=>['type'=>'INT','unsigned'=>true,'null'=>true],'created_by'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addKey('project_id');
        $this->forge->createTable('tasks');

        // Tickets
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'ticket_number'=>['type'=>'VARCHAR','constraint'=>20],'client_id'=>['type'=>'INT','unsigned'=>true],'project_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'subject'=>['type'=>'VARCHAR','constraint'=>200],'description'=>['type'=>'TEXT'],'priority'=>['type'=>'ENUM','constraint'=>['low','medium','high','urgent'],'default'=>'medium'],'status'=>['type'=>'ENUM','constraint'=>['open','in_progress','closed'],'default'=>'open'],'closed_at'=>['type'=>'DATETIME','null'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('ticket_number'); $this->forge->addKey('client_id');
        $this->forge->createTable('tickets');

        // Ticket Replies
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'ticket_id'=>['type'=>'INT','unsigned'=>true],'user_id'=>['type'=>'INT','unsigned'=>true],'message'=>['type'=>'LONGTEXT'],'attachment'=>['type'=>'VARCHAR','constraint'=>500,'null'=>true],'is_admin'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addKey('ticket_id');
        $this->forge->createTable('ticket_replies');

        // Activities
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'user_id'=>['type'=>'INT','unsigned'=>true],'module'=>['type'=>'VARCHAR','constraint'=>50],'module_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'action'=>['type'=>'VARCHAR','constraint'=>100],'description'=>['type'=>'TEXT','null'=>true],'ip_address'=>['type'=>'VARCHAR','constraint'=>45,'null'=>true],'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true);
        $this->forge->createTable('activities');

        // Lead Activities
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'lead_id'=>['type'=>'INT','unsigned'=>true],'user_id'=>['type'=>'INT','unsigned'=>true],'action'=>['type'=>'VARCHAR','constraint'=>100],'notes'=>['type'=>'TEXT','null'=>true],'follow_up_date'=>['type'=>'DATE','null'=>true],'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addKey('lead_id');
        $this->forge->createTable('lead_activities');

        // Notifications
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'user_id'=>['type'=>'INT','unsigned'=>true],'type'=>['type'=>'VARCHAR','constraint'=>50],'title'=>['type'=>'VARCHAR','constraint'=>200],'message'=>['type'=>'TEXT','null'=>true],'reference_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],'reference_type'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],'is_read'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],'read_at'=>['type'=>'DATETIME','null'=>true],'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addKey('user_id');
        $this->forge->createTable('notifications');

        // Razorpay Orders
        $this->forge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'order_id'=>['type'=>'VARCHAR','constraint'=>100],'entity_type'=>['type'=>'ENUM','constraint'=>['invoice','milestone','renewal']],'entity_id'=>['type'=>'INT','unsigned'=>true],'client_id'=>['type'=>'INT','unsigned'=>true],'amount'=>['type'=>'DECIMAL','constraint'=>'12,2'],'currency'=>['type'=>'VARCHAR','constraint'=>10,'default'=>'INR'],'status'=>['type'=>'ENUM','constraint'=>['created','paid','failed','expired'],'default'=>'created'],'payment_id'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'created_at'=>['type'=>'DATETIME','null'=>true],'updated_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('order_id');
        $this->forge->createTable('razorpay_orders');

        // CI Sessions table
        $this->forge->addField(['id'=>['type'=>'VARCHAR','constraint'=>128,'null'=>false],'ip_address'=>['type'=>'VARCHAR','constraint'=>45,'null'=>false],'timestamp'=>['type'=>'INT','unsigned'=>true,'default'=>0,'null'=>false],'data'=>['type'=>'BLOB','null'=>false]]);
        $this->forge->addKey('id',true); $this->forge->addKey('timestamp');
        $this->forge->createTable('ci_sessions');
    }

    public function down()
    {
        $tables = ['ci_sessions','razorpay_orders','notifications','lead_activities','activities','ticket_replies','tickets','tasks','documents','hostings','domains','payments','invoice_items','invoices','agreements','proposals','milestones','projects','clients','leads','settings','users'];
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $t) $this->forge->dropTable($t, true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
