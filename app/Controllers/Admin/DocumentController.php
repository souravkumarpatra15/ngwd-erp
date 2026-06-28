<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use App\Models\ClientModel;

class DocumentController extends BaseController
{
    protected $dm;

    public function __construct()
    {
        $this->dm = new DocumentModel();
    }

    // ── LIST ──────────────────────────────────────────────────
    public function index()
    {
        // Join client + project names for display
        $documents = $this->db->table('documents')
            ->select('documents.*, clients.name as client_name, projects.name as project_name')
            ->join('clients',  'clients.id  = documents.client_id',  'left')
            ->join('projects', 'projects.id = documents.project_id', 'left')
            ->orderBy('documents.created_at', 'DESC')
            ->get()->getResultArray();

        return view('admin/documents/index', [
            'title'     => 'Documents',
            'documents' => $documents,
            'clients'   => (new ClientModel())->orderBy('name')->findAll(), // ← was missing!
        ]);
    }

    // ── UPLOAD ────────────────────────────────────────────────
    public function upload()
    {
        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Invalid file.');
        }

        if (!$this->validate([
            'file' => [
                'rules'  => 'uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,png,jpg,jpeg,xlsx,xls,csv,txt,zip]',
                'errors' => ['ext_in' => 'File type not allowed.', 'max_size' => 'Max file size is 10MB.'],
            ],
        ])) {
            return redirect()->back()->with('error', $this->validator->getError('file'));
        }

        $newName = $file->getRandomName();
        $folder  = FCPATH . 'uploads/' . date('Y/m/') . '/';
        $file->move($folder, $newName);

        $clientId  = $this->request->getPost('client_id') ?: null;
        $projectId = $this->request->getPost('project_id') ?: null;

        $this->dm->insert([
            'client_id'   => $clientId,
            'project_id'  => $projectId,
            'category'    => $this->request->getPost('category') ?? 'other',
            'title'       => $this->request->getPost('title') ?: $file->getClientName(),
            'file_name'   => $file->getClientName(),
            'file_path'   => 'uploads/' . date('Y/m/') . '/' . $newName,
            'file_size'   => $file->getSize(),
            'file_type'   => $file->getMimeType(),
            'notes'       => $this->request->getPost('notes') ?? '',
            'created_by'  => session()->get('user_id'),
        ]);

        $this->logActivity('documents', 0, 'upload', 'Uploaded: ' . $file->getClientName());
        return redirect()->back()->with('success', 'File uploaded successfully!');
    }

    // ── DOWNLOAD ──────────────────────────────────────────────
    public function download($id)
    {
        $doc = $this->dm->find($id);
        if (!$doc) return redirect()->back()->with('error', 'Document not found.');

        $path = FCPATH . $doc['file_path'];
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File no longer exists on the server.');
        }

        return $this->response->download($path, null)->setFileName($doc['file_name']);
    }

    // ── DELETE ────────────────────────────────────────────────
    public function delete($id)
    {
        $doc = $this->dm->find($id);
        if (!$doc) return $this->jsonError('Document not found.');

        // Try to delete file from disk
        $path = FCPATH . $doc['file_path'];
        if (file_exists($path)) @unlink($path);

        $this->dm->delete($id);
        $this->logActivity('documents', $id, 'delete', "Deleted: {$doc['file_name']}");
        return $this->jsonSuccess('Document deleted.');
    }
}
