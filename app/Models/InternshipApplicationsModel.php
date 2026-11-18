<?php
namespace App\Models;

use CodeIgniter\Model;

class InternshipApplicationsModel extends Model
{
    protected $table      = 'internship_applications';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'program_id', 'applicant_name', 'applicant_email', 'applicant_mobile',
        'applicant_city_state', 'education_qualification', 'college_university',
        'graduation_year', 'preferred_role', 'technical_skills',
        'github_profile', 'portfolio_url', 'resume_file_path',
        'profile_photograph', 'laptop_available', 'internet_available',
        'weekly_availability', 'motivation_statement', 'consent_privacy',
        'status', 'reference_number', 'source', 'notes',
        'applied_at', 'created_at', 'updated_at',
    ];

    /**
     * Applications joined with programme details.
     * Chainable: attaches the join to the model's shared builder so chained
     * where()/orderBy()/paginate()/findAll() keep working (Model methods included).
     * The programme join is a derived table exposing only title/mode/duration,
     * so unqualified WHERE columns (status, program_id) are never ambiguous.
     */
    public function withProgram()
    {
        $programSub = $this->db->table('internship_programs')
            ->select('id, title as program_title, mode, duration_months');

        $this->builder()
            ->join('(' . $programSub->getCompiledSelect() . ') as program', 'program.id = internship_applications.program_id', 'left')
            ->select('internship_applications.*, program.program_title, program.mode as program_mode, program.duration_months');

        return $this;
    }

    public function byReference(string $reference): ?array
    {
        return $this->where('reference_number', $reference)->first() ?: null;
    }
}
