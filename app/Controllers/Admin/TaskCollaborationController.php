<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\TaskCollaborationModel;

class TaskCollaborationController extends BaseController
{
    protected TaskModel $tasks;
    protected TaskCollaborationModel $collab;

    public function __construct()
    {
        $this->tasks = new TaskModel();
        $this->collab = new TaskCollaborationModel();
    }

    public function detail(int $id)
    {
        $task = $this->tasks->getWithDetails($id);
        if (! $task) return $this->jsonError('Task not found.');

        return $this->response->setJSON([
            'status' => 'success',
            'task' => $task,
            'comments' => $this->collab->comments($id),
            'subtasks' => $this->collab->subtasks($id),
            'activities' => $this->collab->activities($id),
        ]);
    }

    public function comment(int $id)
    {
        if (! $this->tasks->find($id)) return $this->jsonError('Task not found.');
        $body = trim((string)$this->request->getPost('body'));
        if ($body === '') return $this->jsonError('Comment cannot be empty.');

        $this->collab->insert([
            'task_id' => $id,
            'user_id' => (int)session()->get('user_id'),
            'body' => $body,
            'is_internal' => (int)(bool)$this->request->getPost('is_internal'),
        ]);
        $this->collab->log($id, (int)session()->get('user_id'), 'comment_added');
        return $this->jsonSuccess('Comment added.');
    }

    public function subtask(int $id)
    {
        if (! $this->tasks->find($id)) return $this->jsonError('Task not found.');
        $title = trim((string)$this->request->getPost('title'));
        if ($title === '') return $this->jsonError('Subtask title is required.');

        $db = db_connect();
        $db->table('task_subtasks')->insert([
            'task_id' => $id,
            'title' => $title,
            'is_completed' => 0,
            'sort_order' => (int)$db->table('task_subtasks')->where('task_id', $id)->countAllResults(),
            'created_by' => (int)session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->collab->log($id, (int)session()->get('user_id'), 'subtask_added', null, $title);
        return $this->jsonSuccess('Subtask added.');
    }

    public function toggleSubtask(int $id)
    {
        $db = db_connect();
        $subtask = $db->table('task_subtasks')->where('id', $id)->get()->getRowArray();
        if (! $subtask) return $this->jsonError('Subtask not found.');

        $completed = (int)!((int)$subtask['is_completed']);
        $db->table('task_subtasks')->where('id', $id)->update([
            'is_completed' => $completed,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->collab->log((int)$subtask['task_id'], (int)session()->get('user_id'), 'subtask_toggled', $subtask['is_completed'], $completed);
        return $this->jsonSuccess('Subtask updated.', ['is_completed' => $completed]);
    }
}
