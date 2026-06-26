<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\ProjectModel;

class TaskController extends BaseController
{
    protected $tm;
    public function __construct() { $this->tm = new TaskModel(); }

    public function index() {
        $pid = $this->request->getGet('project_id');
        $b = $this->db->table('tasks')->select('tasks.*, projects.name as project_name')->join('projects','projects.id = tasks.project_id','left')->orderBy('tasks.priority DESC, tasks.due_date ASC');
        if ($pid) $b->where('tasks.project_id',$pid);
        return view('admin/tasks/index', ['title'=>'Tasks','tasks'=>$b->get()->getResultArray(),'projects'=>(new ProjectModel())->select('id,name')->findAll()]);
    }

    public function kanban() {
        $pid = $this->request->getGet('project_id');
        $b = $this->db->table('tasks')->select('tasks.*, projects.name as project_name')->join('projects','projects.id = tasks.project_id','left');
        if ($pid) $b->where('tasks.project_id',$pid);
        $tasks = $b->get()->getResultArray();
        $cols  = ['todo'=>[],'in_progress'=>[],'review'=>[],'completed'=>[],'hold'=>[]];
        foreach ($tasks as $t) $cols[$t['status']][] = $t;
        return view('admin/tasks/kanban', ['title'=>'Kanban Board','columns'=>$cols,'projects'=>(new ProjectModel())->select('id,name')->findAll()]);
    }

    public function store() {
        $data = $this->request->getPost(); unset($data['csrf_test_name']);
        $data['created_by'] = session()->get('user_id');
        $id = $this->tm->insert($data);
        return $this->jsonSuccess('Task created', ['id'=>$id,'task'=>$this->tm->find($id)]);
    }

    public function update($id) { $data=$this->request->getPost();unset($data['csrf_test_name']);$this->tm->update($id,$data);return $this->jsonSuccess('Updated'); }
    public function updateStatus($id) { $s=$this->request->getPost('status');$u=['status'=>$s];if($s==='completed')$u['completed_date']=date('Y-m-d');$this->tm->update($id,$u);return $this->jsonSuccess('Status updated'); }
    public function delete($id) { $this->tm->delete($id);return $this->jsonSuccess('Deleted'); }
}
