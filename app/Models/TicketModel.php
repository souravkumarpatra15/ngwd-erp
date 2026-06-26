<?php
namespace App\Models;
use CodeIgniter\Model;
class TicketModel extends Model {
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['ticket_number','client_id','project_id','subject','description','priority','status','closed_at'];
}
