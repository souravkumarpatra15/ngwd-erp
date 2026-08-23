<?php
namespace App\Models;
use CodeIgniter\Model;

class MarketingLeadModel extends Model {
    protected $table = 'marketing_leads';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'client_id','project_id','campaign_name','platform','name','phone','whatsapp',
        'email','city','requirement','notes','status','lead_date','created_by',
    ];

    public function getDataTable($search = '', $start = 0, $length = 10, $clientId = '', $status = '', $platform = '') {
        $b = $this->db->table('marketing_leads')
            ->select('marketing_leads.*, clients.name as client_name, projects.name as project_name')
            ->join('clients', 'clients.id = marketing_leads.client_id', 'left')
            ->join('projects', 'projects.id = marketing_leads.project_id', 'left')
            ->where('marketing_leads.deleted_at IS NULL');

        if ($search) {
            $b->groupStart()
                ->like('marketing_leads.name', $search)
                ->orLike('marketing_leads.phone', $search)
                ->orLike('marketing_leads.email', $search)
                ->orLike('marketing_leads.campaign_name', $search)
                ->orLike('clients.name', $search)
                ->groupEnd();
        }
        if ($clientId) $b->where('marketing_leads.client_id', $clientId);
        if ($status)   $b->where('marketing_leads.status', $status);
        if ($platform) $b->where('marketing_leads.platform', $platform);

        $total = (clone $b)->countAllResults();
        $data = $b->orderBy('marketing_leads.created_at', 'DESC')->limit($length, $start)->get()->getResultArray();
        return compact('total', 'data') + ['filtered' => $total];
    }

    public function getForClient(int $clientId, int $projectId = 0, string $status = '') {
        $b = $this->where('client_id', $clientId);
        if ($projectId) $b->where('project_id', $projectId);
        if ($status)    $b->where('status', $status);
        return $b->orderBy('created_at', 'DESC')->findAll();
    }

    public function getStatusCounts(int $clientId): array {
        $rows = $this->db->table('marketing_leads')
            ->select('status, COUNT(*) as count')
            ->where('client_id', $clientId)->where('deleted_at IS NULL')
            ->groupBy('status')->get()->getResultArray();
        return array_column($rows, 'count', 'status');
    }
}
