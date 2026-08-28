<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ModulePermissionModel;
use App\Services\PmsAuthorizationService;

/** Superadmin/Admin only — fine-grained per-module CRUD overrides for any internal or client-portal user. */
class ModulePermissionController extends BaseController
{
    private const ALL_MODULES = [
        'dashboard' => 'Dashboard', 'projects' => 'Projects', 'tasks' => 'Tasks', 'kanban' => 'Kanban',
        'milestones' => 'Milestones', 'deliverables' => 'Deliverables', 'leads' => 'Leads (CRM)',
        'clients' => 'Clients', 'invoices' => 'Invoices', 'payments' => 'Payments', 'documents' => 'Documents',
        'tickets' => 'Support Tickets', 'marketing_leads' => 'Marketing Leads', 'domains' => 'Domains',
        'hostings' => 'Hosting', 'agreements' => 'Agreements', 'proposals' => 'Proposals', 'reports' => 'Reports',
        'client_team' => 'Client Team',
    ];

    private function isPrivileged(): bool
    {
        return in_array((string) session()->get('user_role'), ['superadmin', 'admin'], true);
    }

    public function edit($userId)
    {
        if (!$this->isPrivileged()) return redirect()->to('admin/dashboard')->with('error', 'Access denied.');
        $user = (new UserModel())->find((int) $userId);
        if (!$user) return redirect()->back()->with('error', 'User not found.');

        return view('admin/users/permissions', [
            'title'    => 'Module Permissions — ' . $user['name'],
            'user'     => $user,
            'modules'  => self::ALL_MODULES,
            'current'  => (new ModulePermissionModel())->forUser((int) $userId),
        ]);
    }

    public function update($userId)
    {
        if (!$this->isPrivileged()) return redirect()->to('admin/dashboard')->with('error', 'Access denied.');
        $user = (new UserModel())->find((int) $userId);
        if (!$user) return redirect()->back()->with('error', 'User not found.');

        $mpm = new ModulePermissionModel();
        $posted = $this->request->getPost('perms') ?? [];
        foreach (self::ALL_MODULES as $key => $label) {
            $row = $posted[$key] ?? [];
            $mpm->upsert((int) $userId, $key, [
                'can_view'   => !empty($row['view']),
                'can_create' => !empty($row['create']),
                'can_edit'   => !empty($row['edit']),
                'can_delete' => !empty($row['delete']),
            ]);
        }
        $this->logActivity('users', $userId, 'permissions_updated', 'Module permissions updated for ' . $user['email']);
        return redirect()->to('admin/users/' . $userId . '/permissions')->with('success', 'Permissions saved.');
    }
}
