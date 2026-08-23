<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    /**
     * Roles allowed to access the admin area.
     * Fine-grained permissions should be enforced at the module/action level.
     */
    private const ALLOWED_ROLES = [
        'superadmin',
        'admin',
        'manager',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userId = $session->get('user_id');
        $userRole = $session->get('user_role');

        if (!$userId || !in_array($userRole, self::ALLOWED_ROLES, true)) {
            return redirect()->to('/login')->with('error', 'Please login to continue.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
