<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\UserModel;

/** Manage multiple portal users belonging to one client organization. */
class ClientUserController extends BaseController
{
    protected UserModel $userModel;
    protected ClientModel $clientModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->clientModel = new ClientModel();
    }

    private function canManage(): bool
    {
        return in_array((string) session()->get('user_role'), ['superadmin', 'admin', 'manager'], true);
    }

    public function index(int $clientId)
    {
        if (!$this->canManage()) return redirect()->to('admin/clients')->with('error', 'Access denied.');
        $client = $this->clientModel->find($clientId);
        if (!$client) return redirect()->to('admin/clients')->with('error', 'Client not found.');

        return view('admin/clients/users', [
            'title' => 'Client Users',
            'client' => $client,
            'users' => $this->userModel->findAllByClientId($clientId),
        ]);
    }

    public function store(int $clientId)
    {
        if (!$this->canManage()) return redirect()->back()->with('error', 'Access denied.');
        if (!$this->clientModel->find($clientId)) return redirect()->to('admin/clients')->with('error', 'Client not found.');

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
            'client_role' => 'required|in_list[owner,manager,member,viewer]',
        ];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());

        try {
            $this->userModel->createClientUser(
                $clientId,
                (string) $this->request->getPost('name'),
                (string) $this->request->getPost('email'),
                (string) $this->request->getPost('password'),
                (string) $this->request->getPost('client_role')
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $this->logActivity('clients', $clientId, 'client_user_added', 'Added portal user: ' . $this->request->getPost('email'));
        return redirect()->to('admin/clients/' . $clientId . '/users')->with('success', 'Client user added successfully.');
    }

    public function update(int $clientId, int $userId)
    {
        if (!$this->canManage()) return redirect()->back()->with('error', 'Access denied.');
        $user = $this->userModel->where('id', $userId)->where('client_id', $clientId)->where('role', 'client')->first();
        if (!$user) return redirect()->to('admin/clients/' . $clientId . '/users')->with('error', 'Client user not found.');

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

        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'email' => $email,
            'client_role' => (string) $this->request->getPost('client_role'),
            'is_active' => $this->request->getPost('is_active') === null ? (int) $user['is_active'] : (int) $this->request->getPost('is_active'),
        ];
        if ($this->request->getPost('password')) $data['password'] = password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT);

        $this->userModel->update($userId, $data);
        $this->logActivity('clients', $clientId, 'client_user_updated', 'Updated portal user: ' . $email);
        return redirect()->to('admin/clients/' . $clientId . '/users')->with('success', 'Client user updated.');
    }

    public function toggle(int $clientId, int $userId)
    {
        if (!$this->canManage()) return $this->jsonError('Access denied.');
        $user = $this->userModel->where('id', $userId)->where('client_id', $clientId)->where('role', 'client')->first();
        if (!$user) return $this->jsonError('Client user not found.');

        $activeOwners = $this->userModel->where('client_id', $clientId)->where('role', 'client')->where('client_role', 'owner')->where('is_active', 1)->countAllResults();
        if ((int) $user['is_active'] === 1 && $user['client_role'] === 'owner' && $activeOwners <= 1) return $this->jsonError('At least one active client owner must remain.');

        $newStatus = (int) !$user['is_active'];
        $this->userModel->update($userId, ['is_active' => $newStatus]);
        $this->logActivity('clients', $clientId, 'client_user_status', ($newStatus ? 'Activated: ' : 'Deactivated: ') . $user['email']);
        return $this->jsonSuccess($newStatus ? 'Client user activated.' : 'Client user deactivated.');
    }

    public function delete(int $clientId, int $userId)
    {
        if (!$this->canManage()) return $this->jsonError('Access denied.');
        $user = $this->userModel->where('id', $userId)->where('client_id', $clientId)->where('role', 'client')->first();
        if (!$user) return $this->jsonError('Client user not found.');

        $activeOwners = $this->userModel->where('client_id', $clientId)->where('role', 'client')->where('client_role', 'owner')->where('is_active', 1)->countAllResults();
        if ($user['client_role'] === 'owner' && (int) $user['is_active'] === 1 && $activeOwners <= 1) return $this->jsonError('The last active client owner cannot be removed.');

        // Preserve the account and audit history; disable access instead of hard deleting.
        $this->userModel->update($userId, ['is_active' => 0]);
        $this->logActivity('clients', $clientId, 'client_user_removed', 'Removed portal user: ' . $user['email']);
        return $this->jsonSuccess('Client user removed.');
    }
}
