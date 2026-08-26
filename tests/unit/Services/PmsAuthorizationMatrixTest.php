<?php

namespace Tests\Unit\Services;

use App\Models\ProjectMemberModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Regression matrix for the PMS authorization contract.
 *
 * These tests intentionally verify the service's public contract and fail-closed
 * behavior without requiring a live database. Database-backed membership cases
 * belong in the integration suite when the CI database fixture is available.
 */
class PmsAuthorizationMatrixTest extends CIUnitTestCase
{
    private PmsAuthorizationService $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = new PmsAuthorizationService();
    }

    public function testPrivilegedInternalRolesAreRecognized(): void
    {
        $this->assertTrue($this->auth->isPrivilegedInternal('admin'));
        $this->assertTrue($this->auth->isPrivilegedInternal('superadmin'));
        $this->assertFalse($this->auth->isPrivilegedInternal('developer'));
        $this->assertFalse($this->auth->isPrivilegedInternal('client'));
    }

    public function testInvalidProjectIdentifiersFailClosed(): void
    {
        foreach (['developer', 'manager', 'client', ''] as $role) {
            $this->assertFalse($this->auth->canEditProject($role, 0, 0));
            $this->assertFalse($this->auth->canManageProjectTeam($role, 0, 0));
            $this->assertFalse($this->auth->canManageTask($role, 0, 0));
        }
    }

    public function testInvalidTaskIdentifiersFailClosed(): void
    {
        $this->assertFalse($this->auth->canManageTask('developer', 0, 0));
        $this->assertFalse($this->auth->canManageTask('project_manager', 0, 0));
    }

    public function testMembershipModelContractExists(): void
    {
        $model = new ProjectMemberModel();
        $this->assertTrue(method_exists($model, 'isMember'));
        $this->assertTrue(method_exists($model, 'getByProject'));
    }
}
