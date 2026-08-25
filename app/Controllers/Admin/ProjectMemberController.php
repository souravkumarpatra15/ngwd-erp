<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\ProjectMemberModel;
use App\Models\UserModel;

/** Manage the internal team assigned to a project. */
class ProjectMemberController extends BaseController
{
    protected ProjectModel $projectModel;
    protected ProjectMemberModel $memberModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->memberModel = new ProjectMemberModel();
        $this->userModel = new UserModel();
    }

    private function canManage(): bool
    {
        return in_array((string) session()->get('user_role'), ['superadmin', 'admin', 'manager'], true);
    }

    public function index(int $projectId)
    {
        if (!$this->canManage()) return redirect()->to('admin/projects/' . $projectId)->with('error', 'Access denied.');
        $project = $this->projectModel->getWithClient($projectId);
        if (!$project) return redirect()->to('admin/projects')->with('error', 'Project not found.');

        $members = $this->memberModel->getByProject($projectId);
        $assignedIds = array_column($members, 'user_id');
        $available = $this->userModel->admins()->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        if ($assignedIds) $available = array_values(array_filter($available, fn($u) => !in_array((int) $u['id'], array_map('intval', $assignedIds), true)));

        return view('admin/projects/members', [
            'title' => 'Project Team',
            'project' => $project,
            'members' => $members,
            'availableUsers' => $available,
        ]);
    }

    public function store(int $projectId)
    {
        if (!$this->canManage()) return redirect()->back()->with('error', 'Access denied.');
        if (!$this->projectModel->find($projectId)) return redirect()->to('admin/projects')->with('error', 'Project not found.');

        $userId = (int) $this->request->getPost('user_id');
        $user = $this->userModel->admins()->where('id', $userId)->where('is_active', 1)->first();
        if (!$user) return redirect()->back()->with('error', 'Active internal user not found.');

        $role = (string) ($this->request->getPost('project_role') ?: 'member');
        $access = (string) ($this->request->getPost('access_level') ?: 'edit');
        if (!in_array($role, ['project_manager', 'developer', 'designer', 'qa', 'member'], true)) $role = 'member';
        if (!in_array($access, ['view', 'edit', 'manage'], true)) $access = 'edit';

        $this->memberModel->addMember($projectId, $userId, $role, $access);
        $this->logActivity('projects', $projectId, 'member_added', 'Added ' . $user['name'] . ' as ' . $role);
        return redirect()->to('admin/projects/' . $projectId . '/members')->with('success', 'Project member added.');
    }

    public function update(int $projectId, int $memberId)
    {
        if (!$this->canManage()) return $this->jsonError('Access denied.');
        $member = $this->memberModel->where('id', $memberId)->where('project_id', $projectId)->first();
        if (!$member) return $this->jsonError('Project member not found.');

        $role = (string) $this->request->getPost('project_role');
        $access = (string) $this->request->getPost('access_level');
        if (!in_array($role, ['project_manager', 'developer', 'designer', 'qa', 'member'], true)) return $this->jsonError('Invalid project role.');
        if (!in_array($access, ['view', 'edit', 'manage'], true)) return $this->jsonError('Invalid access level.');

        $this->memberModel->update($memberId, ['project_role' => $role, 'access_level' => $access]);
        $this->logActivity('projects', $projectId, 'member_updated', 'Updated project member role/access.');
        return $this->jsonSuccess('Project member updated.');
    }

    public function delete(int $projectId, int $memberId)
    {
        if (!$this->canManage()) return $this->jsonError('Access denied.');
        $member = $this->memberModel->where('id', $memberId)->where('project_id', $projectId)->first();
        if (!$member) return $this->jsonError('Project member not found.');

        $this->memberModel->removeMember($projectId, (int) $member['user_id']);
        $this->logActivity('projects', $projectId, 'member_removed', 'Removed project member #' . $member['user_id']);
        return $this->jsonSuccess('Project member removed.');
    }
}
