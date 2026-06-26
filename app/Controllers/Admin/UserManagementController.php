<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * UserManagementController
 * Full CRUD for admin users (superadmin, admin, manager).
 * Route prefix: admin/users
 */
class UserManagementController extends BaseController
{
    protected UserModel $um;

    public function __construct()
    {
        $this->um = new UserModel();
    }

    // GET admin/users
    public function index()
    {
        $users = $this->um
            ->whereIn('role', ['superadmin', 'admin', 'manager'])
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('admin/users/index', [
            'title' => 'User Management',
            'users' => $users,
        ]);
    }

    // GET admin/users/create
    public function create()
    {
        return view('admin/users/create', [
            'title' => 'Add User',
            'roles' => ['superadmin' => 'Super Admin', 'admin' => 'Admin', 'manager' => 'Manager'],
        ]);
    }

    // POST admin/users/store
    public function store()
    {
        $rules = [
            'name'             => 'required|min_length[2]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'role'             => 'required|in_list[superadmin,admin,manager]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->um->insert([
            'name'       => $this->request->getPost('name'),
            'email'      => $this->request->getPost('email'),
            'role'       => $this->request->getPost('role'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'is_active'  => (int) $this->request->getPost('is_active'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('users', $this->um->getInsertID(), 'create', 'Created user: ' . $this->request->getPost('email'));

        return redirect()->to('admin/users')->with('success', 'User created successfully!');
    }

    // GET admin/users/edit/(:num)
    public function edit($id)
    {
        $user = $this->um->find($id);
        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }

        return view('admin/users/edit', [
            'title' => 'Edit User',
            'user'  => $user,
            'roles' => ['superadmin' => 'Super Admin', 'admin' => 'Admin', 'manager' => 'Manager'],
        ]);
    }

    // POST admin/users/update/(:num)
    public function update($id)
    {
        $user = $this->um->find($id);
        if (!$user) {
            return redirect()->to('admin/users')->with('error', 'User not found.');
        }

        $emailRule = ($user['email'] === $this->request->getPost('email'))
            ? 'required|valid_email'
            : 'required|valid_email|is_unique[users.email]';

        $rules = [
            'name'  => 'required|min_length[2]|max_length[100]',
            'email' => $emailRule,
            'role'  => 'required|in_list[superadmin,admin,manager]',
        ];

        if ($this->request->getPost('password')) {
            $rules['password']         = 'min_length[8]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'       => $this->request->getPost('name'),
            'email'      => $this->request->getPost('email'),
            'role'       => $this->request->getPost('role'),
            'is_active'  => (int) $this->request->getPost('is_active'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);
        }

        $this->um->update($id, $data);
        $this->logActivity('users', $id, 'update', 'Updated user: ' . $data['email']);

        return redirect()->to('admin/users')->with('success', 'User updated successfully!');
    }

    // POST admin/users/delete/(:num)
    public function delete($id)
    {
        // Prevent self-deletion
        if ((int) $id === (int) $this->session->get('user_id')) {
            return $this->jsonError('You cannot delete your own account.');
        }

        $user = $this->um->find($id);
        if (!$user) {
            return $this->jsonError('User not found.');
        }

        $this->um->delete($id);
        $this->logActivity('users', $id, 'delete', 'Deleted user: ' . $user['email']);

        return $this->jsonSuccess('User deleted.');
    }

    // POST admin/users/toggle-active/(:num)
    public function toggleActive($id)
    {
        $user = $this->um->find($id);
        if (!$user) {
            return $this->jsonError('User not found.');
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->um->update($id, ['is_active' => $newStatus]);

        return $this->jsonSuccess($newStatus ? 'User activated.' : 'User deactivated.');
    }
}
