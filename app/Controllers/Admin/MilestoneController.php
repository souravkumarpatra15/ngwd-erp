<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MilestoneModel;
use App\Models\MilestoneNoteModel;
use App\Services\NotificationService;
use App\Services\PaymentService;

class MilestoneController extends BaseController
{
    protected $ms;
    public function __construct()
    {
        $this->ms = new MilestoneModel();
    }

    public function index()
    {
        return view('admin/milestones/index', [
            'title' => 'Milestones',
            'milestones' => $this->db->table('milestones')->select('milestones.*, projects.name as project_name, clients.name as client_name')->join('projects', 'projects.id = milestones.project_id', 'left')->join('clients', 'clients.id = projects.client_id', 'left')->orderBy('milestones.due_date', 'ASC')->get()->getResultArray(),
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);
        $id = $this->ms->insert($data);
        return $this->jsonSuccess('Milestone added', ['id' => $id]);
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);
        $this->ms->update($id, $data);
        return $this->jsonSuccess('Updated');
    }

    public function delete($id)
    {
        $this->ms->delete($id);
        return $this->jsonSuccess('Deleted');
    }

    public function updateStatus($id)
    {
        $s = $this->request->getPost('status');
        $u = ['status' => $s];
        if ($s === 'completed') $u['completed_date'] = date('Y-m-d');
        $this->ms->update($id, $u);
        return $this->jsonSuccess('Status updated');
    }

    public function generatePaymentLink($id)
    {
        $ms = $this->db->table('milestones')->select('milestones.*, projects.client_id')->join('projects', 'projects.id = milestones.project_id')->where('milestones.id', $id)->get()->getRowArray();
        if (!$ms) return $this->jsonError('Milestone not found.');
        if (in_array($ms['status'], ['completed', 'paid'])) return $this->jsonError('This milestone is already completed/paid.');
        $order = (new PaymentService())->createOrder($ms['amount'], 'milestone', $id, $ms['client_id']);
        if ($order) {
            (new NotificationService())->createForClient(
                (int) $ms['client_id'], 'payment_due', 'Payment Link Ready',
                "\"{$ms['title']}\" — " . currencySymbol($ms['currency'] ?? 'INR') . number_format($ms['amount'], 2) . ' is ready to pay',
                (int) $id, 'milestone'
            );
        }
        return $order ? $this->jsonSuccess('Link created', ['url' => base_url("portal/pay-milestone/$id"), 'order' => $order]) : $this->jsonError('Could not create Razorpay order. Check your keys in Settings.');
    }

    // Used by the payment-creation form's "milestones for this project" dropdown.
    // (Pre-existing dead route — the controller method never existed; adding it here
    // since it's the exact endpoint the currency fix in payments/create.php depends on.)
    public function byProject($projectId)
    {
        $rows = $this->ms->where('project_id', $projectId)
            ->whereNotIn('status', ['paid'])
            ->orderBy('sort_order', 'ASC')
            ->findAll();
        return $this->response->setJSON(['data' => $rows]);
    }

    // ── Notes / Q&A thread ──────────────────────────────────────
    public function notes($id)
    {
        $notes = (new MilestoneNoteModel())->getForMilestone((int) $id);
        return $this->response->setJSON(['success' => true, 'notes' => $notes]);
    }

    public function addNote($id)
    {
        $message = trim((string) $this->request->getPost('message'));
        if ($message === '') return $this->jsonError('Note cannot be empty.');

        $ms = $this->db->table('milestones')
            ->select('milestones.id, milestones.title, projects.client_id')
            ->join('projects', 'projects.id = milestones.project_id', 'left')
            ->where('milestones.id', $id)->get()->getRowArray();
        if (!$ms) return $this->jsonError('Milestone not found.');

        (new MilestoneNoteModel())->insert([
            'milestone_id' => $id,
            'user_id'      => session()->get('user_id'),
            'message'      => $message,
            'is_admin'     => 1,
        ]);

        if (!empty($ms['client_id'])) {
            (new NotificationService())->createForClient(
                (int) $ms['client_id'], 'milestone_note', 'New Note on Milestone',
                "\"{$ms['title']}\": {$message}", (int) $id, 'milestone'
            );
        }

        return $this->jsonSuccess('Note added');
    }
}
