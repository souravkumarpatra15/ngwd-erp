# NGWebD ERP — PMS Upgrade Status

## Current architecture

NGWebD ERP already contains Clients, Projects, Milestones, Tasks, Kanban, Client Portal, Documents, Activity, Agreements, Invoices and Payments. The PMS upgrade extends those existing modules instead of creating a separate application.

## Implemented in this upgrade sequence

### Client and team foundation
- Multiple client portal users can belong to the same `client_id`.
- Client portal roles: `owner`, `manager`, `member`, `viewer`.
- Existing primary client login remains compatible and is treated as the owner.
- Client user access can be enabled/disabled without destroying history.
- Internal project membership is supported through `project_members`.
- Project roles: project manager, developer, designer, QA, member.
- Project access levels: view, edit, manage.
- Project creator is automatically added as project manager.

### Task workflow

The legacy task statuses were:

- todo
- in_progress
- review
- completed
- hold

They are normalized to:

- todo
- in_progress
- code_review
- qa
- client_review
- blocked
- done
- cancelled

Existing records are mapped safely:

- review -> code_review
- completed -> done
- hold -> blocked

The task controller also exposes Kanban reordering and validates task status values.

### Deliverables

A dedicated milestone/project deliverable lifecycle has been added:

- draft
- in_progress
- submitted
- under_review
- changes_requested
- approved
- rejected

Deliverables support project, milestone, owner, due date, version and review/approval timestamps.

## Existing areas intentionally retained

- Existing project statuses
- Existing milestone payment functionality
- Existing task/milestone relationship
- Existing client portal authentication
- Existing invoices/payments
- Existing documents
- Existing activity logging

## Next PMS phases

1. Integrate client-user management directly into the client detail UI.
2. Integrate project team into the project detail dashboard.
3. Task detail/subtasks.
4. Task comments and attachments.
5. Deliverable file upload and versioning.
6. Client portal deliverable review/approval and change requests.
7. Project activity timeline improvements.
8. Notifications and mentions.
9. PMS dashboard/reporting.
10. Security and authorization audit.
11. Automated tests and regression checks.

## Safety note

Run migrations in a staging/backup environment first. The task status migration changes a MySQL ENUM and performs an explicit data mapping before removing legacy enum values.
