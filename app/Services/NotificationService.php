<?php
namespace App\Services;

use App\Models\NotificationModel;

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
     */
    public function create(
        int    $userId,
        string $type,
        string $title,
        string $message      = '',
        int    $referenceId  = 0,
        string $referenceType = ''
    ): void {
        // If userId is 0, notify all superadmin / admin users
        if ($userId === 0) {
            $db = \Config\Database::connect();
            $admins = $db->table('users')
                ->whereIn('role', ['superadmin', 'admin'])
                ->where('is_active', 1)
                ->get()->getResultArray();

            foreach ($admins as $admin) {
                $this->insertNotification(
                    (int) $admin['id'], $type, $title, $message, $referenceId, $referenceType
                );
            }
            return;
        }

        $this->insertNotification($userId, $type, $title, $message, $referenceId, $referenceType);
    }

    private function insertNotification(
        int $userId, string $type, string $title,
        string $message, int $referenceId, string $referenceType
    ): void {
        $this->nm->insert([
            'user_id'        => $userId,
            'type'           => $type,
            'title'          => $title,
            'message'        => $message,
            'reference_id'   => $referenceId ?: null,
            'reference_type' => $referenceType ?: null,
            'is_read'        => 0,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
