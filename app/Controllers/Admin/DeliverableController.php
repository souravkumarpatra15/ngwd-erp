<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DeliverableModel;
use App\Models\ProjectModel;
use App\Models\MilestoneModel;
use App\Models\UserModel;

class DeliverableController extends BaseController
{
    protected DeliverableModel $dm;
    public function __construct() { $this->dm = new DeliverableModel(); }

    private function canManage(): bool
    {
        return in_array((string)session()->get('user_role'), ['superadmin','admin','manager'], true);
    }

    public function index()
    {
        $projectId = (int)($this->request->getGet('project_id') ?: 0);
        $projects = (new ProjectModel())->select('id,name')->orderBy('name')->findAll();
        return view('admin/deliverables/index', [
            'title' => 'Deliverables',
            'projectId' => $projectId,
            'projects' => $projects,
            'deliverables' => $projectId ? $this->dm->getByProject($projectId) : [],
        ]);
    }

    public function create()
    {
        if (!$this->canManage()) return redirect()->to('admin/projects')->with('error','Access denied.');
        $projectId = (int)$this->request->getGet('project_id');
        $project = (new ProjectModel())->getWithClient($projectId);
        if (!$project) return redirect()->to('admin/projects')->with('error','Project not found.');
        return view('admin/deliverables/create', [
            'title' => 'New Deliverable',
            'project' => $project,
            'milestones' => (new MilestoneModel())->where('project_id',$projectId)->orderBy('sort_order')->findAll(),
            'users' => (new UserModel())->admins()->where('is_active',1)->orderBy('name')->findAll(),
        ]);
    }

    public function store()
    {
        if (!$this->canManage()) return redirect()->back()->with('error','Access denied.');
        if (!$this->validate(['project_id'=>'required|integer','title'=>'required|min_length[2]|max_length[200]'])) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $data = $this->request->getPost(); unset($data['csrf_test_name']);
        $data['created_by'] = session()->get('user_id');
        $data['status'] = in_array($data['status'] ?? 'draft', DeliverableModel::STATUSES, true) ? $data['status'] : 'draft';
        $id = $this->dm->insert($data);
        $this->logActivity('projects', (int)$data['project_id'], 'deliverable_created', 'Deliverable: '.$data['title']);
        return redirect()->to('admin/deliverables?project_id='.(int)$data['project_id'])->with('success','Deliverable created.');
    }

    public function updateStatus(int $id)
    {
        if (!$this->canManage()) return $this->jsonError('Access denied.');
        $item = $this->dm->find($id);
        if (!$item) return $this->jsonError('Deliverable not found.');
        $status = (string)$this->request->getPost('status');
        if (!in_array($status, DeliverableModel::STATUSES, true)) return $this->jsonError('Invalid deliverable status.');
        $data = ['status'=>$status];
        if ($status === 'submitted') $data['submitted_at'] = date('Y-m-d H:i:s');
        if (in_array($status, ['under_review','changes_requested','approved','rejected'], true)) $data['reviewed_at'] = date('Y-m-d H:i:s');
        if ($status === 'approved') { $data['approved_at'] = date('Y-m-d H:i:s'); $data['approved_by'] = session()->get('user_id'); }
        $this->dm->update($id,$data);
        $this->logActivity('projects',(int)$item['project_id'],'deliverable_status','Deliverable status: '.$status);
        return $this->jsonSuccess('Deliverable status updated.');
    }
}
