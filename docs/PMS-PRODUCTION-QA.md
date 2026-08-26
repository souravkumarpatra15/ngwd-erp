# PMS Production QA Checklist

## Authorization

- [x] Centralized PMS authorization service exists.
- [x] Project team actions use project-scoped authorization.
- [x] Project edit/delete/status operations use project-scoped authorization.
- [x] Task operations use project-scoped authorization.
- [x] Deliverable operations use project-scoped authorization.
- [x] Client approval requires authenticated client context and project/client ownership.
- [x] Invalid identifiers fail closed in unit regression coverage.

## Multi-tenant isolation

- [x] Client portal project access validates the authenticated client's tenant context.
- [x] Deliverable review is project/client scoped.
- [ ] Run integration tests against a fixture database with two clients and cross-tenant access attempts.

## PMS workflow

- [x] Projects
- [x] Project members / developers
- [x] Milestones
- [x] Tasks / Kanban
- [x] Deliverables
- [x] Client approvals
- [x] Project dashboard
- [x] Activity / collaboration foundations

## Test execution required before production

Run from the repository root:

```bash
composer install
vendor/bin/phpunit
```

If the repository's CodeIgniter test bootstrap is not configured for PHPUnit in the deployment environment, configure the CIUnit bootstrap/database fixture before treating the suite as production-gating.

## Manual security scenarios

1. Client A attempts to open Client B project by changing the project ID.
2. Client A attempts to review Client B deliverable by changing the deliverable ID.
3. Developer attempts to manage project members.
4. Developer attempts to approve a client deliverable.
5. Project Manager attempts to modify an unrelated project.
6. Anonymous user attempts every protected PMS endpoint.
7. Submit the same client approval request twice concurrently.

Expected result: unauthorized operations are rejected and no cross-tenant data is exposed or mutated.

## Release gate

This checklist documents the current automated authorization coverage and the remaining environment-dependent integration/manual checks. Do not mark the PMS release fully production-approved until the integration database tests and manual security scenarios have passed.
