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
        $filters = [
            'project_id' => $this->request->getGet('project_id'),
            'status' => $this->request->getGet('status'),
            'priority' => $this->request->getGet('priority'),
            'assigned_to' => $this->request->getGet('assigned_to'),
            'search' => $this->request->getGet('search'),
        ];
        $filters = array_filter($filters, static fn($v) => $v !== null && $v !== '');
        return view('admin/tasks/index', [
            'title' => 'Tasks',
            'tasks' => $this->tm->getAllWithDetails($filters),
            'projects' => (new ProjectModel())->select('id,name')->findAll(),
            'statuses' => TaskModel::STATUSES,
        ]);
    }

    public function kanban() {
        $pid = (int) ($this->request->getGet('project_id') ?: 0);
        return view('admin/tasks/kanban', [
            'title' => 'Kanban Board',
            'columns' => $this->tm->getKanbanBoard($pid ?: null),
            'projects' => (new ProjectModel())->select('id,name')->findAll(),
        ]);
    }

    public function store() {
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);
        $data['milestone_id'] = !empty($data['milestone_id']) ? (int)$data['milestone_id'] : null;
        $data['project_id'] = !empty($data['project_id']) ? (int)$data['project_id'] : null;
        $data['created_by'] = session()->get('user_id');
        $data['status'] = $this->normalizeStatus($data['status'] ?? 'todo');
        if (!$data['status']) return $this->jsonError('Invalid task status.');
        if (!in_array($data['priority'] ?? 'medium', ['low','medium','high','urgent'], true)) $data['priority'] = 'medium';
        if (empty($data['title'])) return $this->jsonError('Task title is required.');
        $id = $this->tm->insert($data);
        return $this->jsonSuccess('Task created', ['id'=>$id,'task'=>$this->tm->getWithDetails((int)$id)]);
    }

    public function update($id) {
        $task = $this->tm->find((int)$id);
        if (!$task) return $this->jsonError('Task not found.');
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);
        if (array_key_exists('milestone_id', $data)) $data['milestone_id'] = $data['milestone_id'] ?: null;
        if (array_key_exists('status', $data)) {
            $status = $this->normalizeStatus($data['status']);
            if (!$status) return $this->jsonError('Invalid task status.');
            $data['status'] = $status;
            if ($status === 'done' && empty($task['completed_date'])) $data['completed_date'] = date('Y-m-d');
        }
        $this->tm->update((int)$id, $data);
        return $this->jsonSuccess('Task updated.');
    }

    public function updateStatus($id) {
        $task = $this->tm->find((int)$id);
        if (!$task) return $this->jsonError('Task not found.');
        $status = $this->normalizeStatus((string)$this->request->getPost('status'));
        if (!$status) return $this->jsonError('Invalid task status.');
        $data = ['status' => $status];
        if ($status === 'done') $data['completed_date'] = date('Y-m-d');
        if ($status !== 'done') $data['completed_date'] = null;
        $this->tm->update((int)$id, $data);
        return $this->jsonSuccess('Status updated.', ['status' => $status]);
    }

    /** Persist Kanban ordering for one status column. */
    public function reorder() {
        $status = $this->normalizeStatus((string)$this->request->getPost('status'));
        $ids = $this->request->getPost('ids');
        if (!$status || !is_array($ids)) return $this->jsonError('Invalid Kanban ordering request.');
        foreach (array_values($ids) as $position => $id) {
            $task = $this->tm->find((int)$id);
            if (!$task) continue;
            $this->tm->update((int)$id, ['status' => $status, 'sort_order' => $position, 'completed_date' => $status === 'done' ? date('Y-m-d') : null]);
        }
        return $this->jsonSuccess('Kanban order saved.');
    }

    public function delete($id) {
        if (!$this->tm->find((int)$id)) return $this->jsonError('Task not found.');
        $this->tm->delete((int)$id);
        return $this->jsonSuccess('Deleted');
    }

    private function normalizeStatus(string $status): ?string
    {
        $status = strtolower(trim($status));
        $aliases = ['review' => 'code_review', 'completed' => 'done', 'hold' => 'blocked'];
        $status = $aliases[$status] ?? $status;
        return in_array($status, TaskModel::STATUSES, true) ? $status : null;
    }
}
