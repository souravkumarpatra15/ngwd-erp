# PMS Task Collaboration

This layer adds collaboration primitives to the existing NGWebD ERP task system.

## Current capabilities

- Task comments
- Internal comments flag for future portal filtering
- Task subtasks with ordering/completion state
- Task activity history
- JSON task collaboration detail endpoint/controller
- Audit events for comments and subtask changes

## Data model

```text
projects
  -> milestones
      -> tasks
          -> task_subtasks
          -> task_comments
          -> task_activities
```

## Migration

Run the CodeIgniter migration:

```bash
php spark migrate
```

This migration creates only the collaboration tables when they do not already exist. It does not modify or delete existing project/task data.

## Next integration

The next PMS stage should connect these primitives to the admin task detail UI and client portal with explicit authorization rules. Internal comments must never be exposed to client users.
