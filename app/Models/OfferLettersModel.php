<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferLettersModel extends Model
{
    protected $table      = 'offer_letters';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'application_id', 'candidate_name', 'candidate_address',
        'position', 'program_name', 'duration', 'start_date', 'end_date',
        'work_mode', 'weekly_commitment', 'compensation_disclosure',
        'learning_objectives', 'terms_conditions', 'authorized_signatory',
        'company_branding', 'offer_reference', 'issue_date',
        'acceptance_deadline', 'status', 'pdf_path', 'joined_at',
        'created_at', 'updated_at',
    ];

    public function forApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getByReference(string $reference): ?array
    {
        return $this->where('offer_reference', $reference)->first() ?: null;
    }
}
