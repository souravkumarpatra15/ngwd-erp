<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\NotificationModel;
use App\Models\SettingModel;
use App\Models\ActivityModel;

class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'text', 'number', 'erp', 'whatsapp'];
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
    /**
     * Resolve a reliable inline MIME type for previewable uploads. Browser
     * uploads sometimes store a generic application/octet-stream, which
     * stops <video>/<img> from playing — map by extension in that case.
     */
    protected function mediaMime(string $extension, $storedMime = null): string
    {
        $map = [
            'mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo', 'mkv' => 'video/x-matroska',
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf',
        ];
        $stored = strtolower(trim((string)$storedMime));
        if ($stored !== '' && $stored !== 'application/octet-stream') return (string)$storedMime;
        $ext = strtolower($extension);
        if (isset($map[$ext])) return $map[$ext];
        return $stored !== '' ? (string)$storedMime : 'application/octet-stream';
    }
    /**
     * Stream a file inline with real HTTP Range (206 Partial Content)
     * support so <video> elements can play and seek. Reads only the
     * requested byte range into memory instead of the whole file.
     */
    protected function streamInlineFile(string $realPath, string $downloadName, string $mime)
    {
        $size = filesize($realPath);
        $start = 0; $end = $size - 1; $status = 200;
        $rangeHeader = $_SERVER['HTTP_RANGE'] ?? '';
        if ($rangeHeader !== '' && preg_match('/bytes\s*=\s*(\d*)-(\d*)/', $rangeHeader, $m)) {
            if ($m[1] === '' && $m[2] !== '') { $start = max(0, $size - (int)$m[2]); }
            else { $start = (int)$m[1]; if ($m[2] !== '') $end = min((int)$m[2], $size - 1); }
            if ($start > $end || $start >= $size) {
                return $this->response->setStatusCode(416)->setHeader('Content-Range', "bytes */{$size}");
            }
            $status = 206;
        }
        $length = $end - $start + 1;
        $safeName = preg_replace('/[^\w\-. ]+/', '_', $downloadName);
        $fh = fopen($realPath, 'rb');
        fseek($fh, $start);
        $body = stream_get_contents($fh, $length);
        fclose($fh);
        $res = $this->response->setStatusCode($status)
            ->setContentType($mime)
            ->setHeader('Accept-Ranges', 'bytes')
            ->setHeader('Content-Length', (string)$length)
            ->setHeader('Content-Disposition', 'inline; filename="' . $safeName . '"');
        if ($status === 206) $res->setHeader('Content-Range', "bytes {$start}-{$end}/{$size}");
        return $res->setBody($body);
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
