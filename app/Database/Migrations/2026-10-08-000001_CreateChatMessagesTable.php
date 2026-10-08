<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatMessagesTable extends Migration
{
    public function up()
    {
        // Create chat_messages table if it doesn't exist
        $schema = $this->db->query("
            CREATE TABLE IF NOT EXISTS chat_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                user_id INT NOT NULL,
                message TEXT,
                image_url VARCHAR(255) DEFAULT NULL,
                message_type VARCHAR(20) DEFAULT 'text',
                is_admin TINYINT(1) DEFAULT 1,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_admin_user (admin_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Also ensure the table exists for subsequent operations
        $this->db->table('chat_messages')->insert([
            'admin_id' => 0,
            'user_id' => 0,
            'message' => 'Migration test',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->db->table('chat_messages')->delete(['id >' => 0]);
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS chat_messages");
    }
}