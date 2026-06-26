<?php
namespace App\Models;
use CodeIgniter\Model;
class PaymentModel extends Model {
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['payment_number','client_id','project_id','invoice_id','milestone_id','amount','method','transaction_id','razorpay_order_id','razorpay_payment_id','payment_date','notes','status','created_by'];
    public function getMonthlyRevenue() {
        $r = $this->db->table('payments')->selectSum('amount')->where('MONTH(payment_date)',date('m'))->where('YEAR(payment_date)',date('Y'))->where('status','completed')->get()->getRowArray();
        return $r['amount'] ?? 0;
    }
    public function getMonthlyRevenueChart() {
        $rows = $this->db->table('payments')->select('MONTH(payment_date) as month, SUM(amount) as total')->where('YEAR(payment_date)',date('Y'))->where('status','completed')->groupBy('MONTH(payment_date)')->get()->getResultArray();
        $chart = array_fill(1,12,0);
        foreach ($rows as $r) $chart[$r['month']] = (float)$r['total'];
        return $chart;
    }
    public function getRecent($limit=5) {
        return $this->db->table('payments')->select('payments.*, clients.name as client_name')->join('clients','clients.id = payments.client_id','left')->where('payments.status','completed')->orderBy('payments.created_at','DESC')->limit($limit)->get()->getResultArray();
    }
    public function getDataTable($search,$start,$length,$status='') {
        $b = $this->db->table('payments')->select('payments.*, clients.name as client_name, projects.name as project_name')->join('clients','clients.id = payments.client_id','left')->join('projects','projects.id = payments.project_id','left');
        if ($search) $b->groupStart()->like('clients.name',$search)->orLike('payments.transaction_id',$search)->orLike('payments.payment_number',$search)->groupEnd();
        if ($status) $b->where('payments.status',$status);
        $total = (clone $b)->countAllResults();
        $data = $b->orderBy('payments.created_at','DESC')->limit($length,$start)->get()->getResultArray();
        return compact('total','data') + ['filtered'=>$total];
    }
}
