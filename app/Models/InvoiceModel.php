<?php
namespace App\Models;
use CodeIgniter\Model;
class InvoiceModel extends Model {
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['invoice_number','client_id','project_id','milestone_id','invoice_date','due_date','subtotal','tax_percent','tax_amount','discount','total','paid_amount','is_gst','notes','terms','status','sent_at','paid_at','created_by'];
    public function getWithDetails($id) {
        return $this->db->table('invoices')->select('invoices.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp, clients.address as client_address, clients.gst_number as client_gst, projects.name as project_name')
            ->join('clients','clients.id = invoices.client_id','left')
            ->join('projects','projects.id = invoices.project_id','left')
            ->where('invoices.id',$id)->get()->getRowArray();
    }
    public function sumBy($col) { return $this->selectSum($col)->get()->getRowArray()[$col] ?? 0; }
    public function getDataTable($search,$start,$length,$status='') {
        $b = $this->db->table('invoices')->select('invoices.*, clients.name as client_name')->join('clients','clients.id = invoices.client_id','left');
        if ($search) $b->groupStart()->like('invoices.invoice_number',$search)->orLike('clients.name',$search)->groupEnd();
        if ($status) $b->where('invoices.status',$status);
        $total = (clone $b)->countAllResults();
        $data = $b->orderBy('invoices.created_at','DESC')->limit($length,$start)->get()->getResultArray();
        return compact('total','data') + ['filtered'=>$total];
    }
}
