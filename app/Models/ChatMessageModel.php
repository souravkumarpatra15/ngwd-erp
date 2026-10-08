<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatMessageModel extends Model
{
    protected $table      = 'chat_messages';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdAt  = 'created_at';
    protected $updatedAt  = 'updated_at';

    protected $allowedFields = [
        'admin_id', 
        'user_id', 
        'message', 
        'image_url', 
        'message_type',
        'is_admin',
        'is_read'
    ];

    // Disable timestamp auto-update if you're managing it yourself
    // protected $timestamps = false;
}