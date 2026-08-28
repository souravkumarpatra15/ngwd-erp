<?php
namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ModulePermissionModel;
use App\Services\PmsAuthorizationService;

/**
 * Lets a Client Owner manage the portal users belonging to their own
 * organization (spec section 26). Manager can view read-only. Every
 * query is hard-scoped to the logged-in user's own client_id — a client
 * user can never see or touch another client's team, regardless of what
 * ids are passed in.
 */
class ClientTeamController extends BaseController
{
    protected UserModel $userModel;
    protected PmsAuthorizationService $auth;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->auth = new PmsAuthorizationService();
    }

    protected function cid(): int { return (int) session()->get('client_id'); }
    protected function role(): string { return (string) session()->get('client_role'); }
    protected function isClientUser(): bool { return strtolower((string) session()->get('user_role')) === 'client' && $this->cid() > 0; }

    public function index()
    {
        if (!$this->isClientUser() || !$this->auth->clientCanViewUsers($this->role())) {
            return redirect()->to('portal/dashboard')->with('error', 'Access denied.');
        }
        $users = $this->userModel->findAllByClientId($this->cid());
        $mpm = new ModulePermissionModel();
        $extraModules = [];
        foreach ($users as $u) {
            $perms = $mpm->forUser((int) $u['id']);
            $extraModules[$u['id']] = [
                'invoices' => !empty($perms['invoices']['can_view']),
                'payments' => !empty($perms['payments']['can_view']),
            ];
        }
        return view('client/team/index', [
            'title'     => 'My Team',
            'users'     => $users,
            'canManage' => $this->auth->clientCanManageUsers($this->role()),
            'extraModules' => $extraModules,
        ]);
    }

    public function store()
    {
        if (!$this->isClientUser() || !$this->auth->clientCanManageUsers($this->role())) {
            return redirect()->back()->with('error', 'Only the account Owner can add team members.');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
            'client_role' => 'required|in_list[owner,manager,member,viewer]',
        ];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());

        try {
            $newUserId = $this->userModel->createClientUser(
                $this->cid(),
                (string) $this->request->getPost('name'),
                (string) $this->request->getPost('email'),
                (string) $this->request->getPost('password'),
                (string) $this->request->getPost('client_role')
            );
            $this->saveExtraModules((int) $newUserId);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to('portal/team')->with('success', 'Team member added.');
    }

    /** Grants explicit view-only access to invoices/payments beyond the member's role default, when checked. */
    private function saveExtraModules(int $userId): void
    {
        $checked = (array) $this->request->getPost('modules');
        $mpm = new ModulePermissionModel();
        foreach (['invoices', 'payments'] as $module) {
            $mpm->upsert($userId, $module, ['can_view' => in_array($module, $checked, true)]);
        }
    }

    public function update(int $userId)
    {
        if (!$this->isClientUser() || !$this->auth->clientCanManageUsers($this->role())) {
            return redirect()->back()->with('error', 'Only the account Owner can edit team members.');
        }
        $user = $this->userModel->where('id', $userId)->where('client_id', $this->cid())->where('role', 'client')->first();
        if (!$user) return redirect()->to('portal/team')->with('error', 'Team member not found.');

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'client_role' => 'required|in_list[owner,manager,member,viewer]',
        ];
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]|max_length[255]';
            $rules['password_confirm'] = 'matches[password]';
        }
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if ($this->userModel->where('email', $email)->where('id !=', $userId)->first()) {
            return redirect()->back()->withInput()->with('error', 'Email address is already registered.');
        }

        $newRole = (string) $this->request->getPost('client_role');
        if ($user['client_role'] === 'owner' && $newRole !== 'owner') {
            $activeOwners = $this->userModel->where('client_id', $this->cid())->where('role', 'client')->where('client_role', 'owner')->where('is_active', 1)->countAllResults();
            if ($activeOwners <= 1) return redirect()->back()->withInput()->with('error', 'At least one active Owner must remain on the account.');
        }

        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'email' => $email,
            'client_role' => $newRole,
        ];
        if ($this->request->getPost('password')) $data['password'] = password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT);

        $this->userModel->update($userId, $data);
        $this->saveExtraModules($userId);
        return redirect()->to('portal/team')->with('success', 'Team member updated.');
    }

    public function toggle(int $userId)
    {
        if (!$this->isClientUser() || !$this->auth->clientCanManageUsers($this->role())) {
            return $this->jsonError('Only the account Owner can change team member status.');
        }
        $user = $this->userModel->where('id', $userId)->where('client_id', $this->cid())->where('role', 'client')->first();
        if (!$user) return $this->jsonError('Team member not found.');

        $activeOwners = $this->userModel->where('client_id', $this->cid())->where('role', 'client')->where('client_role', 'owner')->where('is_active', 1)->countAllResults();
        if ((int) $user['is_active'] === 1 && $user['client_role'] === 'owner' && $activeOwners <= 1) {
            return $this->jsonError('At least one active Owner must remain on the account.');
        }

        $newStatus = (int) !$user['is_active'];
        $this->userModel->update($userId, ['is_active' => $newStatus]);
        return $this->jsonSuccess($newStatus ? 'Team member enabled.' : 'Team member disabled.');
    }
}
