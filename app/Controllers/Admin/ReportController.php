<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LeadModel;
use App\Models\ProjectModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\DomainModel;
use App\Models\HostingModel;

class ReportController extends BaseController
{
    public function index()
    {
        return view('admin/reports/index', ['title' => 'Reports']);
    }

    // ── Revenue ──────────────────────────────────────────────
    public function revenue()
    {
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? 0);

        $b = $this->db->table('payments')
            ->select('payments.*, clients.name as client_name, projects.name as project_name, invoices.invoice_number')
            ->join('clients',  'clients.id  = payments.client_id',  'left')
            ->join('projects', 'projects.id = payments.project_id', 'left')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'left')
            ->where('payments.status', 'completed')
            ->where("YEAR(payments.created_at)", $year);

        if ($month) {
            $b->where("MONTH(payments.created_at)", $month);
        }

        $payments      = $b->orderBy('payments.created_at', 'DESC')->get()->getResultArray();
        $total_revenue = array_sum(array_column($payments, 'amount'));

        return view('admin/reports/revenue', compact('payments', 'total_revenue', 'year', 'month'));
    }

    // ── Leads ────────────────────────────────────────────────
    public function leads()
    {
        $from = $this->request->getGet('from') ?? date('Y-01-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $leads = $this->db->table('leads')
            ->where('DATE(created_at) >=', $from)
            ->where('DATE(created_at) <=', $to)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $converted = count(array_filter($leads, fn($l) => $l['status'] === 'converted'));
        $conv_rate = count($leads) > 0 ? round($converted / count($leads) * 100, 1) : 0;

        return view('admin/reports/leads', compact('leads', 'converted', 'conv_rate', 'from', 'to'));
    }

    // ── Projects ─────────────────────────────────────────────
    public function projects()
    {
        $year   = (int) ($this->request->getGet('year')   ?? date('Y'));
        $status = $this->request->getGet('status') ?? '';

        $b = $this->db->table('projects')
            ->select('projects.*, clients.name as client_name')
            ->join('clients', 'clients.id = projects.client_id', 'left')
            ->where("YEAR(projects.created_at)", $year);

        if ($status) $b->where('projects.status', $status);

        $projects     = $b->orderBy('projects.created_at', 'DESC')->get()->getResultArray();
        $total_budget = array_sum(array_column($projects, 'budget'));

        // Status counts
        $counts = [];
        foreach ($projects as $p) {
            $counts[$p['status']] = ($counts[$p['status']] ?? 0) + 1;
        }

        return view('admin/reports/projects', compact('projects', 'total_budget', 'counts', 'year', 'status'));
    }

    // ── Invoices ─────────────────────────────────────────────
    public function invoices()
    {
        $year   = (int) ($this->request->getGet('year')   ?? date('Y'));
        $status = $this->request->getGet('status') ?? '';

        $b = $this->db->table('invoices')
            ->select('invoices.*, clients.name as client_name, projects.name as project_name')
            ->join('clients',  'clients.id  = invoices.client_id',  'left')
            ->join('projects', 'projects.id = invoices.project_id', 'left')
            ->where("YEAR(invoices.invoice_date)", $year);

        if ($status) $b->where('invoices.status', $status);

        $invoices          = $b->orderBy('invoices.invoice_date', 'DESC')->get()->getResultArray();
        $total_billed      = array_sum(array_column($invoices, 'total'));
        $total_collected   = array_sum(array_column($invoices, 'paid_amount'));
        $total_outstanding = $total_billed - $total_collected;
        $overdue_count     = count(array_filter(
            $invoices,
            fn($i) =>
            strtotime($i['due_date']) < time() && !in_array($i['status'], ['paid', 'cancelled'])
        ));

        return view('admin/reports/invoices', compact(
            'invoices',
            'total_billed',
            'total_collected',
            'total_outstanding',
            'overdue_count',
            'year',
            'status'
        ));
    }

    // ── Payments ─────────────────────────────────────────────
    public function payments()
    {
        $year   = (int) ($this->request->getGet('year')   ?? date('Y'));
        $month  = (int) ($this->request->getGet('month')  ?? 0);
        $method = $this->request->getGet('method') ?? '';

        $b = $this->db->table('payments')
            ->select('payments.*, clients.name as client_name, invoices.invoice_number')
            ->join('clients',  'clients.id  = payments.client_id',  'left')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'left')
            ->where('payments.status', 'completed')
            ->where("YEAR(payments.created_at)", $year);

        if ($month)  $b->where("MONTH(payments.created_at)", $month);
        if ($method) $b->where('payments.method', $method);

        $payments     = $b->orderBy('payments.created_at', 'DESC')->get()->getResultArray();
        $total_amount = array_sum(array_column($payments, 'amount'));

        // Method breakdown
        $method_breakdown = [];
        foreach ($payments as $p) {
            $m = $p['method'] ?? 'other';
            $method_breakdown[$m] = ($method_breakdown[$m] ?? 0) + $p['amount'];
        }
        arsort($method_breakdown);

        return view('admin/reports/payments', compact(
            'payments',
            'total_amount',
            'method_breakdown',
            'year',
            'month',
            'method'
        ));
    }

    // ── Domains & Hostings ────────────────────────────────────
    public function domains()
    {
        $days = (int) ($this->request->getGet('days') ?? 30);
        $type = $this->request->getGet('type') ?? 'all';

        $cutoff = date('Y-m-d', strtotime("+{$days} days"));

        $domains  = [];
        $hostings = [];

        if ($type !== 'hosting') {
            $domains = $this->db->table('domains')
                ->select('domains.*, clients.name as client_name')
                ->join('clients', 'clients.id = domains.client_id', 'left')
                ->where('domains.expiry_date <=', $cutoff)
                ->orderBy('domains.expiry_date', 'ASC')
                ->get()->getResultArray();
        }

        if ($type !== 'domain') {
            $hostings = $this->db->table('hostings')
                ->select('hostings.*, clients.name as client_name')
                ->join('clients', 'clients.id = hostings.client_id', 'left')
                ->where('hostings.expiry_date <=', $cutoff)
                ->orderBy('hostings.expiry_date', 'ASC')
                ->get()->getResultArray();
        }

        $all_items          = array_merge($domains, $hostings);
        $expired_count      = count(array_filter($all_items, fn($i) => strtotime($i['expiry_date']) < time()));
        $expiring_soon_count = count(array_filter($all_items, fn($i) => strtotime($i['expiry_date']) >= time() && ceil((strtotime($i['expiry_date']) - time()) / 86400) <= 30));
        $active_count       = count(array_filter($all_items, fn($i) => strtotime($i['expiry_date']) > time()));
        $total_renewal_cost = array_sum(array_column($all_items, 'renewal_cost'));

        return view('admin/reports/domains', compact(
            'domains',
            'hostings',
            'expired_count',
            'expiring_soon_count',
            'active_count',
            'total_renewal_cost',
            'days',
            'type'
        ));
    }

    // ── Export ────────────────────────────────────────────────
    public function export($reportType, $format)
    {
        $year   = $this->request->getGet('year')   ?? date('Y');
        $month  = $this->request->getGet('month')  ?? '';
        $status = $this->request->getGet('status') ?? '';
        $from   = $this->request->getGet('from')   ?? date('Y-01-01');
        $to     = $this->request->getGet('to')     ?? date('Y-m-d');
        $days   = $this->request->getGet('days')   ?? 30;
        $type   = $this->request->getGet('type')   ?? 'all';

        $data = [];
        $headers = [];
        $filename = '';

        switch ($reportType) {
            case 'revenue':
                $filename = "revenue_{$year}";
                $headers  = ['Payment #', 'Client', 'Project', 'Invoice', 'Amount', 'Method', 'Date'];
                $rows     = $this->db->table('payments')
                    ->select('payments.payment_number, clients.name as client_name, projects.name as project_name, invoices.invoice_number, payments.amount, payments.method, payments.created_at')
                    ->join('clients', 'clients.id=payments.client_id', 'left')
                    ->join('projects', 'projects.id=payments.project_id', 'left')
                    ->join('invoices', 'invoices.id=payments.invoice_id', 'left')
                    ->where('payments.status', 'completed')
                    ->where("YEAR(payments.created_at)", (int)$year)
                    ->get()->getResultArray();
                foreach ($rows as $r) {
                    $data[] = [$r['payment_number'], $r['client_name'], $r['project_name'], $r['invoice_number'], '₹' . $r['amount'], $r['method'], date('d M Y', strtotime($r['created_at']))];
                }
                break;

            case 'leads':
                $filename = "leads_{$from}_to_{$to}";
                $headers  = ['Lead #', 'Name', 'Company', 'Mobile', 'Source', 'Status', 'Follow Up', 'Created'];
                $rows     = $this->db->table('leads')
                    ->where('DATE(created_at) >=', $from)->where('DATE(created_at) <=', $to)
                    ->orderBy('created_at', 'DESC')->get()->getResultArray();
                foreach ($rows as $r) {
                    $data[] = [$r['lead_number'], $r['name'], $r['company_name'] ?? '', $r['mobile'], $r['source'], $r['status'], $r['follow_up_date'] ?? '', date('d M Y', strtotime($r['created_at']))];
                }
                break;

            case 'projects':
                $filename = "projects_{$year}";
                $headers  = ['Project #', 'Name', 'Client', 'Type', 'Budget', 'Advance', 'Status', 'Delivery'];
                $rows     = $this->db->table('projects')
                    ->select('projects.*, clients.name as client_name')
                    ->join('clients', 'clients.id=projects.client_id', 'left')
                    ->where("YEAR(projects.created_at)", (int)$year)
                    ->get()->getResultArray();
                foreach ($rows as $r) {
                    $data[] = [$r['project_number'], $r['name'], $r['client_name'], $r['type'], '₹' . ($r['budget'] ?? 0), '₹' . ($r['advance_paid'] ?? 0), $r['status'], $r['delivery_date'] ?? ''];
                }
                break;

            case 'invoices':
                $filename = "invoices_{$year}";
                $headers  = ['Invoice #', 'Client', 'Project', 'Date', 'Due', 'Total', 'Paid', 'Balance', 'Status'];
                $rows     = $this->db->table('invoices')
                    ->select('invoices.*, clients.name as client_name, projects.name as project_name')
                    ->join('clients', 'clients.id=invoices.client_id', 'left')
                    ->join('projects', 'projects.id=invoices.project_id', 'left')
                    ->where("YEAR(invoices.invoice_date)", (int)$year)
                    ->get()->getResultArray();
                foreach ($rows as $r) {
                    $bal = $r['balance_due'] ?? ($r['total'] - $r['paid_amount']);
                    $data[] = [$r['invoice_number'], $r['client_name'], $r['project_name'], date('d M Y', strtotime($r['invoice_date'])), date('d M Y', strtotime($r['due_date'])), '₹' . $r['total'], '₹' . $r['paid_amount'], '₹' . $bal, $r['status']];
                }
                break;

            case 'payments':
                $filename = "payments_{$year}";
                $headers  = ['Payment #', 'Client', 'Invoice', 'Amount', 'Method', 'Ref/UTR', 'Date'];
                $rows     = $this->db->table('payments')
                    ->select('payments.*, clients.name as client_name, invoices.invoice_number')
                    ->join('clients', 'clients.id=payments.client_id', 'left')
                    ->join('invoices', 'invoices.id=payments.invoice_id', 'left')
                    ->where('payments.status', 'completed')
                    ->where("YEAR(payments.created_at)", (int)$year)
                    ->get()->getResultArray();
                foreach ($rows as $r) {
                    $data[] = [$r['payment_number'], $r['client_name'], $r['invoice_number'] ?? '', '₹' . $r['amount'], $r['method'], $r['transaction_id'] ?? '', date('d M Y', strtotime($r['created_at']))];
                }
                break;

            case 'domains':
                $filename = "renewals_{$days}days";
                $headers  = ['Type', 'Name/Provider', 'Client', 'Expiry', 'Days Left', 'Renewal Cost', 'Status'];
                $cutoff   = date('Y-m-d', strtotime("+{$days} days"));
                $dRows    = $this->db->table('domains')->select('domains.*,"Domain" as record_type,domain_name as display_name,clients.name as client_name')->join('clients', 'clients.id=domains.client_id', 'left')->where('expiry_date<=', $cutoff)->get()->getResultArray();
                $hRows    = $this->db->table('hostings')->select('hostings.*,"Hosting" as record_type,provider as display_name,clients.name as client_name')->join('clients', 'clients.id=hostings.client_id', 'left')->where('expiry_date<=', $cutoff)->get()->getResultArray();
                foreach (array_merge($dRows, $hRows) as $r) {
                    $dl = ceil((strtotime($r['expiry_date']) - time()) / 86400);
                    $data[] = [$r['record_type'], $r['display_name'], $r['client_name'], $r['expiry_date'], $dl . 'd', '₹' . ($r['renewal_cost'] ?? 0), $r['status']];
                }
                break;
        }

        if ($format === 'csv') {
            return $this->exportCSV($filename . '.csv', $headers, $data);
        }
        return $this->exportExcel($filename . '.csv', $headers, $data); // use CSV as Excel fallback
    }

    // ── Helpers ───────────────────────────────────────────────
    private function exportCSV(string $filename, array $headers, array $rows)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename={$filename}");
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
        fputcsv($out, $headers);
        foreach ($rows as $row) fputcsv($out, $row);
        fclose($out);
        exit;
    }

    private function exportExcel(string $filename, array $headers, array $rows)
    {
        // Output CSV with Excel-compatible MIME type
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header("Content-Disposition: attachment; filename={$filename}");
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, $headers);
        foreach ($rows as $row) fputcsv($out, $row);
        fclose($out);
        exit;
    }
}
