<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MilestoneModel;
use App\Services\PaymentService;

class MilestoneController extends BaseController
{
    protected $ms;

    public function __construct()
    {
        $this->ms = new MilestoneModel();
    }

    // ── LIST ──────────────────────────────────────────────────
    public function index()
    {
        $milestones = $this->db->table('milestones')
            ->select('milestones.*, projects.name as project_name, projects.id as project_id,
                      clients.name as client_name')
            ->join('projects', 'projects.id = milestones.project_id', 'left')
            ->join('clients',  'clients.id  = projects.client_id',    'left')
            ->orderBy('milestones.due_date', 'ASC')
            ->get()->getResultArray();

        return view('admin/milestones/index', [
            'title'      => 'Milestones',
            'milestones' => $milestones,
        ]);
    }

    // ── STORE (AJAX from project show modal) ──────────────────
    public function store()
    {
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);

        // Ensure amount is numeric
        $data['amount']     = (float) ($data['amount'] ?? 0);
        $data['sort_order'] = (int)   ($data['sort_order'] ?? 0);
        if (empty($data['due_date'])) unset($data['due_date']);

        $id = $this->ms->insert($data);

        $ms = $this->ms->find($id);
        $this->logActivity('milestones', $id, 'create', "Milestone: {$ms['title']}");

        return $this->jsonSuccess('Milestone added.', ['id' => $id, 'milestone' => $ms]);
    }

    // ── UPDATE ────────────────────────────────────────────────
    public function update($id)
    {
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);
        $this->ms->update($id, $data);
        return $this->jsonSuccess('Milestone updated.');
    }

    // ── DELETE ────────────────────────────────────────────────
    public function delete($id)
    {
        $ms = $this->ms->find($id);
        if (!$ms) return $this->jsonError('Milestone not found.');
        $this->ms->delete($id);
        $this->logActivity('milestones', $id, 'delete', "Deleted milestone: {$ms['title']}");
        return $this->jsonSuccess('Milestone deleted.');
    }

    // ── STATUS UPDATE ─────────────────────────────────────────
    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');
        $valid  = ['pending', 'in_progress', 'completed', 'paid'];
        if (!in_array($status, $valid)) return $this->jsonError('Invalid status.');

        $update = ['status' => $status];
        if ($status === 'completed') $update['completed_date'] = date('Y-m-d');
        $this->ms->update($id, $update);

        return $this->jsonSuccess('Status updated.');
    }

    // ── PAYMENT LINK ──────────────────────────────────────────
    public function generatePaymentLink($id)
    {
        $ms = $this->db->table('milestones')
            ->select('milestones.*, projects.client_id')
            ->join('projects', 'projects.id = milestones.project_id')
            ->where('milestones.id', $id)
            ->get()->getRowArray();

        if (!$ms) return $this->jsonError('Milestone not found.');

        $order = (new PaymentService())->createOrder(
            (float) $ms['amount'], 'milestone', (int)$id, (int)$ms['client_id']
        );

        if (!$order) return $this->jsonError('Could not create Razorpay order. Check your keys.');

        return $this->jsonSuccess('Payment link created.', [
            'order' => $order,
            'pay_url' => base_url("portal/pay/mil-$id"),
        ]);
    }

    // ── AJAX: milestones by project (for payment create page) ─
    public function byProject($projectId)
    {
        $milestones = $this->db->table('milestones')
            ->select('id, title, amount, status, due_date')
            ->where('project_id', $projectId)
            ->whereNotIn('status', ['paid'])
            ->orderBy('sort_order')
            ->get()->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $milestones]);
    }
}
