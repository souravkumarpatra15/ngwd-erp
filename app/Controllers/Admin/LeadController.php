<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LeadModel;
use App\Models\ClientModel;
use App\Models\LeadActivityModel;
use App\Services\EmailService;
use App\Services\WhatsAppService;
use App\Models\UserModel;

class LeadController extends BaseController
{
    protected $leadModel;
    public function __construct()
    {
        $this->leadModel = new LeadModel();
    }

    public function index()
    {
        return view('admin/leads/index', ['title' => 'Leads']);
    }

    public function datatable()
    {
        $result = $this->leadModel->getDataTable(
            $this->request->getGet('search')['value'] ?? '',
            $this->request->getGet('start') ?? 0,
            $this->request->getGet('length') ?? 10,
            $this->request->getGet('status') ?? ''
        );
        return $this->response->setJSON(['draw' => intval($this->request->getGet('draw')), 'recordsTotal' => $result['total'], 'recordsFiltered' => $result['filtered'], 'data' => $result['data']]);
    }

    public function create()
    {
        return view('admin/leads/create', ['title' => 'Add Lead']);
    }

    public function store()
    {
        if (!$this->validate(['name' => 'required|min_length[2]', 'mobile' => 'required|min_length[10]', 'source' => 'required'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = array_merge($this->request->getPost(), ['lead_number' => $this->generateNumber('LEAD', $this->leadModel), 'status' => 'new', 'created_by' => session()->get('user_id')]);
        unset($data['csrf_test_name']);
        $id = $this->leadModel->insert($data);
        $this->logActivity('leads', $id, 'created', 'Lead added: ' . $data['name']);
        return redirect()->to('admin/leads')->with('success', 'Lead added successfully!');
    }

    public function show($id)
    {
        $lead = $this->leadModel->find($id);
        if (!$lead) return redirect()->to('admin/leads');
        $activityModel = new \App\Models\LeadActivityModel();

        $activities = $activityModel
            ->select('lead_activities.*, users.name as user_name')
            ->join('users', 'users.id = lead_activities.user_id', 'left')
            ->where('lead_activities.lead_id', $id)
            ->orderBy('lead_activities.created_at', 'DESC')
            ->findAll();
        return view('admin/leads/show', [
            'title'      => 'Lead: ' . $lead['name'],
            'lead'       => $lead,
            'activities' => $activities,
        ]);
    }

    public function edit($id)
    {
        return view('admin/leads/edit', ['title' => 'Edit Lead', 'lead' => $this->leadModel->find($id)]);
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);
        $this->leadModel->update($id, $data);
        return redirect()->to("admin/leads/$id")->with('success', 'Lead updated!');
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('admin/leads');
        }

        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Lead not found',
                'token'   => csrf_hash()
            ]);
        }

        $this->leadModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Lead deleted successfully',
            'token'   => csrf_hash()
        ]);
    }

    public function convertToClient($id)
    {
        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return $this->jsonError('Lead not found');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {

            $clientModel = new ClientModel();

            $clientData = [
                'client_number' => $this->generateNumber('CLT', $clientModel),
                'name'          => $lead['name'],
                'company_name'  => $lead['company_name'],
                'phone'         => $lead['mobile'],
                'whatsapp'      => $lead['whatsapp'],
                'email'         => $lead['email'],
                'address'       => $lead['address'],
                'lead_id'       => $id,
                'created_by'    => session()->get('user_id'),
                'status'        => 'active'
            ];

            $clientId = $clientModel->insert($clientData);

            // Create Client Login
            if (!empty($lead['email'])) {

                $userModel = new UserModel();

                $exists = $userModel
                    ->where('email', $lead['email'])
                    ->first();

                if (!$exists) {

                    $password = 'Client@' . rand(1000, 9999);

                    $userModel->insert([
                        'name'       => $lead['name'],
                        'email'      => $lead['email'],
                        'password'   => password_hash($password, PASSWORD_BCRYPT),
                        'role'       => 'client',
                        'client_id'  => $clientId,
                        'is_active'  => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    $mailService = new EmailService();

                    $mailService->sendClientWelcome(
                        $lead['email'],
                        $lead['name'],
                        $password
                    );
                }
            }

            $this->leadModel->update($id, [
                'status'              => 'converted',
                'converted_client_id' => $clientId
            ]);

            $this->logActivity(
                'leads',
                $id,
                'converted',
                'Lead converted to client #' . $clientId
            );

            $db->transCommit();

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Lead converted to client successfully',
                    'client_id' => $clientId,
                    'csrf' => csrf_hash()
                ]);
            }

            return redirect()
                ->to('admin/leads/' . $id)
                ->with('success', 'Lead converted to client successfully!');
        } catch (\Throwable $e) {

            $db->transRollback();

            log_message('error', $e->getMessage());

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to convert lead.'
            ]);
        }
    }

    public function addActivity($id)
    {
        $data = $this->request->getPost();
        (new LeadActivityModel())->insert(['lead_id' => $id, 'user_id' => session()->get('user_id'), 'action' => $data['action'], 'notes' => $data['notes'] ?? '', 'follow_up_date' => $data['follow_up_date'] ?? null]);
        if (!empty($data['follow_up_date'])) $this->leadModel->update($id, ['follow_up_date' => $data['follow_up_date'], 'status' => 'follow_up']);
        return redirect()->to("admin/leads/$id")->with('success', 'Activity logged');
    }

    public function sendWhatsApp($id)
    {
        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->to('admin/leads')->with('error', 'Lead not found');
        }

        $mobile = $lead['whatsapp'] ?: $lead['mobile'];
        $message = trim((string) $this->request->getPost('message'));

        if (empty($mobile)) {
            return redirect()->to("admin/leads/$id")->with('error', 'WhatsApp number not found');
        }

        if ($message === '') {
            return redirect()->to("admin/leads/$id")->with('error', 'WhatsApp message is required');
        }

        $result = (new WhatsAppService())->sendMessage($mobile, $message);

        return $result
            ? redirect()->to("admin/leads/$id")->with('success', 'WhatsApp sent successfully')
            : redirect()->to("admin/leads/$id")->with('error', 'WhatsApp send error!');
    }

    public function sendEmail($id)
    {
        $lead = $this->leadModel->find($id);

        if (!$lead) {
            return redirect()->to('admin/leads')->with('error', 'Lead not found');
        }

        if (empty($lead['email'])) {
            return redirect()->to("admin/leads/$id")->with('error', 'Lead email not found');
        }

        $subject = trim((string) $this->request->getPost('subject'));
        $message = trim((string) $this->request->getPost('message'));

        if ($subject === '') {
            return redirect()->to("admin/leads/$id")->with('error', 'Email subject is required');
        }

        if ($message === '') {
            return redirect()->to("admin/leads/$id")->with('error', 'Email message is required');
        }

        $result = (new EmailService())->send(
            $lead['email'],
            $subject,
            $message
        );

        return $result
            ? redirect()->to("admin/leads/$id")->with('success', 'Mail sent successfully')
            : redirect()->to("admin/leads/$id")->with('error', 'Failed to send mail!');
    }
}
