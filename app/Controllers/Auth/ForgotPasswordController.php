<?php
namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\EmailService;

class ForgotPasswordController extends BaseController
{
    protected UserModel $um;

    public function __construct()
    {
        $this->um = new UserModel();
    }

    public function index()
    {
        return view('auth/forgot_password', ['title' => 'Forgot Password']);
    }

    public function sendLink()
    {
        if (!$this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $user = $this->um->where('email', $email)->where('is_active', 1)->first();

        if (!$user) {
            return redirect()->to('forgot-password')->with('success', 'If that email exists, a reset link has been sent.');
        }

        $this->db->table('password_resets')->where('email', $email)->update(['used' => 1]);

        $token = bin2hex(random_bytes(32));
        $this->db->table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'used' => 0,
        ]);

        $resetUrl = base_url('reset-password/' . $token);
        $name = htmlspecialchars((string) ($user['name'] ?? 'there'), ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        $body = "<h3>Password Reset Request</h3><p>Hello {$name},</p><p>Click the button below to reset your password. This link expires in 1 hour.</p><p style='text-align:center;margin:30px 0'><a href='{$safeUrl}' style='background:#0d6efd;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600'>Reset Password</a></p><p>If you did not request this, ignore this email.</p><p>Or copy this link:<br><small>{$safeUrl}</small></p>";

        (new EmailService())->send($email, 'Password Reset — NGWebD ERP', $body);
        return redirect()->to('forgot-password')->with('success', 'If that email exists, a reset link has been sent.');
    }

    public function resetForm($token)
    {
        if (!$this->getValidToken($token)) {
            return redirect()->to('forgot-password')->with('error', 'This reset link is invalid or has expired.');
        }
        return view('auth/reset_password', ['title' => 'Reset Password', 'token' => $token]);
    }

    public function resetPassword($token)
    {
        if (!$this->validate([
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $this->db->transBegin();
        try {
            $record = $this->db->table('password_resets')
                ->where('token', $token)->where('used', 0)
                ->where('expires_at >=', date('Y-m-d H:i:s'))
                ->get()->getRowArray();
            if (!$record) throw new \RuntimeException('Invalid or expired reset token.');

            $user = $this->um->where('email', $record['email'])->where('is_active', 1)->first();
            if (!$user) throw new \RuntimeException('Account not found.');

            if (!$this->um->update($user['id'], [
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s'),
            ])) {
                throw new \RuntimeException('Unable to update password.');
            }

            $updated = $this->db->table('password_resets')
                ->where('token', $token)->where('used', 0)->update(['used' => 1]);
            if (!$updated) throw new \RuntimeException('Unable to invalidate reset token.');
            if (!$this->db->transStatus()) throw new \RuntimeException('Password reset transaction failed.');
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Password reset failed: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('forgot-password')->with('error', 'Unable to reset password. Please request a new link.');
        }

        return redirect()->to('login')->with('success', 'Password reset successfully! Please log in.');
    }

    private function getValidToken(string $token): ?array
    {
        $record = $this->db->table('password_resets')
            ->where('token', $token)->where('used', 0)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();
        return $record ?: null;
    }
}
