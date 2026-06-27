<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProposalModel;
use App\Models\ClientModel;
use App\Services\PDFService;
use App\Services\EmailService;
use App\Services\WhatsAppService;

class ProposalController extends BaseController
{
    protected $pm;
    public function __construct()
    {
        $this->pm = new ProposalModel();
    }
    public function index()
    {
        return view('admin/proposals/index', ['title' => 'Proposals']);
    }
    public function create()
    {
        return view('admin/proposals/create', ['title' => 'New Proposal', 'clients' => (new ClientModel())->findAll(), 'default_terms' => $this->settings['proposal_terms'] ?? '']);
    }
    public function store()
    {
        $data = array_merge($this->request->getPost(), ['proposal_number' => $this->generateNumber('PROP', $this->pm), 'created_by' => session()->get('user_id'), 'status' => 'draft']);
        unset($data['csrf_test_name']);
        $id = $this->pm->insert($data);
        return redirect()->to("admin/proposals/$id")->with('success', 'Proposal created!');
    }
    public function show($id)
    {
        return view('admin/proposals/show', ['title' => 'Proposal', 'proposal' => $this->pm->getWithDetails($id)]);
    }
    public function edit($id)
    {
        return view('admin/proposals/edit', ['title' => 'Edit Proposal', 'proposal' => $this->pm->find($id), 'clients' => (new ClientModel())->findAll()]);
    }
    public function update($id)
    {
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);
        $this->pm->update($id, $data);
        return redirect()->to("admin/proposals/$id")->with('success', 'Updated!');
    }
    public function delete($id)
    {
        $this->pm->delete($id);
        return $this->jsonSuccess('Deleted');
    }
    public function generatePDF($id)
    {
        return (new PDFService())->generateProposal($this->pm->getWithDetails($id), $this->settings);
    }
    public function sendEmail($id)
    {
        $p = $this->pm->getWithDetails($id);
        $path = (new PDFService())->saveProposalPDF($p, $this->settings);
        $res = (new EmailService())->sendProposal($p, $path);
        if ($res) {
            $this->pm->update($id, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
            return $this->jsonSuccess('Emailed!');
        }
        return $this->jsonError('Failed');
    }
    public function sendWhatsApp($id)
    {
        $p = $this->pm->getWithDetails($id);
        $msg = "Dear {$p['client_name']},\n\nProposal: *{$p['title']}*\nAmount: ₹" . number_format($p['total_amount'], 2) . "\nValid: {$p['valid_until']}\n\nDownload: " . base_url("admin/proposals/pdf/$id") . "\n\nRegards,\n" . ($this->settings['company_name'] ?? '');
        $res = (new WhatsAppService())->sendMessage($p['client_whatsapp'], $msg);
        if ($res) {
            $this->pm->update($id, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
            return $this->jsonSuccess('WhatsApp sent!');
        }
        return $this->jsonError('Failed');
    }

    public function datatable()
    {
        $draw   = (int) ($this->request->getGet('draw') ?? 1);
        $start  = (int) ($this->request->getGet('start') ?? 0);
        $length = (int) ($this->request->getGet('length') ?? 25);
        $search = $this->request->getGet('search')['value'] ?? '';
        $status = $this->request->getGet('status') ?? '';

        $builder = $this->db->table('proposals')
            ->select('
            proposals.id,
            proposals.proposal_number,
            proposals.title,
            proposals.total_amount,
            proposals.valid_until,
            proposals.status,
            clients.name AS client_name
        ')
            ->join('clients', 'clients.id = proposals.client_id', 'left')
            ->where('proposals.deleted_at IS NULL');

        if ($status !== '') {
            $builder->where('proposals.status', $status);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('proposals.proposal_number', $search)
                ->orLike('proposals.title', $search)
                ->orLike('clients.name', $search)
                ->orLike('proposals.status', $search)
                ->groupEnd();
        }

        $filteredBuilder = clone $builder;
        $filtered = $filteredBuilder->countAllResults(false);

        $totalBuilder = $this->db->table('proposals')
            ->where('deleted_at IS NULL');

        if ($status !== '') {
            $totalBuilder->where('status', $status);
        }

        $total = $totalBuilder->countAllResults();

        $data = $builder
            ->orderBy('proposals.id', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }
}
