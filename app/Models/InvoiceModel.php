<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table      = 'invoices';
    protected $primaryKey = 'id';
    protected $useTimestamps  = true;

    // balance_due IS a regular column in the DB (NOT generated) — must be in allowedFields
    protected $allowedFields = [
        'invoice_number',
        'client_id',
        'project_id',
        'milestone_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'discount',
        'total',
        'paid_amount',
        'balance_due',     // ← balance_due included
        'is_gst',
        'notes',
        'terms',
        'status',
        'sent_at',
        'paid_at',
        'created_by',
    ];

    // ── With client + project details ─────────────────────────
    public function getWithDetails($id): ?array
    {
        return $this->db->table('invoices')
            ->select('invoices.*, clients.name as client_name, clients.email as client_email,
                      clients.whatsapp as client_whatsapp, clients.address as client_address,
                      clients.gst_number as client_gst,
                      projects.name as project_name')
            ->join('clients',  'clients.id  = invoices.client_id',  'left')
            ->join('projects', 'projects.id = invoices.project_id', 'left')
            ->where('invoices.id', $id)
            ->get()->getRowArray() ?: null;
    }

    // ── DataTable ─────────────────────────────────────────────
    public function getDataTable(string $search, int $start, int $length, string $status = ''): array
    {
        $b = $this->db->table('invoices')
            ->select('invoices.*, clients.name as client_name')
            ->join('clients', 'clients.id = invoices.client_id', 'left');

        if ($search) {
            $b->groupStart()
                ->like('invoices.invoice_number', $search)
                ->orLike('clients.name', $search)
                ->groupEnd();
        }
        if ($status) $b->where('invoices.status', $status);

        $total    = (clone $b)->countAllResults();
        $data     = $b->orderBy('invoices.created_at', 'DESC')->limit($length, $start)->get()->getResultArray();

        return ['total' => $total, 'filtered' => $total, 'data' => $data];
    }

    // ── Sum helper (for dashboard) ────────────────────────────
    public function sumBy(string $col): float
    {
        return (float) ($this->selectSum($col)->get()->getRowArray()[$col] ?? 0);
    }

    // ── Recalculate balance_due after payment update ──────────
    public function recalcBalance(int $id): void
    {
        $inv = $this->find($id);
        if (!$inv) return;
        $balance = max(0, (float)$inv['total'] - (float)$inv['paid_amount']);
        $status  = $inv['paid_amount'] >= $inv['total'] ? 'paid'
            : ((float)$inv['paid_amount'] > 0 ? 'partial' : $inv['status']);
        $update  = ['balance_due' => $balance, 'status' => $status];
        if ($status === 'paid' && empty($inv['paid_at'])) {
            $update['paid_at'] = date('Y-m-d H:i:s');
        }
        $this->update($id, $update);
    }
}
