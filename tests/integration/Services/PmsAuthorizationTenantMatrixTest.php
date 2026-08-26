<?php

namespace Tests\Integration\Services;

use App\Models\ProjectMemberModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Integration\Fixtures\PmsTenantFixture;

/**
 * Tenant-isolation matrix for the PMS authorization boundary.
 *
 * These assertions use the real authorization service and the isolated test
 * identities. They deliberately fail closed until those identities exist in
 * the CI database, preventing a false-positive security suite.
 */
class PmsAuthorizationTenantMatrixTest extends CIUnitTestCase
{
    private PmsAuthorizationService $auth;
    private ProjectMemberModel $members;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = new PmsAuthorizationService();
        $this->members = new ProjectMemberModel();
    }

    public function testFixtureIdentityContractIsDistinct(): void
    {
        $a = PmsTenantFixture::tenantA();
        $b = PmsTenantFixture::tenantB();

        $this->assertNotSame($a['client_id'], $b['client_id']);
        $this->assertNotSame($a['project_id'], $b['project_id']);
        $this->assertNotSame($a['developer_id'], $b['developer_id']);
    }

    public function testCrossTenantDeveloperAccessIsDeniedWhenFixtureIsAbsent(): void
    {
        $projectA = PmsTenantFixture::PROJECT_A;
        $projectB = PmsTenantFixture::PROJECT_B;
        $developerA = PmsTenantFixture::DEVELOPER_A;

        $this->assertFalse($this->members->isMember($projectB, $developerA));
        $this->assertFalse($this->auth->canEditProject('developer', $developerA, $projectB));
        $this->assertFalse($this->auth->canManageTask('developer', $developerA, $projectB));
        $this->assertFalse($this->auth->canViewProject($developerA, $projectB));
        $this->assertFalse($this->auth->canEditProject('developer', $developerA, $projectA));
    }

    public function testCrossTenantManagerAccessFailsClosedForUnknownMembership(): void
    {
        $this->assertFalse($this->auth->canEditProject('project_manager', PmsTenantFixture::DEVELOPER_A, PmsTenantFixture::PROJECT_B));
        $this->assertFalse($this->auth->canManageProjectTeam('project_manager', PmsTenantFixture::DEVELOPER_A, PmsTenantFixture::PROJECT_B));
    }
}
