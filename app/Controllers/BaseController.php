<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\NotificationModel;
use App\Models\SettingModel;
use App\Models\ActivityModel;

class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'text', 'number', 'erp'];
    protected $session;
    protected $settings = [];
    protected $db;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->session  = \Config\Services::session();
        $this->settings = (new SettingModel())->getAllSettings();
        $this->db = \Config\Database::connect();
        if ($this->session->get('user_id')) {
            $unread = (new NotificationModel())->getUnreadCount($this->session->get('user_id'));

            $renderer = \Config\Services::renderer();

            $renderer->setVar('unread_notifications', $unread);
            $renderer->setVar('settings', $this->settings);
            $renderer->setVar('current_user', [
                'id'   => $this->session->get('user_id'),
                'name' => $this->session->get('user_name'),
                'role' => $this->session->get('user_role'),
            ]);
        }
    }

    protected function jsonSuccess($message = 'Success', $data = [])
    {
        return $this->response->setJSON(['status' => 'success', 'message' => $message, 'data' => $data]);
    }
    /**
     * Server-side module permission gate. superadmin/admin/manager pass
     * through untouched; staff/client roles are checked against
     * PmsAuthorizationService::hasModulePermission(). Call at the top of
     * a controller action; returns a response to short-circuit with, or
     * null when the caller may proceed.
     */
    protected function requireModule(string $module, string $action = 'view')
    {
        $userId = (int) session()->get('user_id');
        $role = (string) session()->get('user_role');
        $clientRole = session()->get('client_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if ($auth->hasModulePermission($userId, $role, $clientRole, $module, $action)) {
            return null;
        }
        if ($this->request->isAJAX()) {
            return $this->jsonError('You do not have permission to access this section.');
        }
        return redirect()->to(session()->get('client_id') ? 'portal/dashboard' : 'admin/dashboard')->with('error', 'You do not have permission to access this section.');
    }

    protected function jsonError($message = 'Error', $data = [])
    {
        return $this->response->setJSON(['status' => 'error', 'message' => $message, 'data' => $data]);
    }
    protected function logActivity($module, $moduleId, $action, $description = '')
    {
        (new ActivityModel())->insert([
            'user_id'     => $this->session->get('user_id'),
            'module'      => $module,
            'module_id'   => $moduleId,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $this->request->getIPAddress(),
        ]);
    }
    protected function generateNumber($prefix, $model)
    {
        return sprintf('%s/%s/%05d', $prefix, date('Y'), $model->countAll() + 1);
    }
}
