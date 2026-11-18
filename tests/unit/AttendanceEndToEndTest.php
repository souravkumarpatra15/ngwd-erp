<?php
define('TESTPATH', __DIR__ . '/_support');
namespace Tests\Unit;
use CodeIgniter\Test\CIUnitTestCase;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;

class AttendanceEndToEndTest extends CIUnitTestCase
{
    protected AttendanceModel $attendanceModel;
    protected EmployeeModel $employeeModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceModel = new AttendanceModel();
        $this->employeeModel = new EmployeeModel();
    }

    public function test_successful_check_in(): void
    {
        $employee = $this->employeeModel->first();
        $this->assertNotEmpty($employee);
        $employeeId = $employee['id'];
        $existing = $this->attendanceModel->forEmployee($employeeId)->today($employeeId);
        $this->assertNull($existing);
        $result = $this->attendanceModel->checkIn($employeeId);
        $this->assertTrue($result);
        $record = $this->attendanceModel->forEmployee($employeeId)->today($employeeId);
        $this->assertNotNull($record);
        $this->assertNotNull($record['check_in']);
        $this->assertNull($record['check_out']);
        $this->assertEquals('pending_approval', $record['status']);
    }

    public function test_duplicate_check_in_prevented(): void
    {
        $employee = $this->employeeModel->first();
        $employeeId = $employee['id'];
        $result1 = $this->attendanceModel->checkIn($employeeId);
        $this->assertTrue($result1);
        $existing = $this->attendanceModel->forEmployee($employeeId)->today($employeeId);
        $this->assertNotNull($existing);
        $todayRecords = $this->attendanceModel->where('employee_id', $employeeId)
            ->where('attendance_date', date('Y-m-d'))
            ->countAll();
        $this->assertEquals(1, $todayRecords, 'Should only have one attendance record per day');
    }

    public function test_successful_check_out(): void
    {
        $employee = $this->employeeModel->first();
        $employeeId = $employee['id'];
        $this->attendanceModel->checkIn($employeeId);
        $result = $this->attendanceModel->checkOut($employeeId);
        $this->assertNotNull($result);
        $this->assertNotNull($result['check_in']);
        $this->assertNotNull($result['check_out']);
        $this->assertEquals('present', $result['status']);
        $this->assertGreaterThan(0, $result['duration_minutes']);
    }

    public function test_invalid_check_out_prevented(): void
    {
        $employee = $this->employeeModel->first();
        $employeeId = $employee['id'];
        $result = $this->attendanceModel->checkOut($employeeId);
        $this->assertNull($result);
    }

    public function test_wfh_attendance(): void
    {
        $employee = $this->employeeModel->first();
        $employeeId = $employee['id'];
        $this->attendanceModel->checkIn($employeeId);
        $this->attendanceModel->update(
            $this->attendanceModel->forEmployee($employeeId)->today($employeeId)['id'],
            ['work_mode' => 'work_from_home']
        );
        $record = $this->attendanceModel->forEmployee($employeeId)->today($employeeId);
        $this->assertEquals('work_from_home', $record['work_mode']);
    }

    public function test_monthly_attendance_summary(): void
    {
        $employee = $this->employeeModel->first();
        $employeeId = $employee['id'];
        $month = date('Y-m');
        $summary = $this->attendanceModel->getMonthlySummary($employeeId, $month);
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('attendance_percentage', $summary);
    }

    public function test_status_counts(): void
    {
        $employee = $this->employeeModel->first();
        $employeeId = $employee['id'];
        $month = date('Y-m');
        $statusCounts = $this->attendanceModel->getStatusCounts($employeeId, $month);
        $this->assertIsArray($statusCounts);
    }

    public function test_today_status(): void
    {
        $employee = $this->employeeModel->first();
        $employeeId = $employee['id'];
        $status = $this->attendanceModel->getTodayStatus($employeeId);
        $this->assertIsArray($status);
    }
}