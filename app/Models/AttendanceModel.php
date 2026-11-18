<?php
namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table      = 'attendance';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employee_id', 'check_in', 'check_out',
        'work_from_home', 'work_from_home_notes',
        'date', 'day_type', 'status', 'hours_worked',
        'created_at', 'updated_at',
    ];

    // ── Scopes ──────────────────────────────────────────────
    public function forEmployee(int $employeeId = null)
    {
        if ($employeeId === null) return $this;
        return $this->where('employee_id', $employeeId);
    }

    /** Today's record for an employee (NULL when not checked in yet) */
    public function today(int $employeeId = null): ?array
    {
        if ($employeeId === null) return null;
        return $this->where('employee_id', $employeeId)
            ->where('date', date('Y-m-d'))
            ->first() ?: null;
    }

    public function thisMonth(int $employeeId = null, string $month = null): array
    {
        if ($employeeId === null) return [];
        $month = $month ?? date('Y-m');
        return $this->where('employee_id', $employeeId)
            ->where('date LIKE', $month . '-%')
            ->orderBy('date', 'DESC')
            ->findAll();
    }

    // ── Check-in / check-out ────────────────────────────────
    /** Create today's record with a check-in. Returns insert id or false when already checked in. */
    public function checkIn(int $employeeId, bool $workFromHome = false, string $notes = ''): int|false
    {
        if ($this->today($employeeId)) {
            return false; // duplicate check-in
        }
        return (int) $this->insert([
            'employee_id'            => $employeeId,
            'date'                   => date('Y-m-d'),
            'check_in'               => date('Y-m-d H:i:s'),
            'status'                 => $workFromHome ? 'work_from_home' : 'present',
            'work_from_home'         => $workFromHome ? 1 : 0,
            'work_from_home_notes'   => $workFromHome ? $notes : null,
            'day_type'               => 'working',
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ], true);
    }

    /** Close today's record with a check-out. Returns the updated row or null. */
    public function checkOut(int $employeeId): ?array
    {
        $row = $this->today($employeeId);
        if (!$row || !empty($row['check_out'])) {
            return null; // no record or already checked out
        }
        $this->update($row['id'], [
            'check_out'  => date('Y-m-d H:i:s'),
            'hours_worked' => round((time() - strtotime($row['check_in'])) / 3600, 2),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->find($row['id']);
    }

    // ── Monthly summary ─────────────────────────────────────
    public function getStatusCounts(int $employeeId = null, string $month = null): array
    {
        if ($employeeId === null) return [];
        $month = $month ?? date('Y-m');
        $rows = $this->select('status, COUNT(*) as cnt')
            ->where('employee_id', $employeeId)
            ->where('date LIKE', $month . '-%')
            ->groupBy('status')
            ->findAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['status']] = (int) $row['cnt'];
        }
        return $result;
    }

    /** present_days / working_days x 100 (holidays & week-offs excluded from working_days) */
    public function getMonthlySummary(int $employeeId = null, string $month = null): array
    {
        if ($employeeId === null) {
            return ['working_days' => 0, 'present_days' => 0, 'absent_days' => 0, 'percentage' => 0, 'total_hours' => 0];
        }
        $month = $month ?? date('Y-m');

        $rows = $this->select("SUM(CASE WHEN day_type = 'working' THEN 1 ELSE 0 END) as working_days,
            SUM(CASE WHEN status IN ('present','work_from_home') THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
            COALESCE(SUM(hours_worked), 0) as total_hours")
            ->where('employee_id', $employeeId)
            ->where('date LIKE', $month . '-%')
            ->get()->getRowArray() ?: [];

        $workingDays = (int) ($rows['working_days'] ?? 0);
        $presentDays = (int) ($rows['present_days'] ?? 0);

        return [
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'absent_days'  => (int) ($rows['absent_days'] ?? 0),
            'total_hours'  => (float) ($rows['total_hours'] ?? 0),
            'percentage'   => $workingDays > 0 ? round($presentDays / $workingDays * 100, 1) : 0,
        ];
    }
}
