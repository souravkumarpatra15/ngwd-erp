<?php
namespace App\Models;
use CodeIgniter\Model;
class DocumentModel extends Model {
    protected $table = 'documents';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['client_id','project_id','category','title','file_name','file_path','file_size','file_type','reference_id','reference_type','notes','created_by'];
}
