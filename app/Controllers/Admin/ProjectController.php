<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\ClientModel;
use App\Models\MilestoneModel;
use App\Models\TaskModel;
use App\Models\DocumentModel;
use App\Models\ActivityModel;
use App\Models\ProjectMemberModel;

class ProjectController extends BaseController
{
    protected $projectModel;
    public function __construct() { $this->projectModel = new ProjectModel(); }

    public function index() {
        return view('admin/projects/index', [
            'title'     => 'Projects',
            'pending'   => $this->projectModel->where('status','pending')->countAllResults(),
            'active'    => $this->projectModel->where('status','development')->countAllResults(),
            'testing'   => $this->projectModel->where('status','testing')->countAllResults(),
            'completed' => $this->projectModel->where('status','completed')->countAllResults(),
        ]);
    }

    public function datatable() {
        $search = $this->request->getGet('search')['value'] ?? '';
        $start  = $this->request->getGet('start') ?? 0;
        $length = $this->request->getGet('length') ?? 10;
        $status = $this->request->getGet('status') ?? '';
        $b = $this->db->table('projects')->select('projects.*, clients.name as client_name')->join('clients','clients.id = projects.client_id','left')->where('projects.deleted_at IS NULL');
        if ($search) $b->groupStart()->like('projects.name',$search)->orLike('clients.name',$search)->orLike('projects.project_number',$search)->groupEnd();
        if ($status) $b->where('projects.status',$status);
        $total = (clone $b)->countAllResults();
        $data  = $b->orderBy('projects.created_at','DESC')->limit($length,$start)->get()->getResultArray();
        $ids = array_column($data, 'id');
        $msStats = [];
        if ($ids) {
            $rows = $this->db->table('milestones')->select('project_id, COUNT(*) as total, SUM(status IN ("completed","paid")) as done')->whereIn('project_id', $ids)->groupBy('project_id')->get()->getResultArray();
            foreach ($rows as $r) $msStats[$r['project_id']] = $r;
        }
        foreach ($data as &$row) {
            $stat = $msStats[$row['id']] ?? null;
            $row['progress'] = ($stat && (int) $stat['total'] > 0) ? (int) round(((int) $stat['done'] / (int) $stat['total']) * 100) : 0;
        }
        unset($row);
        return $this->response->setJSON(['draw'=>intval($this->request->getGet('draw')),'recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$data]);
    }

    public function create() {
        return view('admin/projects/create', ['title'=>'New Project','clients'=>(new ClientModel())->findAll()]);
    }

    public function store() {
        if (!$this->validate(['client_id'=>'required|integer','name'=>'required|min_length[2]','type'=>'required'])) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }
        $data = array_merge($this->request->getPost(), ['project_number'=>$this->generateNumber('PROJ',$this->projectModel),'created_by'=>session()->get('user_id')]);
        unset($data['csrf_test_name']);
        $id = $this->projectModel->insert($data);
        $this->logActivity('projects', $id, 'created', 'Project: ' . $data['name']);
        // The creator is automatically added as a project manager.
        $userId = (int) session()->get('user_id');
        if ($userId > 0) (new ProjectMemberModel())->addMember((int)$id, $userId, 'project_manager', 'manage');
        return redirect()->to("admin/projects/$id")->with('success','Project created!');
    }

    public function show($id) {
        $project = $this->projectModel->getWithClient($id);
        if (!$project) return redirect()->to('admin/projects');
        return view('admin/projects/show', [
            'title'      => $project['name'],
            'project'    => $project,
            'milestones' => (new MilestoneModel())->where('project_id',$id)->orderBy('sort_order')->findAll(),
            'tasks'      => (new TaskModel())->where('project_id',$id)->orderBy('created_at','DESC')->findAll(),
            'documents'  => (new DocumentModel())->where('project_id',$id)->findAll(),
            'activities' => (new ActivityModel())->where('module','projects')->where('module_id',$id)->orderBy('created_at','DESC')->limit(20)->findAll(),
            'members'    => (new ProjectMemberModel())->getByProject((int)$id),
            'progress'   => $this->projectModel->getProgress($id),
        ]);
    }

    public function edit($id) {
        return view('admin/projects/edit', ['title'=>'Edit Project','project'=>$this->projectModel->find($id),'clients'=>(new ClientModel())->findAll()]);
    }

    public function update($id) { $data=$this->request->getPost();unset($data['csrf_test_name']);$this->projectModel->update($id,$data);return redirect()->to("admin/projects/$id")->with('success','Updated!'); }
    public function delete($id) { $this->projectModel->delete($id);return $this->jsonSuccess('Deleted'); }
    public function updateStatus($id) { $s=$this->request->getPost('status');$this->projectModel->update($id,['status'=>$s]);$this->logActivity('projects',$id,'status_changed',"Status: $s");return $this->jsonSuccess('Status updated'); }
}
