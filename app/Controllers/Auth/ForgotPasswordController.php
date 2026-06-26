<?php
namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\EmailService;

/**
 * ForgotPasswordController
 * Handles forgot-password and reset-password flow.
 * Routes:
 *   GET/POST  forgot-password
 *   GET/POST  reset-password/(:segment)
 */
class ForgotPasswordController extends BaseController
{
    protected UserModel $um;

    public function __construct()
    {
        $this->um = new UserModel();
    }

    // GET forgot-password
    public function index()
    {
        return view('auth/forgot_password', ['title' => 'Forgot Password']);
    }

    // POST forgot-password
    public function sendLink()
    {
        if (!$this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $user  = $this->um->where('email', $email)->where('is_active', 1)->first();

        // Always show success to prevent email enumeration
        if (!$user) {
            return redirect()->to('forgot-password')->with('success', 'If that email exists, a reset link has been sent.');
        }

        // Invalidate old tokens for this email
        $this->db->table('password_resets')->where('email', $email)->update(['used' => 1]);

        $token = bin2hex(random_bytes(32));
        $this->db->table('password_resets')->insert([
            'email'      => $email,
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'used'       => 0,
        ]);

        $resetUrl = base_url('reset-password/' . $token);
        $body = "
            <h3>Password Reset Request</h3>
            <p>Hello {$user['name']},</p>
            <p>Click the button below to reset your password. This link expires in 1 hour.</p>
            <p style='text-align:center;margin:30px 0'>
                <a href='{$resetUrl}' style='background:#0d6efd;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600'>Reset Password</a>
            </p>
            <p>If you did not request this, ignore this email.</p>
            <p>Or copy this link:<br><small>{$resetUrl}</small></p>
        ";

        (new EmailService())->send($email, 'Password Reset — NGWebD ERP', $body);

        return redirect()->to('forgot-password')->with('success', 'If that email exists, a reset link has been sent.');
    }

    // GET reset-password/(:segment)
    public function resetForm($token)
    {
        $record = $this->getValidToken($token);
        if (!$record) {
            return redirect()->to('forgot-password')->with('error', 'This reset link is invalid or has expired.');
        }

        return view('auth/reset_password', ['title' => 'Reset Password', 'token' => $token]);
    }

    // POST reset-password/(:segment)
    public function resetPassword($token)
    {
        $record = $this->getValidToken($token);
        if (!$record) {
            return redirect()->to('forgot-password')->with('error', 'This reset link is invalid or has expired.');
        }

        if (!$this->validate([
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $user = $this->um->where('email', $record['email'])->first();
        if (!$user) {
            return redirect()->to('login')->with('error', 'Account not found.');
        }

        $this->um->update($user['id'], [
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Mark token as used
        $this->db->table('password_resets')->where('token', $token)->update(['used' => 1]);

        return redirect()->to('login')->with('success', 'Password reset successfully! Please log in.');
    }

    // ── Helper ─────────────────────────────────────────────────
    private function getValidToken(string $token): ?array
    {
        $record = $this->db->table('password_resets')
            ->where('token', $token)
            ->where('used', 0)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        return $record ?: null;
    }
}
