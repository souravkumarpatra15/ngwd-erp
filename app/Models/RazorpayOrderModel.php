<?php
namespace App\Models;
use CodeIgniter\Model;
class RazorpayOrderModel extends Model {
    protected $table = 'razorpay_orders';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['order_id','entity_type','entity_id','client_id','amount','currency','status','payment_id'];
}
