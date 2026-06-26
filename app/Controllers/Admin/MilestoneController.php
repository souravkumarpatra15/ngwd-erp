<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\MilestoneModel;
use App\Services\PaymentService;

class MilestoneController extends BaseController
{
    protected $ms;
    public function __construct() { $this->ms = new MilestoneModel(); }

    public function index() {
        return view('admin/milestones/index', [
            'title' => 'Milestones',
            'milestones' => $this->db->table('milestones')->select('milestones.*, projects.name as project_name, clients.name as client_name')->join('projects','projects.id = milestones.project_id','left')->join('clients','clients.id = projects.client_id','left')->orderBy('milestones.due_date','ASC')->get()->getResultArray(),
        ]);
    }

    public function store() {
        $data = $this->request->getPost(); unset($data['csrf_test_name']);
        $id = $this->ms->insert($data);
        return $this->jsonSuccess('Milestone added', ['id'=>$id]);
    }

    public function update($id) {
        $data = $this->request->getPost(); unset($data['csrf_test_name']);
        $this->ms->update($id, $data);
        return $this->jsonSuccess('Updated');
    }

    public function delete($id) { $this->ms->delete($id); return $this->jsonSuccess('Deleted'); }

    public function updateStatus($id) {
        $s = $this->request->getPost('status');
        $u = ['status'=>$s];
        if ($s === 'completed') $u['completed_date'] = date('Y-m-d');
        $this->ms->update($id, $u);
        return $this->jsonSuccess('Status updated');
    }

    public function generatePaymentLink($id) {
        $ms = $this->db->table('milestones')->select('milestones.*, projects.client_id')->join('projects','projects.id = milestones.project_id')->where('milestones.id',$id)->get()->getRowArray();
        $order = (new PaymentService())->createOrder($ms['amount'], 'milestone', $id, $ms['client_id']);
        return $order ? $this->jsonSuccess('Link created',['url'=>base_url("portal/pay/mil-$id"),'order'=>$order]) : $this->jsonError('Failed');
    }
}
