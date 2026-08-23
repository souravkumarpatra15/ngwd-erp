<?php
namespace App\Models;
use CodeIgniter\Model;
class PaymentModel extends Model {
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['payment_number','client_id','project_id','invoice_id','milestone_id','amount','method','transaction_id','razorpay_order_id','razorpay_payment_id','payment_date','notes','status','created_by'];

    public function getMonthlyRevenue() {
        // A scalar total across currencies is financially misleading. Keep this
        // legacy method returning zero unless exactly one currency is present.
        $totals = $this->getMonthlyRevenueByCurrency();
        return count($totals) === 1 ? (float) reset($totals) : 0;
    }

    public function getMonthlyRevenueChart() {
        // Chart totals are only meaningful when a single currency is present.
        $rows = $this->db->table('payments')
            ->select("MONTH(payments.payment_date) as month, COALESCE(invoices.currency, milestones.currency, 'INR') as currency, SUM(payments.amount) as total")
            ->join('invoices','invoices.id = payments.invoice_id','left')
            ->join('milestones','milestones.id = payments.milestone_id','left')
            ->where('YEAR(payments.payment_date)',date('Y'))->where('payments.status','completed')
            ->groupBy("MONTH(payments.payment_date), COALESCE(invoices.currency, milestones.currency, 'INR')")
            ->get()->getResultArray();
        $currencies=[]; foreach($rows as $r) $currencies[strtoupper((string)$r['currency'])]=true;
        if(count($currencies)!==1) return array_fill(1,12,0);
        $chart=array_fill(1,12,0); foreach($rows as $r) $chart[(int)$r['month']]=(float)$r['total']; return $chart;
    }

    public function getRecent($limit=5) {
        $limit = max(1, min((int) $limit, 100));
        return $this->db->table('payments')->select('payments.*, clients.name as client_name')->join('clients','clients.id = payments.client_id','left')->where('payments.status','completed')->orderBy('payments.created_at','DESC')->limit($limit)->get()->getResultArray();
    }
    public function getDataTable($search,$start,$length,$status='') {
        $start = max(0, (int) $start); $length = (int) $length; if ($length < 1) $length = 25; $length = min($length, 100);
        $base = $this->db->table('payments'); $total = $base->countAllResults();
        $b = $this->db->table('payments')->select("payments.*, clients.name as client_name, projects.name as project_name, COALESCE(invoices.currency, milestones.currency, 'INR') as currency")
            ->join('clients','clients.id = payments.client_id','left')->join('projects','projects.id = payments.project_id','left')->join('invoices','invoices.id = payments.invoice_id','left')->join('milestones','milestones.id = payments.milestone_id','left');
        if ($search) $b->groupStart()->like('clients.name',$search)->orLike('payments.transaction_id',$search)->orLike('payments.payment_number',$search)->groupEnd();
        if ($status) $b->where('payments.status',$status);
        $filtered = (clone $b)->countAllResults(); $data = $b->orderBy('payments.created_at','DESC')->limit($length,$start)->get()->getResultArray(); return compact('total','filtered','data');
    }

    public function getMonthlyRevenueByCurrency(): array {
        $rows = $this->db->table('payments')->select("COALESCE(invoices.currency, milestones.currency, 'INR') AS currency, SUM(payments.amount) AS total")
            ->join('invoices','invoices.id = payments.invoice_id','left')->join('milestones','milestones.id = payments.milestone_id','left')
            ->where('MONTH(payments.payment_date)',date('m'))->where('YEAR(payments.payment_date)',date('Y'))->where('payments.status','completed')
            ->groupBy("COALESCE(invoices.currency, milestones.currency, 'INR')")->get()->getResultArray();
        $result=[]; foreach($rows as $row){$currency=strtoupper((string)($row['currency']??'INR'));$result[$currency]=($result[$currency]??0)+(float)$row['total'];} return $result;
    }
}