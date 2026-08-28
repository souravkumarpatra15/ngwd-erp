<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\PmsAuthorizationService;

class SearchController extends BaseController
{
    private const ALLOWED_ROLES = ['superadmin', 'admin', 'manager', 'staff'];

    public function index()
    {
        $session = session();
        $role = (string) $session->get('user_role');
        $department = (string) $session->get('department');
        $userId = (int) $session->get('user_id');

        if (!$session->get('user_id') || !in_array($role, self::ALLOWED_ROLES, true)) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $q = trim((string) $this->request->getGet('q'));
        if (strlen($q) < 2) return $this->response->setJSON([]);

        $auth = new PmsAuthorizationService();
        $db = \Config\Database::connect();
        $results = [];

        $leads = $db->table('leads')->groupStart()->like('name', $q)->orLike('email', $q)->orLike('mobile', $q)->groupEnd()->limit(5)->get()->getResultArray();
        foreach ($leads as $row) $results[] = ['title' => $row['name'] ?? 'Lead', 'type' => 'Lead', 'url' => base_url('admin/leads')];

        // Clients carry contact info directly — only show to roles/departments
        // permitted to see client contact details.
        if ($auth->canViewClientContact($role, $department)) {
            $clients = $db->table('clients')->groupStart()->like('name', $q)->orLike('email', $q)->orLike('client_number', $q)->groupEnd()->limit(5)->get()->getResultArray();
            foreach ($clients as $row) $results[] = ['title' => $row['name'] ?? 'Client', 'type' => 'Client', 'url' => base_url('admin/clients')];
        }

        // Projects are scoped to what this user can see (same rule as the
        // project list/kanban) rather than every project in the company.
        $visible = $auth->getVisibleProjectIds($role, $userId);
        $projectQuery = $db->table('projects')->like('name', $q);
        if ($visible !== null) $projectQuery->whereIn('id', $visible ?: [0]);
        $projects = $projectQuery->limit(5)->get()->getResultArray();
        foreach ($projects as $row) $results[] = ['title' => $row['name'] ?? 'Project', 'type' => 'Project', 'url' => base_url('admin/projects')];

        // Invoices are pure financial data — gated the same as project cost.
        if ($auth->canViewFinancials($role, $department)) {
            $invoices = $db->table('invoices')->like('invoice_number', $q)->limit(5)->get()->getResultArray();
            foreach ($invoices as $row) $results[] = ['title' => $row['invoice_number'] ?? 'Invoice', 'type' => 'Invoice', 'url' => base_url('admin/invoices')];
        }

        return $this->response->setJSON($results);
    }
}
