<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferAcceptancesModel extends Model
{
    protected $table      = 'offer_acceptances';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'offer_letter_id', 'candidate_name', 'candidate_id',
        'accepted_at', 'acceptance_type', 'response_notes',
        'created_at', 'updated_at',
    ];

    public function forOffer(int $offerLetterId): array
    {
        return $this->where('offer_letter_id', $offerLetterId)
            ->orderBy('accepted_at', 'DESC')
            ->findAll();
    }
}
