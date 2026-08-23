<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class LoginController extends BaseController
{
    public function index()
    {
        if (session()->get('user_id')) {
            return redirect()->to(in_array(session()->get('user_role'), ['superadmin', 'admin', 'manager'], true) ? 'admin/dashboard' : 'portal/dashboard');
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
            'logged_in' => true,
        ]);

        $userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        $dashboard = in_array($user['role'], ['superadmin', 'admin', 'manager'], true) ? 'admin/dashboard' : 'portal/dashboard';
        return redirect()->to($dashboard)->with('success', 'Welcome back, ' . $user['name'] . '!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Logged out successfully.');
    }
}
