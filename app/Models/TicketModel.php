<?php
namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table      = 'tickets';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'ticket_number', 'client_id', 'project_id', 'subject',
        'description', 'priority', 'status', 'closed_at',
    ];

    // ── Ticket list with client + reply count ──────────────────
    public function getAllWithDetails(array $filters = []): array
    {
        $b = $this->db->table('tickets')
            ->select('tickets.*,
                      clients.name as client_name, clients.email as client_email,
                      projects.name as project_name,
                      COUNT(tr.id) as reply_count,
                      MAX(tr.created_at) as last_reply_at')
            ->join('clients', 'clients.id = tickets.client_id', 'left')
            ->join('projects', 'projects.id = tickets.project_id', 'left')
            ->join('ticket_replies tr', 'tr.ticket_id = tickets.id', 'left')
            ->groupBy('tickets.id');

        if (!empty($filters['status'])) {
            $b->where('tickets.status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $b->where('tickets.priority', $filters['priority']);
        }
        if (!empty($filters['client_id'])) {
            $b->where('tickets.client_id', $filters['client_id']);
        }

        return $b->orderBy('tickets.created_at', 'DESC')->get()->getResultArray();
    }

    // ── Full ticket detail with all replies ────────────────────
    public function getWithReplies(int $id): ?array
    {
        $ticket = $this->db->table('tickets')
            ->select('tickets.*, clients.name as client_name, clients.email as client_email,
                      clients.whatsapp as client_whatsapp, projects.name as project_name')
            ->join('clients', 'clients.id = tickets.client_id', 'left')
            ->join('projects', 'projects.id = tickets.project_id', 'left')
            ->where('tickets.id', $id)
            ->get()->getRowArray();

        if (!$ticket) return null;

        $ticket['replies'] = $this->db->table('ticket_replies')
            ->select('ticket_replies.*, users.name as author_name, users.role as author_role, users.avatar as author_avatar')
            ->join('users', 'users.id = ticket_replies.user_id', 'left')
            ->where('ticket_id', $id)
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        return $ticket;
    }

    // ── Scoped counts ──────────────────────────────────────────
    public function countOpen(): int
    {
        return $this->where('status', 'open')->countAllResults(false);
    }

    public function countByPriority(): array
    {
        $rows = $this->select('priority, COUNT(*) as cnt')
                     ->where('status', 'open')
                     ->groupBy('priority')
                     ->get()->getResultArray();

        return array_column($rows, 'cnt', 'priority');
    }

    // ── Client-scoped ──────────────────────────────────────────
    public function getForClient(int $clientId): array
    {
        return $this->where('client_id', $clientId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
