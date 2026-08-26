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
use App\Services\PmsAuthorizationService;

class ProjectController extends BaseController
{
    protected $projectModel;
    protected $pmsAuth;
    public function __construct() { $this->projectModel = new ProjectModel(); $this->pmsAuth = new PmsAuthorizationService(); }
    private function canEditProject(int $id): bool { return $this->pmsAuth->canEditProject((string)session()->get('user_role'), (int)session()->get('user_id'), $id); }
    private function canManageProject(int $id): bool { return $this->pmsAuth->canManageProjectTeam((string)session()->get('user_role'), (int)session()->get('user_id'), $id); }

    public function index() {
        $visible = $this->pmsAuth->getVisibleProjectIds((string) session()->get('user_role'), (int) session()->get('user_id'));
        $scope = fn($q) => $visible === null ? $q : $q->whereIn('id', $visible ?: [0]);
        return view('admin/projects/index', [
            'title'=>'Projects','pending'=>$scope($this->projectModel->where('status','pending'))->countAllResults(),'active'=>$scope($this->projectModel->where('status','development'))->countAllResults(),'testing'=>$scope($this->projectModel->where('status','testing'))->countAllResults(),'completed'=>$scope($this->projectModel->where('status','completed'))->countAllResults(),
        ]);
    }
    public function datatable() {
        $search=$this->request->getGet('search')['value']??'';$start=$this->request->getGet('start')??0;$length=$this->request->getGet('length')??10;$status=$this->request->getGet('status')??'';
        $b=$this->db->table('projects')->select('projects.*, clients.name as client_name')->join('clients','clients.id = projects.client_id','left')->where('projects.deleted_at IS NULL');
        $visible = $this->pmsAuth->getVisibleProjectIds((string) session()->get('user_role'), (int) session()->get('user_id'));
        if ($visible !== null) $b->whereIn('projects.id', $visible ?: [0]);
        if($search)$b->groupStart()->like('projects.name',$search)->orLike('clients.name',$search)->orLike('projects.project_number',$search)->groupEnd();if($status)$b->where('projects.status',$status);
        $total=(clone $b)->countAllResults();$data=$b->orderBy('projects.created_at','DESC')->limit($length,$start)->get()->getResultArray();$ids=array_column($data,'id');$msStats=[];
        if($ids){$rows=$this->db->table('milestones')->select('project_id, COUNT(*) as total, SUM(status IN ("completed","paid")) as done')->whereIn('project_id',$ids)->groupBy('project_id')->get()->getResultArray();foreach($rows as $r)$msStats[$r['project_id']]=$r;}
        foreach($data as &$row){$stat=$msStats[$row['id']]??null;$row['progress']=($stat&&(int)$stat['total']>0)?(int)round(((int)$stat['done']/(int)$stat['total'])*100):0;}unset($row);return $this->response->setJSON(['draw'=>intval($this->request->getGet('draw')),'recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$data]);
    }
    public function create(){ if(!$this->pmsAuth->isPrivilegedInternal((string)session()->get('user_role')) && strtolower((string)session()->get('user_role'))!=='manager') return redirect()->to('admin/projects')->with('error','Access denied.'); return view('admin/projects/create',['title'=>'New Project','clients'=>(new ClientModel())->findAll()]); }
    public function store(){ if(!$this->pmsAuth->isPrivilegedInternal((string)session()->get('user_role')) && strtolower((string)session()->get('user_role'))!=='manager') return redirect()->to('admin/projects')->with('error','Access denied.'); if(!$this->validate(['client_id'=>'required|integer','name'=>'required|min_length[2]','type'=>'required']))return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());$data=array_merge($this->request->getPost(),['project_number'=>$this->generateNumber('PROJ',$this->projectModel),'created_by'=>session()->get('user_id')]);unset($data['csrf_test_name']);$id=$this->projectModel->insert($data);$this->logActivity('projects',$id,'created','Project: '.$data['name']);$userId=(int)session()->get('user_id');if($userId>0)(new ProjectMemberModel())->addMember((int)$id,$userId,'project_manager','manage');return redirect()->to("admin/projects/$id")->with('success','Project created!'); }
    public function show($id){$project=$this->projectModel->getWithClient($id);if(!$project)return redirect()->to('admin/projects');if(!$this->pmsAuth->canViewProjectScoped((string)session()->get('user_role'),(int)session()->get('user_id'),(int)$id))return redirect()->to('admin/projects')->with('error','You are not assigned to this project.');if($this->request->getGet('dashboard')==='1')return view('admin/projects/dashboard',['title'=>$project['name'].' — PMS Dashboard','project'=>$project,'stats'=>$this->getProjectDashboardStats((int)$id),'members'=>(new ProjectMemberModel())->getByProject((int)$id),'activities'=>(new ActivityModel())->where('module','projects')->where('module_id',$id)->orderBy('created_at','DESC')->limit(12)->findAll()]);return view('admin/projects/show',['title'=>$project['name'],'project'=>$project,'milestones'=>(new MilestoneModel())->where('project_id',$id)->orderBy('sort_order')->findAll(),'tasks'=>(new TaskModel())->where('project_id',$id)->orderBy('created_at','DESC')->findAll(),'documents'=>(new DocumentModel())->where('project_id',$id)->findAll(),'activities'=>(new ActivityModel())->where('module','projects')->where('module_id',$id)->orderBy('created_at','DESC')->limit(20)->findAll(),'members'=>(new ProjectMemberModel())->getByProject((int)$id),'progress'=>$this->projectModel->getProgress($id)]);}
    protected function getProjectDashboardStats(int $projectId):array{$today=date('Y-m-d');$tasks=$this->db->table('tasks')->select('status, COUNT(*) AS total')->where('project_id',$projectId)->groupBy('status')->get()->getResultArray();$taskStats=[];foreach($tasks as $row)$taskStats[$row['status']]=(int)$row['total'];$totalTasks=array_sum($taskStats);$doneTasks=($taskStats['done']??0)+($taskStats['completed']??0);$milestoneStats=$this->db->table('milestones')->select('status, COUNT(*) AS total')->where('project_id',$projectId)->groupBy('status')->get()->getResultArray();$milestones=[];foreach($milestoneStats as $row)$milestones[$row['status']]=(int)$row['total'];$totalMilestones=array_sum($milestones);$doneMilestones=($milestones['completed']??0)+($milestones['paid']??0);$overdueTasks=(int)$this->db->table('tasks')->where('project_id',$projectId)->where('due_date <',$today)->whereNotIn('status',['done','completed','cancelled'])->countAllResults();$upcomingTasks=(int)$this->db->table('tasks')->where('project_id',$projectId)->where('due_date >=',$today)->where('due_date <=',date('Y-m-d',strtotime('+7 days')))->whereNotIn('status',['done','completed','cancelled'])->countAllResults();$overdueMilestones=(int)$this->db->table('milestones')->where('project_id',$projectId)->where('due_date <',$today)->whereNotIn('status',['completed','paid','cancelled'])->countAllResults();$pendingApprovals=0;if($this->db->tableExists('deliverables'))$pendingApprovals=(int)$this->db->table('deliverables')->where('project_id',$projectId)->whereIn('status',['submitted','under_review'])->countAllResults();return ['task_stats'=>$taskStats,'total_tasks'=>$totalTasks,'done_tasks'=>$doneTasks,'task_progress'=>$totalTasks?(int)round(($doneTasks/$totalTasks)*100):0,'milestone_stats'=>$milestones,'total_milestones'=>$totalMilestones,'done_milestones'=>$doneMilestones,'milestone_progress'=>$totalMilestones?(int)round(($doneMilestones/$totalMilestones)*100):0,'overdue_tasks'=>$overdueTasks,'upcoming_tasks'=>$upcomingTasks,'overdue_milestones'=>$overdueMilestones,'pending_approvals'=>$pendingApprovals];}
    public function edit($id){if(!$this->canEditProject((int)$id))return redirect()->to('admin/projects/'.$id)->with('error','Access denied.');return view('admin/projects/edit',['title'=>'Edit Project','project'=>$this->projectModel->find($id),'clients'=>(new ClientModel())->findAll()]);}
    public function update($id){if(!$this->canEditProject((int)$id))return redirect()->to('admin/projects/'.$id)->with('error','Access denied.');$data=$this->request->getPost();unset($data['csrf_test_name']);$this->projectModel->update($id,$data);return redirect()->to("admin/projects/$id")->with('success','Updated!');}
    public function delete($id){if(!$this->canManageProject((int)$id))return $this->jsonError('Access denied.');$this->projectModel->delete($id);return $this->jsonSuccess('Deleted');}
    public function updateStatus($id){if(!$this->canEditProject((int)$id))return $this->jsonError('Access denied.');$s=$this->request->getPost('status');$this->projectModel->update($id,['status'=>$s]);$this->logActivity('projects',$id,'status_changed',"Status: $s");return $this->jsonSuccess('Status updated');}
}
