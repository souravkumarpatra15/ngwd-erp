<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class LoginController extends BaseController
{
    public function index()
    {
        if (session()->get('user_id')) {
            $role = session()->get('user_role');
            $isClientOnly = $role === 'client' && !session()->get('client_id') ? true : in_array($role, ['client'], true);
            return redirect()->to($isClientOnly ? 'portal/dashboard' : 'admin/dashboard');
        }
        return view('auth/login', ['title' => 'Login — NGWebD ERP']);
    }

    public function process()
    {
        if (!$this->validate([
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[8]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $user = $userModel->where('email', $email)->where('is_active', 1)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        $validRoles = ['superadmin', 'admin', 'manager', 'staff', 'client', 'hr_manager', 'recruitment_manager', 'project_manager', 'team_leader', 'permanent_employee', 'intern', 'finance_manager'];
        if (!in_array((string) $user['role'], $validRoles, true)) {
            log_message('critical', 'Login blocked: user #{id} has an invalid role value ({role}). Check whether the users.role ENUM migration has been run.', ['id' => $user['id'], 'role' => $user['role']]);
            return redirect()->back()->withInput()->with('error', 'Your account role is not configured correctly. Please contact an administrator.');
        }

        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $userModel->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        session()->regenerate(true);
        session()->set([
            'user_id' => $user['id'],
            'user_name' => $user['name'],
            'user_email' => $user['email'],
            'user_role' => $user['role'],
            'client_id' => $user['client_id'],
            'client_role' => $user['client_role'] ?? null,
            'department' => $user['department'] ?? null,
            'logged_in' => true,
        ]);

        $userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        $dashboard = in_array($user['role'], ['client'], true) && (int) ($user['client_id'] ?? 0) > 0 ? 'portal/dashboard' : 'admin/dashboard';
        return redirect()->to($dashboard)->with('success', 'Welcome back, ' . $user['name'] . '!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Logged out successfully.');
    }
}
