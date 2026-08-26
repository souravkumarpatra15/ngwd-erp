<?php

namespace Tests\Integration\Services;

use App\Models\ProjectMemberModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Integration\Fixtures\PmsDatabaseTenantFixture;
use Tests\Integration\Fixtures\PmsTenantFixture;

/**
 * Tenant-isolation matrix for the PMS authorization boundary.
 *
 * Fail-closed checks use the static contract fixture. Cross-tenant access
 * checks also use records created in the real CI database schema, avoiding
 * hard-coded primary-key assumptions.
 */
class PmsAuthorizationTenantMatrixTest extends CIUnitTestCase
{
    private PmsAuthorizationService $auth;
    private ProjectMemberModel $members;
    private PmsDatabaseTenantFixture $fixture;
    private array $tenant = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = new PmsAuthorizationService();
        $this->members = new ProjectMemberModel();
        $this->fixture = new PmsDatabaseTenantFixture($this->db);
        $this->tenant = $this->fixture->create();
    }

    protected function tearDown(): void
    {
        $this->fixture->cleanup();
        parent::tearDown();
    }

    public function testFixtureIdentityContractIsDistinct(): void
    {
        $this->assertNotSame($this->tenant['client_a'], $this->tenant['client_b']);
        $this->assertNotSame($this->tenant['project_a'], $this->tenant['project_b']);
        $this->assertNotSame($this->tenant['developer_a'], $this->tenant['developer_b']);
    }

    public function testDeveloperCanAccessOwnProjectButNotOtherTenantProject(): void
    {
        $this->assertTrue($this->members->isMember($this->tenant['project_a'], $this->tenant['developer_a']));
        $this->assertTrue($this->auth->canEditProject('developer', $this->tenant['developer_a'], $this->tenant['project_a']));
        $this->assertTrue($this->auth->canManageTask('developer', $this->tenant['developer_a'], $this->tenant['project_a']));
        $this->assertTrue($this->auth->canViewProject($this->tenant['developer_a'], $this->tenant['project_a']));

        $this->assertFalse($this->members->isMember($this->tenant['project_b'], $this->tenant['developer_a']));
        $this->assertFalse($this->auth->canEditProject('developer', $this->tenant['developer_a'], $this->tenant['project_b']));
        $this->assertFalse($this->auth->canManageTask('developer', $this->tenant['developer_a'], $this->tenant['project_b']));
        $this->assertFalse($this->auth->canViewProject($this->tenant['developer_a'], $this->tenant['project_b']));
    }

    public function testClientProjectBoundaryUsesRealProjectRows(): void
    {
        $this->assertTrue($this->auth->canClientAccessProject($this->tenant['client_a'], $this->tenant['project_a']));
        $this->assertFalse($this->auth->canClientAccessProject($this->tenant['client_a'], $this->tenant['project_b']));
        $this->assertTrue($this->auth->canClientAccessProject($this->tenant['client_b'], $this->tenant['project_b']));
        $this->assertFalse($this->auth->canClientAccessProject($this->tenant['client_b'], $this->tenant['project_a']));
    }

    public function testCrossTenantManagerAccessFailsClosedForUnknownMembership(): void
    {
        $this->assertFalse($this->auth->canEditProject('project_manager', $this->tenant['developer_a'], $this->tenant['project_b']));
        $this->assertFalse($this->auth->canManageProjectTeam('project_manager', $this->tenant['developer_a'], $this->tenant['project_b']));
    }

    public function testStaticInvalidIdentityChecksFailClosed(): void
    {
        $this->assertFalse($this->auth->canClientAccessProject(0, $this->tenant['project_a']));
        $this->assertFalse($this->auth->canClientAccessProject(-1, $this->tenant['project_a']));
        $this->assertFalse($this->auth->canClientAccessProject($this->tenant['client_a'], 0));
        $this->assertFalse($this->auth->canClientAccessProject($this->tenant['client_a'], -1));

        $this->assertFalse($this->auth->canClientAccessDeliverable(0, 1));
        $this->assertFalse($this->auth->canClientAccessDeliverable($this->tenant['client_a'], 0));
    }

    public function testLegacyStaticFixtureContractRemainsDistinct(): void
    {
        $a = PmsTenantFixture::tenantA();
        $b = PmsTenantFixture::tenantB();

        $this->assertNotSame($a['client_id'], $b['client_id']);
        $this->assertNotSame($a['project_id'], $b['project_id']);
        $this->assertNotSame($a['developer_id'], $b['developer_id']);
    }
}
