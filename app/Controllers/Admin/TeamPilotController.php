<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TaskModel;
use App\Models\UserModel;
use App\Models\ProjectModel;
use App\Models\ProjectMemberModel;
use App\Models\ActivityModel;
use App\Models\OnboardingChecklistsModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\CLIRequest;

class TeamPilotController extends BaseController
{
    protected $helpers = ['url', 'form', 'html', 'csrf'];
    protected $taskModel;
    protected $userModel;
    protected $projectModel;
    protected $projectMemberModel;
    protected $activityModel;
    protected $auth;
    protected $onboardingChecklistsModel;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->taskModel = new TaskModel();
        $this->userModel = new UserModel();
        $this->projectModel = new ProjectModel();
        $this->projectMemberModel = new ProjectMemberModel();
        $this->activityModel = new ActivityModel();
        $this->auth = new PmsAuthorizationService();
        $this->onboardingChecklistsModel = new OnboardingChecklistsModel();
    }

    /**
     * Daily stand-up dashboard
     * Shows: yesterday's work, today's plan, blockers, quick capture
     */
    public function standUp($projectId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();

        // Check appropriate role - employees, PM, Team Leader, HR, etc.
        if (!$auth->canViewProjects($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access stand-up.');
        }

        $userId = session()->get('user_id');
        $userName = session()->get('user_name');

        // Get project context if provided
        $project = null;
        $teamMembers = [];
        $myStandUps = [];
        $teamStandUps = [];
        $yesterday = $today = $blockers = '';

        if ($projectId) {
            $project = $this->projectModel->find($projectId);
            if (!$project) {
                return redirect()->to('/admin/projects')->with('error', 'Project not found.');
            }

            // Check user has access to this project
            if (!$auth->canViewProjectScoped($role, (int)session()->get('user_id'), (int)$projectId)) {
                return redirect()->to('/admin/projects')->with('error', 'You are not assigned to this project.');
            }

            $teamMembers = $this->projectMemberModel->getByProject((int)$projectId);

            // Get user's recent stand-ups/activities (with author names)
            $myStandUps = $this->standUpQuery()
                ->where('a.module_id', $projectId)
                ->where('a.user_id', $userId)
                ->orderBy('a.created_at', 'DESC')
                ->limit(10)
                ->get()->getResultArray();

            // Get team stand-ups
            $teamStandUps = $this->standUpQuery()
                ->where('a.module_id', $projectId)
                ->orderBy('a.created_at', 'DESC')
                ->limit(50)
                ->get()->getResultArray();

            // Populate today's sections from the user's latest stand-up today
            $todayEntry = $this->standUpQuery()
                ->where('a.module_id', $projectId)
                ->where('a.user_id', $userId)
                ->where('a.created_at >=', date('Y-m-d 00:00:00'))
                ->orderBy('a.created_at', 'DESC')
                ->get()->getFirstRow('array');
            if ($todayEntry) {
                $parsed = $this->parseStandUp((string) $todayEntry['description']);
                $yesterday = $parsed['yesterday'];
                $today     = $parsed['today'];
                $blockers  = $parsed['blockers'];
            }
        }

        // Get current user's tasks for context
        $myTasks = [];
        if ($projectId) {
            $myTasks = $this->taskModel->getAllWithDetails([
                'project_id' => $projectId,
                'assigned_to' => $userId,
            ]);
        }

        return view('admin/team-pilot/stand-up', [
            'title' => 'Daily Stand-Up',
            'project' => $project,
            'teamMembers' => $teamMembers,
            'myStandUps' => $myStandUps,
            'teamStandUps' => $teamStandUps,
            'myTasks' => $myTasks,
            'yesterday' => $yesterday,
            'today' => $today,
            'blockers' => $blockers,
            'auth' => $auth,
            'canViewProjects' => $auth->canViewProjects($role),
            'userName' => $userName,
            'userId' => $userId,
            'projectId' => $projectId,
        ]);
    }

    /** Activities joined with author names, filtered to stand_up entries */
    private function standUpQuery()
    {
        return \Config\Database::connect()->table('activities a')
            ->select('a.*, u.name AS user_name')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->where('a.module', 'projects')
            ->where('a.action', 'stand_up');
    }

    /** Extract yesterday/today/blockers from a stored stand-up description */
    private function parseStandUp(string $description): array
    {
        $out = ['yesterday' => '', 'today' => '', 'blockers' => ''];
        foreach (array_keys($out) as $field) {
            if (preg_match('/' . $field . '="([^"]*)"/', $description, $m)) {
                $out[$field] = $m[1];
            }
        }
        return $out;
    }

    /** 
     * Process stand-up submission
     * Captures: yesterday's work, today's plan, blockers
     */
    public function submitStandUp($projectId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewProjects($role)) {
            return $this->jsonError('You do not have permission to submit stand-up.');
        }

        $userId = session()->get('user_id');
        $userName = session()->get('user_name');

        $yesterday = $this->request->getGet('yesterday') ?? $this->request->getPost('yesterday') ?? '';
        $today = $this->request->getGet('today') ?? $this->request->getPost('today') ?? '';
        $blockers = $this->request->getGet('blockers') ?? $this->request->getPost('blockers') ?? '';

        // Input validation
        if (trim($yesterday) === '' && trim($today) === '') {
            return $this->jsonError('Please provide at least yesterday\'s work or today\'s plan.');
        }

        // Start activity log entry for stand-up
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => $userId,
            'module' => 'projects',
            'module_id' => $projectId ?? 0,
            'action' => 'stand_up',
            'description' => $userName . ' stand-up: yesterday="' . substr($yesterday, 0, 200) . '", today="' . substr($today, 0, 200) . '", blockers="' . substr($blockers, 0, 200) . '"',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Also store in onboarding/checklists if applicable
        if ($projectId) {
            $this->onboardingChecklistsModel->insert([
                'project_id' => $projectId,
                'user_id' => $userId,
                'checklist_item' => 'Stand-up: ' . date('M d'),
                'is_completed' => true,
                'completed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->jsonSuccess('Stand-up submitted successfully', [
            'yesterday' => $yesterday,
            'today' => $today,
            'blockers' => $blockers,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** 
     * Quick capture - fast task/note creation from anywhere
     */
    public function quickCapture($projectId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewProjects($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to quick capture.');
        }

        $userId = session()->get('user_id');

        // Get project context if provided
        $project = null;
        $assignedTasks = [];

        if ($projectId) {
            $project = $this->projectModel->find($projectId);
            if (!$project) {
                return redirect()->to('/admin/projects')->with('error', 'Project not found.');
            }

            // Check access
            if (!$auth->canViewProjectScoped($role, (int)session()->get('user_id'), (int)$projectId)) {
                return redirect()->to('/admin/projects')->with('error', 'You are not assigned to this project.');
            }

            $assignedTasks = $this->taskModel->getAllWithDetails([
                'project_id' => $projectId,
                'assigned_to' => $userId,
            ]);
        }

        return view('admin/team-pilot/quick-capture', [
            'title' => 'Quick Capture',
            'project' => $project,
            'assignedTasks' => $assignedTasks,
            'projectId' => $projectId,
            'userId' => $userId,
        ]);
    }

    /** 
     * Process quick capture submission
     * Creates task or note quickly
     */
    public function submitQuickCapture()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewProjects($role)) {
            return $this->jsonError('You do not have permission to quick capture.');
        }

        $userId = session()->get('user_id');
        $userName = session()->get('user_name') ?? 'User';
        $title = $this->request->getPost('title') ?? '';
        $description = $this->request->getPost('description') ?? '';
        $type = $this->request->getPost('type') ?? 'task'; // task, note, milestone
        $projectId = (int)($this->request->getPost('project_id') ?? 0);
        $assignedTo = (int)($this->request->getPost('assigned_to') ?? $userId);
        $priority = $this->request->getPost('priority') ?? 'medium';
        $dueDate = $this->request->getPost('due_date') ?? null;

        if (trim($title) === '') {
            return $this->jsonError('Title is required for quick capture.');
        }

        $data = [
            'project_id' => $projectId > 0 ? $projectId : null,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'status' => 'todo',
            'assigned_to' => $assignedTo,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($dueDate) {
            $data['due_date'] = $dueDate;
        }

        // If type is note, we'll store as activity or special marker
        if ($type === 'note') {
            // Store as activity log
            $activity = new \App\Models\ActivityModel();
            $activity->insert([
                'user_id' => $userId,
                'module' => 'projects',
                'module_id' => $projectId > 0 ? $projectId : 0,
                'action' => 'quick_capture_note',
                'description' => $userName . ' quick capture note: ' . substr($title, 0, 200),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            return $this->jsonSuccess('Note captured via quick capture', ['type' => 'note']);
        }

        // Create task
        $taskId = $this->taskModel->insert($data);

        // Also add activity log
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => $userId,
            'module' => 'projects',
            'module_id' => $taskId,
            'action' => 'task_created',
            'description' => $userName . ' created task via quick capture: ' . $title,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSuccess('Task created via quick capture', ['task_id' => $taskId]);
    }

    /** 
     * Blockers tracking - view and manage blockers
     */
    public function blockers($projectId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewProjects($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access blockers.');
        }

        $userId = session()->get('user_id');
        $blockers = [];

        if ($projectId) {
            // Get tasks with blockers status
            $tasks = $this->taskModel->where('project_id', $projectId)
                ->where('status', 'blocked')
                ->orderBy('due_date', 'ASC')
                ->findAll();

            foreach ($tasks as $task) {
                $blockers[] = [
                    'task_id' => $task['id'],
                    'task_title' => $task['title'],
                    'assigned_to' => $task['assigned_to'],
                    'description' => $task['description'],
                    'due_date' => $task['due_date'],
                    'created_at' => $task['created_at'],
                ];
            }
        }

        return view('admin/team-pilot/blockers', [
            'title' => 'Blockers',
            'projectId' => $projectId,
            'blockers' => $blockers,
            'canViewProjects' => $auth->canViewProjects($role),
        ]);
    }

    /** 
     * Mark a blocker as resolved
     */
    public function resolveBlocker($taskId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewProjects($role)) {
            return $this->jsonError('You do not have permission to resolve blockers.');
        }

        if (!$taskId) {
            $taskId = $this->request->getPost('task_id') ?: $this->request->getPost('resolve_task_id');
        }
        if (!$taskId) {
            return $this->jsonError('Task ID is required.');
        }

        // Update task status from blocked to in_progress or todo
        $this->taskModel->update($taskId, [
            'status' => 'in_progress',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log the resolution
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'projects',
            'module_id' => $taskId,
            'action' => 'blocker_resolved',
            'description' => 'Blocker resolved for task ID ' . $taskId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSuccess('Blocker resolved', ['task_id' => $taskId]);
    }

    /** 
     * Personal notes per user/project
     */
    public function notes($projectId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewProjects($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access notes.');
        }

        $userId = session()->get('user_id');
        $notes = [];
        $project = null;

        if ($projectId) {
            $project = $this->projectModel->find($projectId);
            if (!$project) {
                return redirect()->to('/admin/projects')->with('error', 'Project not found.');
            }

            // Check access
            if (!$auth->canViewProjectScoped($role, (int)session()->get('user_id'), (int)$projectId)) {
                return redirect()->to('/admin/projects')->with('error', 'You are not assigned to this project.');
            }

            // Get notes from activity log
            $activity = new \App\Models\ActivityModel();
            $notes = $activity->where('module', 'projects')
                ->where('module_id', $projectId)
                ->where('action', 'quick_capture_note')
                ->orderBy('created_at', 'DESC')
                ->limit(20)
                ->findAll();
        }

        return view('admin/team-pilot/notes', [
            'title' => 'Notes',
            'project' => $project,
            'notes' => $notes,
            'projectId' => $projectId,
            'canViewProjects' => $auth->canViewProjects($role),
        ]);
    }

    /**
     * Save a personal project note (POST from the Notes page form)
     */
    public function saveNote($projectId = null)
    {
        $role = session()->get('user_role');
        if (!$this->auth->canViewProjects($role)) {
            return $this->jsonError('You do not have permission to save notes.');
        }

        $projectId = (int) ($projectId ?: $this->request->getPost('project_id'));
        if (!$projectId) {
            return $this->jsonError('Project context is required.');
        }

        $text = trim((string) $this->request->getPost('description'));
        if ($text === '') {
            return $this->jsonError('Note text is required.');
        }

        $userName = session()->get('user_name') ?? 'User';
        $this->activityModel->insert([
            'user_id'    => session()->get('user_id'),
            'module'     => 'projects',
            'module_id'  => $projectId,
            'action'     => 'quick_capture_note',
            'description' => $userName . ' quick capture note: ' . substr($text, 0, 500),
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/team-pilot/notes/' . $projectId)
            ->with('message', 'Note added successfully.');
    }

    /**
     * Weekly reports - aggregate team progress
     */
    public function weeklyReport($projectId = null, $weeks = 1)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewProjects($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to generate weekly reports.');
        }

        $userId = session()->get('user_id');
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-' . (7 * max(1, (int) $weeks)) . ' days'));

        $project = null;
        $teamMembers = [];
        $standUps = [];
        $taskStats = [];

        if ($projectId) {
            $project = $this->projectModel->find($projectId);

            if (!$auth->canViewProjectScoped($role, (int)session()->get('user_id'), (int)$projectId)) {
                return redirect()->to('/admin/projects')->with('error', 'You are not assigned to this project.');
            }

            $teamMembers = $this->projectMemberModel->getByProject((int)$projectId);

            // Get stand-ups in the period
            $activity = new \App\Models\ActivityModel();
            $standUps = $activity->where('module', 'projects')
                ->where('module_id', $projectId)
                ->where('action', 'stand_up')
                ->where('created_at >=', $startDate)
                ->where('created_at <=', $endDate)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            // Get task statistics (always keyed by project id for the view)
            $taskStats[(int) $projectId] = $this->taskModel->getProgressWithHours((int)$projectId);
        } else {
            // Global weekly report - get all accessible projects
            $visible = $this->auth->getVisibleProjectIds($role, (int)session()->get('user_id'));
            if ($visible !== null && count($visible) > 0) {
                // Get stats for each visible project
                foreach ($visible as $pid) {
                    $projStats = $this->taskModel->getProgressWithHours($pid);
                    if ($projStats) {
                        $taskStats[$pid] = $projStats;
                    }
                }
            }
        }

        // Project names for the view (no DB access from views)
        $projectNames = [];
        foreach (array_keys($taskStats) as $pid) {
            $proj = $this->projectModel->find((int) $pid);
            $projectNames[$pid] = $proj['name'] ?? ('Project ' . $pid);
        }

        return view('admin/team-pilot/weekly-report', [
            'title' => 'Weekly Report',
            'project' => $project,
            'weeks' => $weeks,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'teamMembers' => $teamMembers,
            'standUps' => $standUps,
            'taskStats' => $taskStats,
            'projectNames' => $projectNames,
            'canViewProjects' => $auth->canViewProjects($role),
        ]);
    }

    /** 
     * Team activity dashboard - aggregated activity across projects
     */
    public function teamActivity()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewProjects($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access team activity.');
        }

        $userId = session()->get('user_id');
        $days = (int)($this->request->getGet('days') ?? 7);

        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-' . $days . ' days'));

        $activities = \Config\Database::connect()->table('activities a')
            ->select('a.*, u.name AS user_name')
            ->join('users u', 'u.id = a.user_id', 'left')
            ->where('a.created_at >=', $startDate)
            ->where('a.created_at <=', $endDate)
            ->whereIn('a.module', ['projects', 'hrms', 'recruitment'])
            ->orderBy('a.created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        // Group by module
        $grouped = [];
        foreach ($activities as $a) {
            $m = $a['module'];
            if (!isset($grouped[$m])) {
                $grouped[$m] = [];
            }
            $grouped[$m][] = $a;
        }

        return view('admin/team-pilot/team-activity', [
            'title' => 'Team Activity',
            'activities' => $activities,
            'grouped' => $grouped,
            'days' => $days,
            'canViewProjects' => $auth->canViewProjects($role),
        ]);
    }
}