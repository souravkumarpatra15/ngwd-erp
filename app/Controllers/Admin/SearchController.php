<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SearchController extends BaseController
{
    public function index()
    {
        $q = trim($this->request->getGet('q'));

        if (strlen($q) < 2) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        $results = [];

        $leads = $db->table('leads')
            ->like('name', $q)
            ->orLike('email', $q)
            ->orLike('mobile', $q)
            ->limit(5)
            ->get()
            ->getResultArray();

        foreach ($leads as $row) {
            $results[] = [
                'title' => $row['name'] ?? 'Lead',
                'type'  => 'Lead',
                'url'   => base_url('admin/leads')
            ];
        }

        $clients = $db->table('clients')
            ->like('name', $q)
            ->orLike('email', $q)
            ->orLike('client_number', $q)
            ->limit(5)
            ->get()
            ->getResultArray();

        foreach ($clients as $row) {
            $results[] = [
                'title' => $row['name'] ?? 'Client',
                'type'  => 'Client',
                'url'   => base_url('admin/clients')
            ];
        }

        $projects = $db->table('projects')
            ->like('name', $q)
            ->limit(5)
            ->get()
            ->getResultArray();

        foreach ($projects as $row) {
            $results[] = [
                'title' => $row['name'] ?? 'Project',
                'type'  => 'Project',
                'url'   => base_url('admin/projects')
            ];
        }

        $invoices = $db->table('invoices')
            ->like('invoice_number', $q)
            ->limit(5)
            ->get()
            ->getResultArray();

        foreach ($invoices as $row) {
            $results[] = [
                'title' => $row['invoice_number'] ?? 'Invoice',
                'type'  => 'Invoice',
                'url'   => base_url('admin/invoices')
            ];
        }

        return $this->response->setJSON($results);
    }
}
