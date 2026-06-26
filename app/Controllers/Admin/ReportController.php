<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index() { return view('admin/reports/index', ['title'=>'Reports']); }

    public function revenue() {
        $year  = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? '';
        $b = $this->db->table('payments')->select('payments.*, clients.name as client_name, projects.name as project_name')->join('clients','clients.id = payments.client_id','left')->join('projects','projects.id = payments.project_id','left')->where('YEAR(payment_date)',$year)->where('status','completed');
        if ($month) $b->where('MONTH(payment_date)',$month);
        $payments = $b->orderBy('payment_date','DESC')->get()->getResultArray();
        return view('admin/reports/revenue', ['title'=>'Revenue Report','payments'=>$payments,'total_revenue'=>array_sum(array_column($payments,'amount')),'year'=>$year,'month'=>$month]);
    }

    public function leads() {
        $from=$this->request->getGet('from')??date('Y-m-01');$to=$this->request->getGet('to')??date('Y-m-d');
        $leads=$this->db->table('leads')->where('created_at >=',$from.' 00:00:00')->where('created_at <=',$to.' 23:59:59')->where('deleted_at IS NULL')->get()->getResultArray();
        $conv=count(array_filter($leads,fn($l)=>$l['status']==='converted'));
        return view('admin/reports/leads',['title'=>'Lead Report','leads'=>$leads,'converted'=>$conv,'conv_rate'=>count($leads)?round(($conv/count($leads))*100,1):0,'from'=>$from,'to'=>$to]);
    }

    public function projects() {
        $status=$this->request->getGet('status')??'';
        $b=$this->db->table('projects')->select('projects.*, clients.name as client_name')->join('clients','clients.id = projects.client_id','left')->where('projects.deleted_at IS NULL');
        if($status)$b->where('projects.status',$status);
        return view('admin/reports/projects',['title'=>'Project Report','projects'=>$b->orderBy('projects.created_at','DESC')->get()->getResultArray(),'status'=>$status]);
    }

    public function invoices() {
        $status=$this->request->getGet('status')??'';
        $b=$this->db->table('invoices')->select('invoices.*, clients.name as client_name')->join('clients','clients.id = invoices.client_id','left');
        if($status)$b->where('invoices.status',$status);
        return view('admin/reports/invoices',['title'=>'Invoice Report','invoices'=>$b->orderBy('invoices.created_at','DESC')->get()->getResultArray()]);
    }

    public function payments() {
        $b=$this->db->table('payments')->select('payments.*, clients.name as client_name')->join('clients','clients.id = payments.client_id','left')->where('status','completed');
        return view('admin/reports/payments',['title'=>'Payment Report','payments'=>$b->orderBy('payment_date','DESC')->get()->getResultArray()]);
    }

    public function domains() {
        $b=$this->db->table('domains')->select('domains.*, clients.name as client_name, clients.email as client_email')->join('clients','clients.id = domains.client_id','left');
        return view('admin/reports/domains',['title'=>'Domain Report','domains'=>$b->orderBy('expiry_date','ASC')->get()->getResultArray()]);
    }

    public function export($type, $format) {
        $data = match($type) {
            'revenue' => $this->db->table('payments')->select('payment_number, clients.name as client, amount, method, payment_date, status')->join('clients','clients.id=payments.client_id','left')->where('status','completed')->get()->getResultArray(),
            'leads'   => $this->db->table('leads')->where('deleted_at IS NULL')->get()->getResultArray(),
            'invoices'=> $this->db->table('invoices')->select('invoice_number, clients.name as client, total, paid_amount, status, due_date')->join('clients','clients.id=invoices.client_id','left')->get()->getResultArray(),
            'domains' => $this->db->table('domains')->select('domain_name, clients.name as client, registrar, expiry_date, renewal_cost, status')->join('clients','clients.id=domains.client_id','left')->get()->getResultArray(),
            default   => [],
        };
        if (empty($data)) return redirect()->back()->with('error','No data to export');
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="'.$type.'_'.date('Y-m-d').'.csv"');
            $out = fopen('php://output','w');
            fputcsv($out, array_keys($data[0]));
            foreach ($data as $row) fputcsv($out, $row);
            fclose($out); exit;
        }
        if ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$type.'_'.date('Y-m-d').'.xls"');
            echo '<table><tr>'.implode('',array_map(fn($h)=>'<th>'.htmlspecialchars($h).'</th>',array_keys($data[0]))).'</tr>';
            foreach ($data as $row) echo '<tr>'.implode('',array_map(fn($v)=>'<td>'.htmlspecialchars($v??'').'</td>',$row)).'</tr>';
            echo '</table>'; exit;
        }
        return redirect()->back()->with('error','Unknown format');
    }
}
