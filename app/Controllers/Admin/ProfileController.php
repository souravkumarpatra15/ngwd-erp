<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * ProfileController
 * Admin: view/edit own profile, change password.
 * Routes: admin/profile
 */
class ProfileController extends BaseController
{
    protected UserModel $um;

    public function __construct()
    {
        $this->um = new UserModel();
    }

    // GET admin/profile
    public function index()
    {
        $user = $this->um->find($this->session->get('user_id'));

        return view('admin/profile/index', [
            'title' => 'My Profile',
            'user'  => $user,
        ]);
    }

    // POST admin/profile/update
    public function update()
    {
        $id   = $this->session->get('user_id');
        $user = $this->um->find($id);

        $emailRule = ($user['email'] === $this->request->getPost('email'))
            ? 'required|valid_email'
            : 'required|valid_email|is_unique[users.email]';

        if (!$this->validate(['name' => 'required|min_length[2]', 'email' => $emailRule])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
        ];

        // Avatar upload
        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($avatar->getMimeType(), $allowed)) {
                return redirect()->back()->with('error', 'Avatar must be a JPG, PNG or WebP image.');
            }
            $name = 'avatar_' . $id . '_' . time() . '.' . $avatar->getExtension();
            $avatar->move(WRITEPATH . 'uploads/avatars/', $name);
            $data['avatar'] = 'uploads/avatars/' . $name;
        }

        $this->um->update($id, $data);
        session()->set('user_name', $data['name']);
        session()->set('user_email', $data['email']);

        return redirect()->to('admin/profile')->with('success', 'Profile updated successfully!');
    }

    // POST admin/profile/change-password
    public function changePassword()
    {
        $id = $this->session->get('user_id');

        if (!$this->validate([
            'current_password'  => 'required',
            'new_password'      => 'required|min_length[8]',
            'confirm_password'  => 'required|matches[new_password]',
        ])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $user = $this->um->find($id);

        if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        $this->um->update($id, [
            'password'   => password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('users', $id, 'password_change', 'Changed own password');

        return redirect()->to('admin/profile')->with('success', 'Password changed successfully!');
    }
}
