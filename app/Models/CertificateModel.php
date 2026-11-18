<?php
namespace App\Models;

use CodeIgniter\Model;

class CertificateModel extends Model
{
    protected $table      = 'certificates';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'internship_application_id', 'certificate_number', 'student_name',
        'internship_program', 'duration_months', 'final_evaluation',
        'mentor_approval', 'hr_approval', 'issued_at',
        'revoked', 'revoked_at', 'revocation_reason',
        'verification_hash', 'qr_code_path',
    ];

    // ── Derived status ──────────────────────────────────────
    // The certificates table has no status column. Status derives from
    // the `revoked` and `hr_approval` flags:
    //   revoked = 1        -> 'revoked'
    //   hr_approval = 1    -> 'active' (issued & verifiable)
    //   otherwise          -> 'pending' (awaiting mentor/HR approval)
    public function deriveStatus(array $row): array
    {
        if (!isset($row['revoked']) && !isset($row['hr_approval'])) {
            return $row;
        }
        $row['status'] = ((int) ($row['revoked'] ?? 0) === 1)
            ? 'revoked'
            : ((int) ($row['hr_approval'] ?? 0) === 1 ? 'active' : 'pending');
        return $row;
    }

    // ── Lookups ─────────────────────────────────────────────
    public function findByCertificateNumber(string $certNumber): ?array
    {
        $row = $this->where('certificate_number', $certNumber)->first();
        return $row ? $this->deriveStatus($row) : null;
    }

    public function findByApplicationId(int $applicationId): ?array
    {
        $row = $this->where('internship_application_id', $applicationId)
            ->orderBy('created_at', 'DESC')
            ->first();
        return $row ? $this->deriveStatus($row) : null;
    }

    // ── Generation helpers ──────────────────────────────────
    public function generateCertificateNumber(): string
    {
        do {
            $number = 'NGW-CERT-' . date('Y') . '-' . str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->where('certificate_number', $number)->countAllResults() > 0);
        return $number;
    }

    /** Stable verification hash — sha256 of the certificate number with a fixed salt */
    public function generateVerificationHash(string $certificateNumber): string
    {
        return hash('sha256', $certificateNumber . '-ngwebd-cert');
    }

    // ── Status helpers ──────────────────────────────────────
    public function approveHr(int $id): bool
    {
        return $this->update($id, [
            'hr_approval' => 1,
            'issued_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function markRevoked(int $id, string $reason = ''): bool
    {
        return $this->update($id, [
            'revoked'           => 1,
            'revoked_at'        => date('Y-m-d H:i:s'),
            'revocation_reason' => $reason,
        ]);
    }

    public function isVerifiable(array $row): bool
    {
        return (int) ($row['revoked'] ?? 0) === 0 && (int) ($row['hr_approval'] ?? 0) === 1;
    }
}
