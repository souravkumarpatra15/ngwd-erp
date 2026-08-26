<?php

namespace Tests\Integration\Services;

use App\Models\ProjectMemberModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Integration\Fixtures\PmsTenantFixture;

/**
 * Tenant-isolation matrix for the PMS authorization boundary.
 *
 * These assertions use the real authorization service and isolated test
 * identities. Tests that require seeded identities are intentionally kept
 * separate from fail-closed checks so missing CI fixtures cannot create a
 * false-positive authorization result.
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

    public function testCrossTenantDeveloperAccessIsDenied(): void
    {
        $projectB = PmsTenantFixture::PROJECT_B;
        $developerA = PmsTenantFixture::DEVELOPER_A;

        $this->assertFalse($this->members->isMember($projectB, $developerA));
        $this->assertFalse($this->auth->canEditProject('developer', $developerA, $projectB));
        $this->assertFalse($this->auth->canManageTask('developer', $developerA, $projectB));
        $this->assertFalse($this->auth->canViewProject($developerA, $projectB));
    }

    public function testCrossTenantManagerAccessFailsClosedForUnknownMembership(): void
    {
        $this->assertFalse($this->auth->canEditProject('project_manager', PmsTenantFixture::DEVELOPER_A, PmsTenantFixture::PROJECT_B));
        $this->assertFalse($this->auth->canManageProjectTeam('project_manager', PmsTenantFixture::DEVELOPER_A, PmsTenantFixture::PROJECT_B));
    }

    public function testClientTenantBoundaryFailsClosedForInvalidIdentity(): void
    {
        $this->assertFalse($this->auth->canClientAccessProject(0, PmsTenantFixture::PROJECT_A));
        $this->assertFalse($this->auth->canClientAccessProject(-1, PmsTenantFixture::PROJECT_A));
        $this->assertFalse($this->auth->canClientAccessProject(PmsTenantFixture::CLIENT_A, 0));
        $this->assertFalse($this->auth->canClientAccessProject(PmsTenantFixture::CLIENT_A, -1));

        $this->assertFalse($this->auth->canClientAccessDeliverable(0, 1));
        $this->assertFalse($this->auth->canClientAccessDeliverable(PmsTenantFixture::CLIENT_A, 0));
    }
}
