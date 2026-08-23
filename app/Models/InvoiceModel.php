<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table      = 'invoices';
    protected $primaryKey = 'id';
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'invoice_number','client_id','project_id','milestone_id','domain_id','hosting_id',
        'invoice_date','due_date','subtotal','tax_percent','tax_amount','discount','total',
        'paid_amount','balance_due','is_gst','currency','notes','terms','status','sent_at','paid_at','created_by',
    ];

    public function getWithDetails($id): ?array
    {
        return $this->db->table('invoices')
            ->select('invoices.*, clients.name as client_name, clients.email as client_email,
                      clients.whatsapp as client_whatsapp, clients.address as client_address,
                      clients.gst_number as client_gst, projects.name as project_name,
                      milestones.title as milestone_title, domains.domain_name as domain_name,
                      hostings.provider as hosting_provider, hostings.package as hosting_package')
            ->join('clients','clients.id = invoices.client_id','left')
            ->join('projects','projects.id = invoices.project_id','left')
            ->join('milestones','milestones.id = invoices.milestone_id','left')
            ->join('domains','domains.id = invoices.domain_id','left')
            ->join('hostings','hostings.id = invoices.hosting_id','left')
            ->where('invoices.id',$id)->get()->getRowArray() ?: null;
    }

    public static function forLabel(array $invoice): string
    {
        if (!empty($invoice['milestone_id']) && !empty($invoice['milestone_title'])) return 'Milestone — '.$invoice['milestone_title'];
        if (!empty($invoice['domain_id']) && !empty($invoice['domain_name'])) return 'Domain Renewal — '.$invoice['domain_name'];
        if (!empty($invoice['hosting_id']) && !empty($invoice['hosting_provider'])) return 'Hosting Renewal — '.$invoice['hosting_provider'].(!empty($invoice['hosting_package']) ? ' ('.$invoice['hosting_package'].')' : '');
        if (!empty($invoice['project_name'])) return 'Project — '.$invoice['project_name'];
        return 'General';
    }

    public function getDataTable(string $search, int $start, int $length, string $status = ''): array
    {
        $start = max(0, $start);
        $length = $length < 1 ? 25 : min($length, 100);
        $base = $this->db->table('invoices');
        $recordsTotal = $base->countAllResults();
        $b = $this->db->table('invoices')
            ->select('invoices.*, clients.name as client_name, milestones.title as milestone_title,
                      domains.domain_name as domain_name, hostings.provider as hosting_provider, hostings.package as hosting_package')
            ->join('clients','clients.id = invoices.client_id','left')
            ->join('milestones','milestones.id = invoices.milestone_id','left')
            ->join('domains','domains.id = invoices.domain_id','left')
            ->join('hostings','hostings.id = invoices.hosting_id','left');
        if ($search) $b->groupStart()->like('invoices.invoice_number',$search)->orLike('clients.name',$search)->groupEnd();
        if ($status) $b->where('invoices.status',$status);
        $recordsFiltered = (clone $b)->countAllResults();
        $data = $b->orderBy('invoices.created_at','DESC')->limit($length,$start)->get()->getResultArray();
        return ['total' => $recordsTotal, 'filtered' => $recordsFiltered, 'data' => $data];
    }

    public function sumBy(string $col): float
    {
        $allowed = ['subtotal','tax_amount','discount','total','paid_amount','balance_due'];
        if (!in_array($col, $allowed, true)) throw new \InvalidArgumentException('Invalid invoice aggregate column.');
        return (float) ($this->selectSum($col)->get()->getRowArray()[$col] ?? 0);
    }

    public function recalcBalance(int $id): bool
    {
        $this->db->transBegin();
        try {
            $inv = $this->db->query('SELECT * FROM invoices WHERE id = ? FOR UPDATE', [$id])->getRowArray();
            if (!$inv) {
                $this->db->transRollback();
                return false;
            }

            $total = max(0, (float) $inv['total']);
            $paid = max(0, (float) $inv['paid_amount']);
            $balance = max(0, $total - $paid);
            $status = $paid >= $total && $total > 0
                ? 'paid'
                : ($paid > 0 ? 'partial' : ($inv['status'] === 'paid' ? 'sent' : $inv['status']));

            $update = ['balance_due' => $balance, 'status' => $status];
            if ($status === 'paid' && empty($inv['paid_at'])) $update['paid_at'] = date('Y-m-d H:i:s');
            if ($status !== 'paid' && !empty($inv['paid_at'])) $update['paid_at'] = null;

            if (!$this->update($id, $update) || !$this->db->transStatus()) {
                throw new \RuntimeException('Unable to recalculate invoice balance.');
            }
            $this->db->transCommit();
            return true;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Invoice balance recalculation failed: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}