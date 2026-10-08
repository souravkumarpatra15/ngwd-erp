<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatMessagesTable extends Migration
{
    public function up()
    {
        // Create chat_messages table if it doesn't exist
        $this->db->query("
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
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS chat_messages");
    }
}