<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MarketingLeadModel;
use App\Models\ClientModel;
use App\Models\ProjectModel;

class MarketingLeadController extends BaseController
{
    protected $mlm;

    public function __construct()
    {
        $this->mlm = new MarketingLeadModel();
    }

    public function index()
    {
        return view('admin/marketing_leads/index', [
            'title'   => 'Marketing Leads',
            'clients' => (new ClientModel())->where('is_active', 1)->orderBy('name')->findAll(),
        ]);
    }

    public function datatable()
    {
        $result = $this->mlm->getDataTable(
            $this->request->getGet('search')['value'] ?? '',
            $this->request->getGet('start') ?? 0,
            $this->request->getGet('length') ?? 10,
            $this->request->getGet('client_id') ?? '',
            $this->request->getGet('status') ?? '',
            $this->request->getGet('platform') ?? ''
        );
        return $this->response->setJSON([
            'draw'            => intval($this->request->getGet('draw')),
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $result['data'],
        ]);
    }

    public function store()
    {
        if (!$this->validate([
            'client_id' => 'required|is_natural_no_zero',
            'name'      => 'required|min_length[2]',
        ])) {
            return $this->request->isAJAX()
                ? $this->jsonError('Please fill all required fields.', $this->validator->getErrors())
                : redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $clientId = (int) $this->request->getPost('client_id');
        if (!(new ClientModel())->find($clientId)) {
            return $this->request->isAJAX() ? $this->jsonError('Selected client does not exist.') : redirect()->back()->with('error', 'Selected client does not exist.');
        }

        $projectId = (int) $this->request->getPost('project_id');
        if ($projectId && !(new ProjectModel())->where('id', $projectId)->where('client_id', $clientId)->first()) {
            return $this->request->isAJAX() ? $this->jsonError('Selected project does not belong to this client.') : redirect()->back()->with('error', 'Selected project does not belong to this client.');
        }

        $data = $this->request->getPost([
            'client_id','project_id','campaign_name','platform','name','phone','whatsapp',
            'email','city','requirement','notes','lead_date',
        ]);
        $data['project_id'] = $projectId ?: null;
        $data['status']     = 'new';
        $data['lead_date']  = $data['lead_date'] ?: date('Y-m-d');
        $data['created_by'] = session()->get('user_id');

        $id = $this->mlm->insert($data);
        $this->logActivity('marketing_leads', $id, 'created', 'Marketing lead added: ' . $data['name']);

        if ($this->request->isAJAX()) return $this->jsonSuccess('Lead added successfully!');
        return redirect()->to('admin/marketing-leads')->with('success', 'Lead added successfully!');
    }

    public function edit($id)
    {
        $lead = $this->mlm->find($id);
        if (!$lead) return $this->jsonError('Lead not found.');
        return $this->response->setJSON(['status' => 'success', 'data' => $lead]);
    }

    public function update($id)
    {
        $lead = $this->mlm->find($id);
        if (!$lead) return $this->jsonError('Lead not found.');

        $clientId = (int) $this->request->getPost('client_id');
        if ($clientId && !(new ClientModel())->find($clientId)) {
            return $this->jsonError('Selected client does not exist.');
        }
        $projectId = (int) $this->request->getPost('project_id');
        if ($projectId && !(new ProjectModel())->where('id', $projectId)->where('client_id', $clientId ?: $lead['client_id'])->first()) {
            return $this->jsonError('Selected project does not belong to this client.');
        }

        $data = $this->request->getPost([
            'client_id','project_id','campaign_name','platform','name','phone','whatsapp',
            'email','city','requirement','notes','status','lead_date',
        ]);
        $data['project_id'] = $projectId ?: null;

        $this->mlm->update($id, $data);
        $this->logActivity('marketing_leads', $id, 'updated', 'Marketing lead updated: ' . ($data['name'] ?? $lead['name']));
        return $this->jsonSuccess('Lead updated successfully!');
    }

    public function delete($id)
    {
        $lead = $this->mlm->find($id);
        if (!$lead) return $this->jsonError('Lead not found.');
        $this->mlm->delete($id);
        $this->logActivity('marketing_leads', $id, 'deleted', 'Marketing lead deleted: ' . $lead['name']);
        return $this->jsonSuccess('Lead deleted successfully.');
    }

    /**
     * Bulk-import leads for a client/project via CSV upload.
     * Expected headers (case-insensitive, order-independent):
     * name, phone, whatsapp, email, city, campaign_name, requirement, notes, lead_date
     * Only "name" is mandatory per row.
     */
    public function uploadCsv()
    {
        $clientId = (int) $this->request->getPost('client_id');
        if (!$clientId || !(new ClientModel())->find($clientId)) {
            return redirect()->back()->with('error', 'Please select a valid client before uploading.');
        }
        $projectId = (int) $this->request->getPost('project_id');
        if ($projectId && !(new ProjectModel())->where('id', $projectId)->where('client_id', $clientId)->first()) {
            return redirect()->back()->with('error', 'Selected project does not belong to the selected client.');
        }

        $file = $this->request->getFile('csv_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Please choose a valid CSV file.');
        }
        if (!$this->validate(['csv_file' => ['rules' => 'uploaded[csv_file]|max_size[csv_file,5120]|ext_in[csv_file,csv]', 'errors' => ['ext_in' => 'Only CSV files are allowed.', 'max_size' => 'Max file size is 5MB.']]])) {
            return redirect()->back()->with('error', $this->validator->getError('csv_file'));
        }

        $handle = fopen($file->getTempName(), 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'Unable to read the uploaded file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file appears to be empty.');
        }
        $map = [];
        foreach ($header as $i => $col) {
            $key = strtolower(trim((string) $col));
            $key = str_replace(' ', '_', $key);
            $map[$key] = $i;
        }
        $required = 'name';
        if (!array_key_exists($required, $map)) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV must include a "name" column.');
        }

        $campaignFallback = trim((string) $this->request->getPost('campaign_name'));
        $platformFallback = $this->request->getPost('platform') ?: 'facebook';
        $userId = session()->get('user_id');

        $rows = [];
        $skipped = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $get = fn($field) => isset($map[$field], $row[$map[$field]]) ? trim((string) $row[$map[$field]]) : '';
            $name = $get('name');
            if ($name === '') { $skipped++; continue; }

            $leadDate = $get('lead_date');
            $leadDate = $leadDate && strtotime($leadDate) ? date('Y-m-d', strtotime($leadDate)) : date('Y-m-d');

            $rows[] = [
                'client_id'     => $clientId,
                'project_id'    => $projectId ?: null,
                'campaign_name' => $get('campaign_name') ?: $campaignFallback ?: null,
                'platform'      => in_array($get('platform'), ['facebook','instagram','google_ads','other']) ? $get('platform') : $platformFallback,
                'name'          => $name,
                'phone'         => $get('phone') ?: null,
                'whatsapp'      => $get('whatsapp') ?: null,
                'email'         => $get('email') ?: null,
                'city'          => $get('city') ?: null,
                'requirement'   => $get('requirement') ?: null,
                'notes'         => $get('notes') ?: null,
                'status'        => 'new',
                'lead_date'     => $leadDate,
                'created_by'    => $userId,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
        }
        fclose($handle);

        if (empty($rows)) {
            return redirect()->back()->with('error', 'No valid rows found in the CSV file.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            foreach (array_chunk($rows, 200) as $chunk) {
                $this->mlm->insertBatch($chunk);
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Marketing lead CSV import failed: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Import failed. Please check the file format and try again.');
        }

        $this->logActivity('marketing_leads', $clientId, 'csv_import', count($rows) . ' leads imported for client #' . $clientId);

        $msg = count($rows) . ' lead(s) imported successfully.' . ($skipped ? " ($skipped row(s) skipped for missing name.)" : '');
        return redirect()->to('admin/marketing-leads')->with('success', $msg);
    }
}
