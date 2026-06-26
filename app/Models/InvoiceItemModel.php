<?php
namespace App\Models;
use CodeIgniter\Model;
class InvoiceItemModel extends Model {
    protected $table = 'invoice_items';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['invoice_id','description','quantity','unit_price','total','sort_order'];
}
