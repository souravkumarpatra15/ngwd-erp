<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DeliverableModel;
use App\Models\DeliverableApprovalModel;
use App\Models\DeliverableFileModel;
use App\Models\ProjectModel;
use App\Models\MilestoneModel;
use App\Models\UserModel;
use App\Services\PmsAuthorizationService;

class DeliverableController extends BaseController
{
    public const FILE_EXTENSIONS = 'pdf,doc,docx,ppt,pptx,xls,xlsx,csv,txt,zip,png,jpg,jpeg,gif,webp,mp4,mov,avi,webm,mkv';
    public const FILE_MAX_KB = 102400; // 100MB — videos need headroom
    protected DeliverableModel $dm;
    protected PmsAuthorizationService $pmsAuth;

    public function __construct() { $this->dm = new DeliverableModel(); $this->pmsAuth = new PmsAuthorizationService(); }

    private function canManageProject(int $projectId): bool
    {
        return $this->pmsAuth->canManageProjectTeam((string)session()->get('user_role'), (int)session()->get('user_id'), $projectId);
    }
    private function fileStorageRoot(): string { return rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR; }
    private function storeUploadedFiles(int $deliverableId): int
    {
        $files = $this->request->getFileMultiple('files');
        if (empty($files)) { $single = $this->request->getFile('file'); $files = $single ? [$single] : []; $legacy = $this->request->getFile('files'); if ($legacy && $legacy->isValid()) $files[] = $legacy; }
        $files = array_filter((array)$files, static fn($f) => $f instanceof \CodeIgniter\HTTP\Files\UploadedFile && $f->isValid());
        if (empty($files)) return 0;
        $allowed = array_map('strtolower', array_map('trim', explode(',', self::FILE_EXTENSIONS)));
        $imageExts = ['png','jpg','jpeg','gif','webp']; $videoExts = ['mp4','mov','avi','webm','mkv'];
        $folder = $this->fileStorageRoot();
        if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) return 0;
        $model = new DeliverableFileModel(); $count = 0;
        foreach ($files as $file) {
            $ext = strtolower($file->getClientExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) continue;
            if (($file->getSize() / 1024) > self::FILE_MAX_KB) continue;
            $newName = $file->getRandomName();
            try { $file->move($folder, $newName); } catch (\Throwable $e) { log_message('error', 'Deliverable upload failed: {message}', ['message' => $e->getMessage()]); continue; }
            $model->insert(['deliverable_id' => $deliverableId, 'filename' => $newName, 'original_name' => $file->getClientName(), 'mime_type' => $file->getClientMimeType(), 'size' => $file->getSize(), 'is_image' => in_array($ext, $imageExts, true) ? 1 : 0, 'is_video' => in_array($ext, $videoExts, true) ? 1 : 0, 'uploaded_by' => session()->get('user_id'), 'created_at' => date('Y-m-d H:i:s')]);
            $count++;
        }
        return $count;
    }

    public function index()
    {
        $projectId = (int)($this->request->getGet('project_id') ?: 0);
        $projects = (new ProjectModel())->select('id,name')->orderBy('name')->findAll();
        $deliverables = $projectId ? $this->dm->getByProject($projectId) : [];
        $fileCounts = [];
        if (!empty($deliverables)) {
            $fileCounts = (new DeliverableFileModel())->countByDeliverables(array_column($deliverables, 'id'));
        }
        return view('admin/deliverables/index', ['title'=>'Deliverables','projectId'=>$projectId,'projects'=>$projects,'deliverables'=>$deliverables,'fileCounts'=>$fileCounts]);
    }

    public function create()
    {
        $projectId = (int)$this->request->getGet('project_id');
        if (!$projectId || !$this->canManageProject($projectId)) return redirect()->to('admin/projects')->with('error','Access denied.');
        $project = (new ProjectModel())->getWithClient($projectId);
        if (!$project) return redirect()->to('admin/projects')->with('error','Project not found.');
        return view('admin/deliverables/create', ['title'=>'New Deliverable','project'=>$project,'milestones'=>(new MilestoneModel())->where('project_id',$projectId)->orderBy('sort_order')->findAll(),'users'=>(new UserModel())->admins()->where('is_active',1)->orderBy('name')->findAll()]);
    }

    public function store()
    {
        $projectId = (int)$this->request->getPost('project_id');
        if (!$projectId || !$this->canManageProject($projectId)) return redirect()->back()->with('error','Access denied.');
        if (!$this->validate(['project_id'=>'required|integer','title'=>'required|min_length[2]|max_length[200]'])) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && $file->getSize() > 0) {
            $ext = strtolower($file->getClientExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));
            $allowed = array_map('strtolower', array_map('trim', explode(',', self::FILE_EXTENSIONS)));
            if (!in_array($ext, $allowed, true)) return redirect()->back()->withInput()->with('error','File type not allowed. Allowed: images, videos, PDF, Word, Excel, PowerPoint, ZIP, TXT, CSV.');
            if (($file->getSize() / 1024) > self::FILE_MAX_KB) return redirect()->back()->withInput()->with('error','Max file size is 100MB.');
        }
        $data = $this->request->getPost(); unset($data['csrf_test_name']); $data['created_by'] = session()->get('user_id'); $data['status'] = in_array($data['status'] ?? 'draft', DeliverableModel::STATUSES, true) ? $data['status'] : 'draft';
        $id = $this->dm->insert($data); $this->logActivity('projects',$projectId,'deliverable_created','Deliverable: '.$data['title']);
        $uploaded = $this->storeUploadedFiles((int)$id);
        $msg = 'Deliverable created.' . ($uploaded ? " {$uploaded} file(s) attached." : '');
        return redirect()->to('admin/deliverables/view/'.$id)->with('success',$msg);
    }

    public function view(int $id)
    {
        $item = $this->dm->getWithDetails($id);
        if (!$item) return redirect()->to('admin/deliverables')->with('error', 'Deliverable not found.');
        if (!$this->pmsAuth->canViewProjectScoped((string) session()->get('user_role'), (int) session()->get('user_id'), (int) $item['project_id'])) {
            return redirect()->to('admin/deliverables')->with('error', 'You are not assigned to this project.');
        }
        return view('admin/deliverables/detail', [
            'title' => $item['title'],
            'deliverable' => $item,
            'history' => (new DeliverableApprovalModel())->history($id),
            'files' => (new DeliverableFileModel())->forDeliverable((int)$id),
            'canManage' => $this->canManageProject((int) $item['project_id']),
        ]);
    }

    public function uploadFiles(int $id)
    {
        $item = $this->dm->find($id);
        if (!$item) return $this->jsonError('Deliverable not found.');
        if (!$this->canManageProject((int)$item['project_id'])) return $this->jsonError('Access denied.');
        $count = $this->storeUploadedFiles((int)$id);
        if ($count === 0) return $this->jsonError('No valid files received. Allowed: images, videos, PDF, Word, Excel, PowerPoint, ZIP, TXT, CSV (max 100MB each).');
        $this->logActivity('projects', (int)$item['project_id'], 'deliverable_file', "Files added to deliverable: {$item['title']} ({$count})");
        return $this->jsonSuccess($count > 1 ? "{$count} files uploaded." : 'File uploaded.', ['files' => (new DeliverableFileModel())->forDeliverable((int)$id)]);
    }

    public function downloadFile(int $fileId)
    {
        $file = (new DeliverableFileModel())->find((int)$fileId);
        if (!$file) return redirect()->back()->with('error', 'File not found.');
        $item = $this->dm->find((int)$file['deliverable_id']);
        if (!$item || !$this->pmsAuth->canViewProjectScoped((string)session()->get('user_role'), (int)session()->get('user_id'), (int)$item['project_id'])) return redirect()->back()->with('error', 'Access denied.');
        $path = $this->fileStorageRoot() . $file['filename'];
        $root = realpath($this->fileStorageRoot()); $real = realpath($path);
        if (!$root || !$real || !is_file($real) || !str_starts_with($real, $root . DIRECTORY_SEPARATOR)) return redirect()->back()->with('error', 'File no longer exists on the server.');
        $mime = $file['mime_type'] ?: mime_content_type($real);
        $ext = strtolower(pathinfo($file['original_name'] ?? '', PATHINFO_EXTENSION));
        $inline = !empty($file['is_image']) || !empty($file['is_video']) || str_starts_with((string)$mime, 'image/') || str_starts_with((string)$mime, 'video/') || in_array($ext, ['mp4','mov','avi','webm','mkv','png','jpg','jpeg','gif','webp','pdf'], true);
        if ($inline) return $this->streamInlineFile($real, $file['original_name'], $this->mediaMime($ext, $mime));
        return $this->response->download($real, null)->setFileName($file['original_name']);
    }

    public function deleteFile(int $fileId)
    {
        $file = (new DeliverableFileModel())->find((int)$fileId);
        if (!$file) return $this->jsonError('File not found.');
        $item = $this->dm->find((int)$file['deliverable_id']);
        if (!$item || !$this->canManageProject((int)$item['project_id'])) return $this->jsonError('Access denied.');
        $path = $this->fileStorageRoot() . $file['filename'];
        if (is_file($path)) @unlink($path);
        (new DeliverableFileModel())->delete((int)$fileId);
        $this->logActivity('projects', (int)$item['project_id'], 'deliverable_file_removed', "File removed from deliverable: {$item['title']} ({$file['original_name']})");
        return $this->jsonSuccess('File removed.');
    }

    public function updateStatus(int $id)
    {
        $item = $this->dm->find($id);
        if (!$item) return $this->jsonError('Deliverable not found.');
        if (!$this->canManageProject((int)$item['project_id'])) return $this->jsonError('Access denied.');
        $status = (string)$this->request->getPost('status');
        if (!in_array($status, DeliverableModel::STATUSES, true)) return $this->jsonError('Invalid deliverable status.');
        $data=['status'=>$status]; if($status==='submitted')$data['submitted_at']=date('Y-m-d H:i:s'); if(in_array($status,['under_review','changes_requested','approved','rejected'],true))$data['reviewed_at']=date('Y-m-d H:i:s'); if($status==='approved'){$data['approved_at']=date('Y-m-d H:i:s');$data['approved_by']=session()->get('user_id');}
        $this->dm->update($id,$data); $this->logActivity('projects',(int)$item['project_id'],'deliverable_status','Deliverable status: '.$status); return $this->jsonSuccess('Deliverable status updated.');
    }
}
