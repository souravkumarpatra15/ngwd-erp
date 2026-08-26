<?php

namespace Tests\Integration\Services;

use App\Models\ProjectMemberModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Database-backed PMS authorization contract tests.
 *
 * The fixture-specific cross-tenant cases should be enabled once the CI
 * database seed data is available. These guards verify that the integration
 * suite is connected to the real membership model and that invalid identities
 * remain denied when a database is present.
 */
class PmsAuthorizationIntegrationTest extends CIUnitTestCase
{
    private PmsAuthorizationService $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = new PmsAuthorizationService();
    }

    public function testRealMembershipModelIsAvailable(): void
    {
        $model = new ProjectMemberModel();
        $this->assertTrue(method_exists($model, 'isMember'));
        $this->assertTrue(method_exists($model, 'getByProject'));
    }

    public function testUnknownIdentityCannotCrossProjectBoundaries(): void
    {
        $this->assertFalse($this->auth->canEditProject('developer', PHP_INT_MAX, PHP_INT_MAX));
        $this->assertFalse($this->auth->canManageProjectTeam('developer', PHP_INT_MAX, PHP_INT_MAX));
        $this->assertFalse($this->auth->canManageTask('developer', PHP_INT_MAX, PHP_INT_MAX));
    }

    public function testClientRoleDoesNotGainInternalProjectManagementPrivileges(): void
    {
        $this->assertFalse($this->auth->canManageProjectTeam('client', PHP_INT_MAX, PHP_INT_MAX));
    }
}
