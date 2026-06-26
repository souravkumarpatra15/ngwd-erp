<?php
namespace App\Controllers\Auth;
use App\Controllers\BaseController;
use App\Models\UserModel;

class LoginController extends BaseController
{
    public function index() {
        if (session()->get('user_id')) {
            return redirect()->to(session()->get('user_role') === 'superadmin' ? 'admin/dashboard' : 'portal/dashboard');
        }
        return view('auth/login', ['title' => 'Login — NGWebD ERP']);
    }

    public function process() {
        if (!$this->validate(['email'=>'required|valid_email','password'=>'required|min_length[6]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $userModel = new UserModel();
        $user = $userModel->where('email', $this->request->getPost('email'))->where('is_active', 1)->first();
        if (!$user || !password_verify($this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }
        session()->set([
            'user_id'    => $user['id'],
            'user_name'  => $user['name'],
            'user_email' => $user['email'],
            'user_role'  => $user['role'],
            'client_id'  => $user['client_id'],
        ]);
        $userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        return redirect()->to($user['role'] === 'superadmin' ? 'admin/dashboard' : 'portal/dashboard')
            ->with('success', 'Welcome back, ' . $user['name'] . '!');
    }

    public function logout() {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Logged out successfully.');
    }
}
