<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Models\LeadModel;
use App\Models\ClientModel;
use App\Models\ProjectModel;
use App\Models\PaymentModel;
use App\Models\InvoiceModel;
use App\Models\DomainModel;
use App\Models\HostingModel;
use App\Models\ProposalModel;
use App\Models\TaskModel;
use App\Models\OnboardingChecklistsModel;
use App\Models\ApplicationStatusHistoryModel;
use App\Models\NotificationModel;
use App\Services\PmsAuthorizationService;

class DashboardController extends BaseController
{
    public function index()
    {
        $role = session()->get('user_role');
        $userId = (int) session()->get('user_id');
        $auth = new PmsAuthorizationService();
        $canViewFinancials = $auth->canViewFinancials((string) session()->get('user_role'), (string) session()->get('department'));

        // Route to role-specific dashboard data
        $roleDashboard = $this->getRoleDashboardData($role, $userId, $auth);

        // Common data for all dashboards
        $commonData = $this->getCommonDashboardData($role, $userId, $auth, $canViewFinancials);

        // Merge role-specific with common data
        $data = array_merge($roleDashboard, $commonData);

        // The dashboard view reads the camelCase key; keep both spellings so
        // older partials that use the snake_case key keep working too.
        $data['canViewFinancials'] = $canViewFinancials;
        $data['can_view_financials'] = $canViewFinancials;

        // Add role-specific metadata
        $data['user_role'] = $role;
        $data['role_dashboard'] = $role;

        return view('admin/dashboard/index', $data);
    }

    /**
     * Get role-specific dashboard data
     */
    protected function getRoleDashboardData(string $role, int $userId, PmsAuthorizationService $auth): array
    {
        $data = [];

        // Super Admin - full overview
        if ($role === 'superadmin') {
            $data = $this->superAdminDashboard($auth);
        }
        // Founder - business overview
        elseif ($role === 'founder') {
            $data = $this->founderDashboard($auth);
        }
        // HR Manager - HR-focused data
        elseif ($role === 'hr_manager') {
            $data = $this->hrManagerDashboard($auth);
        }
        // Recruitment Manager - ATS-focused data
        elseif ($role === 'recruitment_manager') {
            $data = $this->recruitmentManagerDashboard($auth);
        }
        // Project Manager - project-focused data
        elseif ($role === 'project_manager') {
            $data = $this->projectManagerDashboard($auth, $userId);
        }
        // Team Leader - team-focused data
        elseif ($role === 'team_leader') {
            $data = $this->teamLeaderDashboard($auth, $userId);
        }
        // Permanent Employee - employee-focused data
        elseif ($role === 'permanent_employee') {
            $data = $this->employeeDashboard($auth, $userId);
        }
        // Intern - intern-focused data
        elseif ($role === 'intern') {
            $data = $this->internDashboard($auth, $userId);
        }
        // Finance Manager - finance-focused data
        elseif ($role === 'finance_manager') {
            $data = $this->financeManagerDashboard($auth);
        }

        return $data;
    }

    /**
     * Super Admin dashboard - complete overview
     */
    protected function superAdminDashboard(PmsAuthorizationService $auth): array
    {
        $employeeModel = new EmployeeModel();
        $userModel = new UserModel();
        $projectModel = new ProjectModel();
        $taskModel = new TaskModel();
        $notificationModel = new NotificationModel();
        $leadModel = new LeadModel();
        $clientModel = new ClientModel();
        $paymentModel = new PaymentModel();
        $invoiceModel = new InvoiceModel();
        $domainModel = new DomainModel();
        $hostingModel = new HostingModel();
        $proposalModel = new ProposalModel();

        $visible = $auth->getVisibleProjectIds('superadmin', 0);

        return [
            'total_employees'      => $employeeModel->where('employment_status', 'active')->countAll(),
            'total_users'          => $userModel->where('is_active', 1)->countAll(),
            'total_departments'    => (int) ($employeeModel->db->table('employees')->select('COUNT(DISTINCT department_id) as cnt')->get()->getRowArray()['cnt'] ?? 0),
            'total_designations'   => (int) ($employeeModel->db->table('employees')->select('COUNT(DISTINCT designation_id) as cnt')->get()->getRowArray()['cnt'] ?? 0),
            'active_employees'     => $employeeModel->where('employment_status', 'active')->countAll(),
            'interns'              => $employeeModel->where('employment_type', 'internship')->countAll(),
            'total_leads'          => $leadModel->countAll(),
            'total_clients'        => $clientModel->countAll(),
            'active_projects'      => $visible === null ? $projectModel->where('status', 'development')->countAllResults() : $projectModel->whereIn('id', $visible)->where('status', 'development')->countAllResults(),
            'completed_projects'   => $visible === null ? $projectModel->where('status', 'completed')->countAllResults() : $projectModel->whereIn('id', $visible)->where('status', 'completed')->countAllResults(),
            'pending_proposals'    => $proposalModel->where('status', 'sent')->countAllResults(),
            'unread_notifications' => $notificationModel->getUnreadCount(0), // superadmin sees all
            'todays_followups'     => $leadModel->getTodaysFollowUps(),
            'upcoming_renewals'    => array_merge(
                $domainModel->getUpcomingRenewals(30),
                $hostingModel->getUpcomingRenewals(30)
            ),
            'recent_payments'      => $auth->canViewFinancials('superadmin', '') ? $paymentModel->getRecent(5) : [],
            'monthly_revenue'      => $auth->canViewFinancials('superadmin', '') ? $paymentModel->getMonthlyRevenueByCurrency() : [],
            'project_status_chart' => $projectModel->getStatusChart(),
            'my_work'              => (new TaskModel())->getMyWork(0), // superadmin sees all work
        ];
    }

    /**
     * Founder dashboard - business overview
     */
    protected function founderDashboard(PmsAuthorizationService $auth): array
    {
        $employeeModel = new EmployeeModel();
        $projectModel = new ProjectModel();
        $taskModel = new TaskModel();
        $notificationModel = new NotificationModel();
        $leadModel = new LeadModel();
        $clientModel = new ClientModel();
        $paymentModel = new PaymentModel();
        $invoiceModel = new InvoiceModel();

        return [
            'total_employees'      => $employeeModel->countAll(),
            'active_employees'     => $employeeModel->where('employment_status', 'active')->countAll(),
            'total_clients'        => $clientModel->countAll(),
            'total_leads'          => $leadModel->countAll(),
            'active_projects'      => $projectModel->where('status', 'development')->countAllResults(),
            'completed_projects'   => $projectModel->where('status', 'completed')->countAllResults(),
            'pending_proposals'    => $proposalModel->where('status', 'sent')->countAllResults(),
            'unread_notifications' => $notificationModel->getUnreadCount($userId ?? 0),
            'todays_followups'     => $leadModel->getTodaysFollowUps(),
            'recent_payments'      => $auth->canViewFinancials('founder', '') ? $paymentModel->getRecent(5) : [],
            'project_status_chart' => $projectModel->getStatusChart(),
        ];
    }

    /**
     * HR Manager dashboard - HR-focused data
     */
    protected function hrManagerDashboard(PmsAuthorizationService $auth): array
    {
        $employeeModel = new EmployeeModel();
        $userModel = new UserModel();
        $notificationModel = new NotificationModel();
        $onboardingChecklistsModel = new OnboardingChecklistsModel();
        $applicationStatusHistoryModel = new ApplicationStatusHistoryModel();

        // Count employees by role/type
        $totalEmployees = $employeeModel->countAll();
        $activeEmployees = $employeeModel->where('employment_status', 'active')->countAll();
        $interns = $employeeModel->where('employment_type', 'internship')->countAll();
        $permanent = $employeeModel->where('employment_type', 'permanent')->countAll();

        // Recent joins and conversions
        $recentJoins = 0;
        $recentConversions = 0;
        try {
            $recentJoins = $onboardingChecklistsModel->where('is_completed', 1)->countAll();
            $recentConversions = $applicationStatusHistoryModel->where('change_reason', 'Intern converted to permanent employee')->countAll();
        } catch (\Exception $e) {
            // Tables may not exist in all installations
        }

        return [
            'total_employees'        => $totalEmployees,
            'active_employees'       => $activeEmployees,
            'interns'                => $interns,
            'permanent_employees'    => $permanent,
            'recent_joins'           => $recentJoins,
            'recent_conversions'     => $recentConversions,
            'unread_notifications'   => $notificationModel->getUnreadCount($userId ?? 0),
            'open_positions'         => $this->getOpenPositionsCount(),
            'training_completion'    => $this->getTrainingCompletionRate(),
            'employee-turnover-rate' => $this->getTurnoverRate(),
        ];
    }

    private function getOpenPositionsCount(): int
    {
        $employeeModel = new EmployeeModel();
        // Count employees with active status but no designated permanent role
        // or those in internship period who haven't converted yet
        $active = $employeeModel->where('employment_status', 'active')->countAll();
        $permanent = $employeeModel->where('employment_type', 'permanent')->countAll();
        // Estimate open positions: active minus permanent, minimum 0
        return max(0, $active - $permanent);
    }

    private function getTrainingCompletionRate(): float
    {
        $employeeModel = new EmployeeModel();
        // Count employees who have completed training (indicated by onboarding checklists)
        try {
            $totalEmployees = $employeeModel->countAll();
            $completedTrainings = (new \App\Models\OnboardingChecklistsModel())->where('is_completed', 1)->countAll();
            if ($totalEmployees > 0) {
                return round(($completedTrainings / $totalEmployees) * 100, 0);
            }
        } catch (\Exception $e) {
            // Tables may not exist in all installations
        }
        return 0.0;
    }

    private function getTurnoverRate(): float
    {
        $employeeModel = new EmployeeModel();
        // Calculate turnover rate based on employees who have left (resigned/terminated)
        try {
            $totalEmployees = $employeeModel->countAll();
            $departedEmployees = $employeeModel->whereIn('employment_status', ['resigned', 'terminated', 'inactive'])->countAll();
            if ($totalEmployees > 0) {
                return round(($departedEmployees / $totalEmployees) * 100, 0);
            }
        } catch (\Exception $e) {
            // Tables may not exist in all installations
        }
        return 0.0;
    }

    /**
     * Recruitment Manager dashboard - ATS-focused data
     */
    protected function recruitmentManagerDashboard(PmsAuthorizationService $auth): array
    {
        $employeeModel = new EmployeeModel();
        $notificationModel = new NotificationModel();
        $applicationStatusHistoryModel = new ApplicationStatusHistoryModel();

        // Intern to permanent conversion rate
        try {
            $totalInterns = $employeeModel->where('employment_type', 'internship')->countAll();
            $totalConversions = $applicationStatusHistoryModel->where('change_reason', 'Intern converted to permanent employee')->countAll();
            if ($totalInterns > 0) {
                $data['conversion_rate'] = round(($totalConversions / $totalInterns) * 100, 0);
            }
        } catch (\Exception $e) {
            $data['conversion_rate'] = 0.0;
        }

        // Recruitment pipeline counts
        try {
            $data['open_interviews'] = (new \App\Models\ApplicationStatusHistoryModel())->where('change_reason', 'Interview scheduled')->countAll();
        } catch (\Exception $e) {
            $data['open_interviews'] = 0;
        }

        try {
            $data['pending_offers'] = (new \App\Models\ApplicationStatusHistoryModel())->where('change_reason', 'Offer generated')->countAll();
        } catch (\Exception $e) {
            $data['pending_offers'] = 0;
        }

        try {
            // Count applications created in the current month
            $data['applications_this_month'] = (new \App\Models\ApplicationStatusHistoryModel())->where('created_at >=', date('Y-m-d', strtotime('-1 month')))->countAll();
        } catch (\Exception $e) {
            $data['applications_this_month'] = 0;
        }

        return $data;
    }

    /**
     * Project Manager dashboard - project-focused data
     */
    protected function projectManagerDashboard(PmsAuthorizationService $auth, int $userId): array
    {
        $projectModel = new ProjectModel();
        $taskModel = new TaskModel();
        $notificationModel = new NotificationModel();
        $employeeModel = new EmployeeModel();

        // Get projects assigned to this PM
        $visible = $auth->getVisibleProjectIds($role = 'project_manager', $userId);
        $scope = fn($q) => $visible === null ? $q : $q->whereIn('id', $visible ?: [0]);

        $pmProjects = $projectModel->whereIn('id', array_keys($visible ?? []))->findAll();

        // Calculate project stats
        $totalProjects = count($pmProjects);
        $activeProjects = 0;
        $completedProjects = 0;

        foreach ($pmProjects as $p) {
            if ($p['status'] === 'development') $activeProjects++;
            if ($p['status'] === 'completed') $completedProjects++;
        }

        return [
            'total_projects'         => $totalProjects,
            'active_projects'        => $activeProjects,
            'completed_projects'     => $completedProjects,
            'unread_notifications'   => $notificationModel->getUnreadCount($userId),
            'my_work'                => (new TaskModel())->getMyWork($userId),
            'team_tasks'             => $this->getTeamTasksSummary($userId),
            'project_status_chart'   => $this->getProjectStatusChartByPM($userId),
            'overdue_tasks'          => (new TaskModel())->getOverdue(),
        ];
    }

    private function getTeamTasksSummary(int $userId): array
    {
        return ['total' => 0, 'completed' => 0, 'in_progress' => 0];
    }

    private function getProjectStatusChartByPM(int $userId): array
    {
        return ['development' => 0, 'testing' => 0, 'completed' => 0];
    }

    /**
     * Team Leader dashboard - team-focused data
     */
    protected function teamLeaderDashboard(PmsAuthorizationService $auth, int $userId): array
    {
        $projectModel = new ProjectModel();
        $taskModel = new TaskModel();
        $employeeModel = new EmployeeModel();
        $notificationModel = new NotificationModel();

        // Get projects where user is team leader
        $visible = $auth->getVisibleProjectIds($role = 'team_leader', $userId);
        $scope = fn($q) => $visible === null ? $q : $q->whereIn('id', $visible ?: [0]);

        $leaderProjects = $projectModel->whereIn('id', array_keys($visible ?? []))->findAll();

        $totalProjects = count($leaderProjects);
        $activeTasks = 0;
        $overdueTasks = 0;

        foreach ($leaderProjects as $p) {
            $tasks = $taskModel->where('project_id', $p['id'])->whereNotIn('status', ['done', 'completed', 'cancelled'])->findAll();
            $activeTasks += count($tasks);
            $overdueTasks += count(array_filter($tasks, fn($t) => strtotime($t['due_date'] ?? '') < strtotime(date('Y-m-d'))));
        }

        return [
            'total_team_members'     => $this->getTeamMemberCount($userId),
            'total_projects'         => $totalProjects,
            'active_tasks'           => $activeTasks,
            'overdue_tasks'          => $overdueTasks,
            'unread_notifications'   => $notificationModel->getUnreadCount($userId),
            'my_work'                => (new TaskModel())->getMyWork($userId),
            'team_activity_recent'   => $this->getRecentTeamActivity($userId),
        ];
    }

    private function getTeamMemberCount(int $userId): int
    {
        return 0;
    }

    private function getRecentTeamActivity(int $userId): array
    {
        return [];
    }

    /**
     * Permanent Employee dashboard - employee-focused data
     */
    protected function employeeDashboard(PmsAuthorizationService $auth, int $userId): array
    {
        $employeeModel = new EmployeeModel();
        $taskModel = new TaskModel();
        $notificationModel = new NotificationModel();
        $projectModel = new ProjectModel();

        // Get employee record for this user
        $employee = $employeeModel->where('user_id', $userId)->first();

        // Get assigned tasks
        $myTasks = $taskModel->getMyWork($userId);

        // Get employee's projects via team membership
        $employeeProjects = [];
        if ($employee && !empty($employee['department_id'])) {
            // Get projects in same department
            $employeeProjects = $projectModel->where('department_id', $employee['department_id'])
                ->limit(5)
                ->findAll();
        }

        return [
            'employee'               => $employee ? [
                'employee_id' => $employee['employee_id'],
                'name'        => $employee['name'] ?? '',
                'designation' => $employee['designation_id'] ? 'Assigned' : 'N/A',
                'department'  => $employee['department_id'] ? 'Assigned' : 'N/A',
                'joining_date'=> $employee['joining_date'] ? date('F j, Y', strtotime($employee['joining_date'])) : 'N/A',
            ] : null,
            'my_tasks'               => $myTasks,
            'my_projects'            => $employeeProjects,
            'unread_notifications'   => $notificationModel->getUnreadCount($userId),
            'pending_onboarding'     => $this->checkPendingOnboarding($userId),
            'upcoming_milestones'    => $this->getUpcomingMilestones($userId),
        ];
    }

    private function checkPendingOnboarding(int $userId): bool
    {
        return false;
    }

    private function getUpcomingMilestones(int $userId): array
    {
        return [];
    }

    /**
     * Intern dashboard - intern-focused data
     */
    protected function internDashboard(PmsAuthorizationService $auth, int $userId): array
    {
        $employeeModel = new EmployeeModel();
        $taskModel = new TaskModel();
        $notificationModel = new NotificationModel();
        $projectModel = new ProjectModel();
        $applicationStatusHistoryModel = new ApplicationStatusHistoryModel();

        // Get employee record (intern)
        $employee = $employeeModel->where('user_id', $userId)->where('employment_type', 'internship')->first();

        // Get assigned tasks
        $myTasks = $taskModel->getMyWork($userId);

        // Get application/status history
        $conversionProgress = 0;
        try {
            $totalConversions = $applicationStatusHistoryModel->where('change_reason', 'Intern converted to permanent employee')->countAll();
            $totalInterns = $employeeModel->where('employment_type', 'internship')->countAll();
            $conversionProgress = $totalInterns > 0 ? round(($totalConversions / $totalInterns) * 100, 0) : 0;
        } catch (\Exception $e) {
            $conversionProgress = 0;
        }

        return [
            'employee'               => $employee ? [
                'employee_id' => $employee['employee_id'],
                'name'        => $employee['name'] ?? '',
                'designation' => 'Intern',
                'department'  => 'Intern Programme',
                'joining_date'=> $employee['joining_date'] ? date('F j, Y', strtotime($employee['joining_date'])) : 'N/A',
            ] : null,
            'my_tasks'               => $myTasks,
            'internship_program'     => $this->getInternProgramDetails($userId),
            'conversion_progress'    => $conversionProgress,
            'unread_notifications'   => $notificationModel->getUnreadCount($userId),
            'upcoming_interviews'    => $this->getUpcomingInterviews($userId),
            'onboarding_status'      => $this->getOnboardingStatus($userId),
        ];
    }

    private function getInternProgramDetails(int $userId): array
    {
        return ['program' => 'NGWebD Internship', 'status' => 'active', 'progress' => 0];
    }

    private function getUpcomingInterviews(int $userId): array
    {
        return [];
    }

    private function getOnboardingStatus(int $userId): string
    {
        return 'not_started';
    }

    /**
     * Finance Manager dashboard - finance-focused data
     */
    protected function financeManagerDashboard(PmsAuthorizationService $auth): array
    {
        $paymentModel = new PaymentModel();
        $invoiceModel = new InvoiceModel();
        $employeeModel = new EmployeeModel();
        $notificationModel = new NotificationModel();
        $projectModel = new ProjectModel();

        return [
            'unread_notifications'   => $notificationModel->getUnreadCount($userId ?? 0),
            'total_revenue'          => $auth->canViewFinancials('finance_manager', '') ? $paymentModel->getMonthlyRevenueByCurrency() : [],
            'pending_invoices'       => $auth->canViewFinancials('finance_manager', '') ? $invoiceModel->where('status', '!=' , 'paid')->countAllResults() : 0,
            'overdue_invoices'       => $auth->canViewFinancials('finance_manager', '') ? $invoiceModel->where('due_date <', date('Y-m-d'))->where('status', '!=' , 'paid')->countAllResults() : 0,
            'payroll_processing'     => $this->getPayrollProcessingStatus(),
            'salary_summary'         => $this->getSalarySummary(),
            'expense_breakdown'      => $this->getExpenseBreakdown(),
            'budget_vs_actual'       => $this->getBudgetVsActual(),
        ];
    }

    private function getPayrollProcessingStatus(): array
    {
        return ['is_processing' => false, 'last_processed' => null];
    }

    private function getSalarySummary(): array
    {
        return ['total_salary' => 0, 'employee_count' => 0];
    }

    private function getExpenseBreakdown(): array
    {
        return ['categories' => [], 'amounts' => []];
    }

    private function getBudgetVsActual(): array
    {
        return ['budget' => 0, 'actual' => 0, 'variance' => 0];
    }

    /**
     * Get common dashboard data applicable to all roles
     */
    protected function getCommonDashboardData(string $role, int $userId, PmsAuthorizationService $auth, bool $canViewFinancials): array
    {
        $projectModel = new ProjectModel();
        $taskModel = new TaskModel();
        $leadModel = new LeadModel();
        $clientModel = new ClientModel();
        $notificationModel = new NotificationModel();
        $domainModel = new DomainModel();
        $hostingModel = new HostingModel();
        $paymentModel = new PaymentModel();

        $visible = $auth->getVisibleProjectIds($role, $userId);
        $scope = fn($q) => $visible === null ? $q : $q->whereIn('id', $visible ?: [0]);

        return [
            'can_view_financials' => $canViewFinancials,
            'total_leads'         => $leadModel->countAll(),
            'total_clients'       => $clientModel->countAll(),
            'active_projects'     => $visible === null ? $projectModel->where('status', 'development')->countAllResults() : $projectModel->whereIn('id', $visible)->where('status', 'development')->countAllResults(),
            'completed_projects'  => $visible === null ? $projectModel->where('status', 'completed')->countAllResults() : $projectModel->whereIn('id', $visible)->where('status', 'completed')->countAllResults(),
            'domain_renewals'     => $domainModel->getExpiringCount(30),
            'hosting_renewals'    => $hostingModel->getExpiringCount(30),
            'todays_followups'    => $leadModel->getTodaysFollowUps(),
            'upcoming_renewals'   => array_merge(
                $domainModel->getUpcomingRenewals(30),
                $hostingModel->getUpcomingRenewals(30)
            ),
            'recent_payments'     => $canViewFinancials ? $paymentModel->getRecent(5) : [],
            'monthly_revenue'     => $canViewFinancials ? $paymentModel->getMonthlyRevenueByCurrency() : [],
            'monthly_revenue_chart' => $canViewFinancials ? $paymentModel->getMonthlyRevenueChart() : [],
            'project_status_chart' => $projectModel->getStatusChart(),
            'lead_conversion_chart' => $leadModel->getConversionChart(),
            'my_work'             => (new TaskModel())->getMyWork($userId),
            'notifications'       => (new NotificationModel())->getUserNotifications($userId, 10),
            'unread_count'        => $notificationModel->getUnreadCount($userId),
        ];
    }

    // ── NOTIFICATIONS ─────────────────────────────────────────
    // Route: GET admin/notifications
    public function notifications()
    {
        $userId = (int) session()->get('user_id');
        $nm = new NotificationModel();
        return view('admin/notifications/index', [
            'title'        => 'Notifications',
            'notifications' => $nm->getUserNotifications($userId, 100),
            'unread_count' => $nm->getUnreadCount($userId),
        ]);
    }

    // Route: GET admin/notifications/recent (bell dropdown polling)
    public function notificationsRecent()
    {
        $nm = new NotificationModel();
        return $this->response->setJSON([
            'notifications' => $nm->getUserNotifications((int) session()->get('user_id'), 8),
            'unread'        => $nm->getUnreadCount((int) session()->get('user_id')),
        ]);
    }

    // Route: POST admin/notifications/read/(:num)
    public function markRead($id = null)
    {
        $userId = (int) session()->get('user_id');
        if (!(new NotificationModel())->markRead((int) $id, $userId)) {
            return $this->jsonError('Notification not found.');
        }
        return $this->jsonSuccess('Marked as read');
    }

    // Route: POST admin/notifications/read-all
    public function markAllRead()
    {
        (new NotificationModel())->markAllRead((int) session()->get('user_id'));
        return $this->jsonSuccess('All marked as read');
    }

    // ── AJAX dropdowns ────────────────────────────────────────
    // Route: GET admin/ajax/clients (client select dropdowns)
    public function ajaxClients()
    {
        $clients = (new ClientModel())
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $clients]);
    }

    // Route: GET admin/ajax/projects/(:num) (project select dropdowns)
    public function ajaxProjects($clientId = null)
    {
        $projectModel = new ProjectModel();
        if ($clientId) {
            $projectModel->where('client_id', (int) $clientId);
        }
        $projects = $projectModel
            ->select('id, name, client_id, status')
            ->orderBy('name', 'ASC')
            ->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $projects]);
    }
}