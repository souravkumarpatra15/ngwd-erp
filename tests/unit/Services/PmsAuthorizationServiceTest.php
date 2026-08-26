<?php

namespace Tests\Unit\Services;

use App\Models\ProjectMemberModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\Test\CIUnitTestCase;

class PmsAuthorizationServiceTest extends CIUnitTestCase
{
    public function testServiceClassCanBeConstructed(): void
    {
        $service = new PmsAuthorizationService();
        $this->assertInstanceOf(PmsAuthorizationService::class, $service);
    }

    public function testProjectMemberModelExposesMembershipLookup(): void
    {
        $model = new ProjectMemberModel();
        $this->assertTrue(method_exists($model, 'isMember'));
    }

    public function testAuthorizationFailsClosedForUnknownUser(): void
    {
        $service = new PmsAuthorizationService();
        $this->assertFalse($service->canEditProject('developer', 0, 0));
        $this->assertFalse($service->canManageProjectTeam('developer', 0, 0));
        $this->assertFalse($service->canManageTask('developer', 0, 0));
    }
}
