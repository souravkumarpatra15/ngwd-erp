<?php

namespace Tests\Integration\Fixtures;

/**
 * Minimal tenant fixture contract for PMS integration tests.
 *
 * The fixture intentionally uses explicit IDs and is kept separate from
 * production seeders. Wire these IDs to the CI schema/seed layer once the
 * corresponding users/projects are available in the test database.
 */
final class PmsTenantFixture
{
    public const CLIENT_A = 10001;
    public const CLIENT_B = 10002;
    public const PROJECT_A = 20001;
    public const PROJECT_B = 20002;
    public const DEVELOPER_A = 30001;
    public const DEVELOPER_B = 30002;

    public static function tenantA(): array
    {
        return ['client_id' => self::CLIENT_A, 'project_id' => self::PROJECT_A, 'developer_id' => self::DEVELOPER_A];
    }

    public static function tenantB(): array
    {
        return ['client_id' => self::CLIENT_B, 'project_id' => self::PROJECT_B, 'developer_id' => self::DEVELOPER_B];
    }
}
