<?php
namespace App\Services;

use App\Models\NotificationModel;
use Config\Database;

class NotificationService
{
    protected NotificationModel $nm;

    public function __construct()
    {
        $this->nm = new NotificationModel();
    }

    /**
     * Create a notification for a specific user.
     *
     * @param int    $userId       The user to notify. Pass 0 to notify all superadmins.
     * @param string $type         Notification type slug (e.g. 'new_ticket', 'follow_up_due')
     * @param string $title        Short title shown in the bell dropdown
     * @param string $message      Longer description
     * @param int    $referenceId  ID of the related record (lead, ticket, etc.)
     * @param string $referenceType  Module name ('lead', 'ticket', etc.)
     * @param bool   $preventDuplicate  If true, skip if similar notification exists
     * @return bool|int Returns notification ID on success, false on duplicate prevention
     */
    public function create(
        int    $userId,
        string $type,
        string $title,
        string $message      = '',
        int    $referenceId  = 0,
        string $referenceType = '',
        bool   $preventDuplicate = false
    ): int|false {
        // If userId is 0, notify all superadmin / admin users
        if ($userId === 0) {
            $db = \Config\Database::connect();
            $admins = $db->table('users')
                ->whereIn('role', ['superadmin', 'admin'])
                ->where('is_active', 1)
                ->get()->getResultArray();

            foreach ($admins as $admin) {
                $this->create(
                    (int) $admin['id'],
                    $type,
                    $title,
                    $message,
                    $referenceId,
                    $referenceType,
                    $preventDuplicate
                );
            }
            return true;
        }

        // Prevent duplicate notifications
        if ($preventDuplicate) {
            $existing = $this->nm->where('user_id', $userId)
                ->where('type', $type)
                ->where('reference_id', $referenceId)
                ->where('is_read', 0)
                ->first();

            if ($existing) {
                // Notification already exists and is unread - update the message if changed
                $this->nm->update($existing['id'], [
                    'title' => $title,
                    'message' => $message,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                return $existing['id'];
            }
        }

        return $this->nm->insert([
            'user_id'        => $userId,
            'type'           => $type,
            'title'          => $title,
            'message'        => $message,
            'reference_id'   => $referenceId ?: null,
            'reference_type' => $referenceType ?: null,
            'is_read'        => 0,
            'read_at'        => null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Notify a client's portal login (resolves client_id -> their users.id).
     * No-op if that client has no portal login yet.
     */
    public function createForClient(
        int    $clientId,
        string $type,
        string $title,
        string $message      = '',
        int    $referenceId  = 0,
        string $referenceType = '',
        bool   $preventDuplicate = false
    ): void {
        $db   = \Config\Database::connect();
        $user = $db->table('users')->where('client_id', $clientId)->where('role', 'client')->get()->getRowArray();
        if ($user) {
            $this->create(
                (int) $user['id'],
                $type,
                $title,
                $message,
                $referenceId,
                $referenceType,
                $preventDuplicate
            );
        }
    }

    /**
     * Notify about task assignment.
     * Prevents duplicate assignment notifications.
     */
    public function notifyTaskAssignment(int $userId, int $taskId, string $taskTitle, int $assignedBy = 0): bool
    {
        $refType = 'task';
        $refId = $taskId;

        // Check for existing unread task assignment notification
        $existing = $this->nm->where('user_id', $userId)
            ->where('type', 'task_assignment')
            ->where('reference_id', $taskId)
            ->where('is_read', 0)
            ->first();

        if ($existing) {
            // Update existing notification with new info if needed
            $this->nm->update($existing['id'], [
                'title' => 'Task Assignment Updated',
                'message' => 'Task: ' . $taskTitle . ' has been newly assigned to you.',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            return true;
        }

        return $this->create(
            $userId,
            'task_assignment',
            'Task Assigned',
            'You have been assigned task: ' . $taskTitle . '',
            $taskId,
            $refType
        );
    }

    /**
     * Notify about interview scheduling.
     */
    public function notifyInterviewSchedule(int $userId, int $applicationId, string $candidateName, string $interviewTime): bool
    {
        $refType = 'interview';
        $refId = $applicationId;

        return $this->create(
            $userId,
            'interview_schedule',
            'Interview Scheduled',
            'Interview scheduled for candidate: ' . $candidateName . ' at ' . $interviewTime,
            $applicationId,
            $refType
        );
    }

    /**
     * Notify about application status update.
     */
    public function notifyApplicationStatus(int $userId, int $applicationId, string $newStatus, string $previousStatus): bool
    {
        $refType = 'application';
        $refId = $applicationId;

        $statusDisplay = ucfirst($newStatus);
        $message = 'Application status updated from ' . ucfirst($previousStatus) . ' to ' . $statusDisplay;

        return $this->create(
            $userId,
            'application_status',
            'Application Status Update',
            $message,
            $applicationId,
            $refType
        );
    }

    /**
     * Notify about offer letter generation.
     */
    public function notifyOfferGenerated(int $userId, int $offerId, string $candidateName): bool
    {
        $refType = 'offer';
        $refId = $offerId;

        return $this->create(
            $userId,
            'offer_generated',
            'Offer Letter Generated',
            'Offer letter generated for candidate: ' . $candidateName,
            $offerId,
            $refType
        );
    }

    /**
     * Notify about offer acceptance.
     */
    public function notifyOfferAccepted(int $userId, int $offerId, string $candidateName): bool
    {
        $refType = 'offer';
        $refId = $offerId;

        return $this->create(
            $userId,
            'offer_accepted',
            'Offer Accepted',
            'Offer accepted by: ' . $candidateName,
            $offerId,
            $refType
        );
    }

    /**
     * Notify about joining confirmation.
     */
    public function notifyJoiningConfirmed(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'join_confirmed',
            'Joining Confirmed',
            'Employee joining confirmed: ' . $employeeName,
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about onboarding milestone.
     */
    public function notifyOnboardingMilestone(int $userId, int $employeeId, string $milestone, string $details = ''): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        $title = 'Onboarding Milestone';
        $message = 'Milestone reached: ' . $milestone;

        if (!empty($details)) {
            $message .= ' - ' . $details;
        }

        return $this->create(
            $userId,
            'onboarding_milestone',
            $title,
            $message,
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about project deadline.
     */
    public function notifyProjectDeadline(int $userId, int $projectId, string $deadline, string $taskTitle): bool
    {
        $refType = 'project';
        $refId = $projectId;

        return $this->create(
            $userId,
            'project_deadline',
            'Project Deadline',
            'Deadline approaching: ' . $taskTitle . ' for project ID ' . $projectId . ' by ' . $deadline,
            $projectId,
            $refType
        );
    }

    /**
     * Notify about attendance check-in.
     */
    public function notifyCheckIn(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'check_in',
            'Attendance Check-In',
            $employeeName . ' has checked in.',
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about missing check-out reminder.
     */
    public function notifyMissingCheckOut(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'missing_check_out',
            'Missing Check-Out Reminder',
            'Reminder: ' . $employeeName . ' has not checked out yet.',
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about attendance correction request.
     */
    public function notifyCorrectionRequest(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'attendance';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'correction_request',
            'Attendance Correction Request',
            'Attendance correction requested for ' . $employeeName,
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about attendance correction approval.
     */
    public function notifyCorrectionApproval(int $userId, int $employeeId, string $employeeName, bool $approved): bool
    {
        $refType = 'attendance';
        $refId = $employeeId;

        $status = $approved ? 'approved' : 'rejected';
        $title = $approved ? 'Attendance Correction Approved' : 'Attendance Correction Rejected';

        return $this->create(
            $userId,
            'correction_approval',
            $title,
            $employeeName . ' ' . ($approved ? 'attendance correction was approved' : 'attendance correction was rejected'),
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about monthly evaluation reminder.
     */
    public function notifyEvaluationReminder(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'evaluation_reminder',
            'Monthly Evaluation Reminder',
            'Monthly evaluation pending for ' . $employeeName,
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about internship completion approaching.
     */
    public function notifyCompletionApproaching(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'completion_approaching',
            'Internship Completion Approaching',
            'Internship nearing completion for ' . $employeeName,
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about final evaluation pending.
     */
    public function notifyFinalEvaluationPending(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'final_evaluation_pending',
            'Final Evaluation Pending',
            'Final evaluation pending for ' . $employeeName,
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about certificate approved.
     */
    public function notifyCertificateApproved(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'certificate';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'certificate_approved',
            'Certificate Approved',
            'Internship certificate approved for ' . $employeeName,
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about certificate generated.
     */
    public function notifyCertificateGenerated(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'certificate';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'certificate_generated',
            'Certificate Generated',
            'Internship certificate generated for ' . $employeeName,
            $employeeId,
            $refType
        );
    }
    public function notifyPayrollProcessing(int $userId, int $payrollRecordId, string $period): bool
    {
        $refType = 'payroll';
        $refId = $payrollRecordId;

        return $this->create(
            $userId,
            'payroll_processing',
            'Payroll Processing',
            'Payroll period ' . $period . ' is now processing.',
            $payrollRecordId,
            $refType
        );
    }

    /**
     * Notify about payroll approval.
     */
    public function notifyPayrollApproval(int $userId, int $payrollRecordId, string $approvedBy): bool
    {
        $refType = 'payroll';
        $refId = $payrollRecordId;

        return $this->create(
            $userId,
            'payroll_approved',
            'Payroll Approved',
            'Payroll period approved by ' . $approvedBy,
            $payrollRecordId,
            $refType
        );
    }

    /**
     * Notify about payroll payment.
     */
    public function notifyPayrollPaid(int $userId, int $payrollRecordId, string $paymentMethod): bool
    {
        $refType = 'payroll';
        $refId = $payrollRecordId;

        return $this->create(
            $userId,
            'payroll_paid',
            'Payroll Paid',
            'Payroll has been paid via ' . $paymentMethod,
            $payrollRecordId,
            $refType
        );
    }

    /**
     * Notify about employee joining.
     */
    public function notifyEmployeeJoined(int $userId, int $employeeId, string $employeeName): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'employee_joined',
            'Employee Joined',
            $employeeName . ' has joined the company.',
            $employeeId,
            $refType
        );
    }

    /**
     * Notify about employee conversion (intern to permanent).
     */
    public function notifyEmployeeConversion(int $userId, int $employeeId, string $previousRole): bool
    {
        $refType = 'employee';
        $refId = $employeeId;

        return $this->create(
            $userId,
            'employee_conversion',
            'Employee Conversion',
            'Employee converted from ' . $previousRole . ' to permanent employee.',
            $employeeId,
            $refType
        );
    }

    /**
     * Notify multiple users (e.g., team members, role-based).
     *
     * @param array|int $userIds       Single user ID or array of user IDs
     * @param string    $type          Notification type slug
     * @param string $title            Short title
     * @param string $message          Longer description
     * @param int       $referenceId   ID of the related record
     * @param string    $referenceType Module name
     * @param bool      $preventDuplicate If true, each user checks for duplicates individually
     * @return array|int List of notification IDs created, or single ID
     */
    public function notifyMany(
        array|int $userIds,
        string $type,
        string $title,
        string $message      = '',
        int    $referenceId  = 0,
        string $referenceType = '',
        bool   $preventDuplicate = false
    ): array|int {
        $ids = array_unique(array_map('intval', is_array($userIds) ? $userIds : [$userIds]));

        $createdIds = [];
        foreach ($ids as $userId) {
            $result = $this->create(
                $userId,
                $type,
                $title,
                $message,
                $referenceId,
                $referenceType,
                $preventDuplicate
            );

            if (is_int($result)) {
                $createdIds[] = $result;
            }
        }

        return count($createdIds) === 1 ? $createdIds[0] : $createdIds;
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->nm->where('user_id', $userId)->where('is_read', 0)->countAllResults();
    }

    /**
     * Get recent notifications for a user.
     */
    public function getRecentNotifications(int $userId, int $limit = 20): array
    {
        return $this->nm->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(int $id, int $userId): bool
    {
        return $this->nm->where('id', $id)
            ->where('user_id', $userId)
            ->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllRead(int $userId): void
    {
        $this->nm->where('user_id', $userId)
            ->where('is_read', 0)
            ->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    /**
     * Check if a notification type+reference already exists as unread for a user.
     */
    private function hasUnreadDuplicate(int $userId, string $type, int $referenceId): bool
    {
        return $this->nm->where('user_id', $userId)
            ->where('type', $type)
            ->where('reference_id', $referenceId)
            ->where('is_read', 0)
            ->countAllResults() > 0;
    }
}