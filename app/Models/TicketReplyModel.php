<?php
namespace App\Models;
use CodeIgniter\Model;
class TicketReplyModel extends Model {
    protected $table = 'ticket_replies';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['ticket_id','user_id','message','attachment','is_admin'];
    protected $createdField = 'created_at';
}
