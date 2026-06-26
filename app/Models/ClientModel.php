<?php
namespace App\Models;
use CodeIgniter\Model;
class ClientModel extends Model {
    protected $table = 'clients';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $allowedFields = ['client_number','name','company_name','phone','whatsapp','email','gst_number','pan_number','address','city','state','pincode','website','notes','lead_id','is_active','created_by'];
    public function search($term) {
        return $this->like('name',$term)->orLike('email',$term)->orLike('phone',$term)->select('id,name,email,phone')->limit(10)->findAll();
    }
}
