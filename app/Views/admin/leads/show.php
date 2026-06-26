<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-end gap-2 mb-3">
  <a href="<?= base_url('admin/leads/edit/' . $lead['id']) ?>" class="btn btn-warning btn-sm">
    <i class="bi bi-pencil me-1"></i>Edit
  </a>
  <a href="<?= base_url('admin/leads') ?>" class="btn btn-light btn-sm">
    Back
  </a>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
          <div>
            <h5 class="fw-bold mb-1"><?= esc($lead['name']) ?></h5>
            <div class="text-muted small"><?= esc($lead['company_name'] ?? '') ?></div>
          </div>

          <span class="badge bg-primary align-self-start">
            <?= ucwords(str_replace('_', ' ', $lead['status'] ?? 'new')) ?>
          </span>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <small class="text-muted">Lead Number</small>
            <div class="fw-semibold"><?= esc($lead['lead_number'] ?? '-') ?></div>
          </div>

          <div class="col-md-6">
            <small class="text-muted">Source</small>
            <div class="fw-semibold"><?= ucwords(str_replace('_', ' ', $lead['source'] ?? '-')) ?></div>
          </div>

          <div class="col-md-6">
            <small class="text-muted">Mobile</small>
            <div><a href="tel:<?= esc($lead['mobile']) ?>"><?= esc($lead['mobile']) ?></a></div>
          </div>

          <div class="col-md-6">
            <small class="text-muted">WhatsApp</small>
            <div><?= esc($lead['whatsapp'] ?? '-') ?></div>
          </div>

          <div class="col-md-6">
            <small class="text-muted">Email</small>
            <div><?= esc($lead['email'] ?? '-') ?></div>
          </div>

          <div class="col-md-6">
            <small class="text-muted">Budget</small>
            <div class="fw-semibold">
              <?= !empty($lead['budget']) ? '₹' . number_format($lead['budget']) : '-' ?>
            </div>
          </div>

          <div class="col-md-6">
            <small class="text-muted">Follow Up Date</small>
            <div><?= esc($lead['follow_up_date'] ?? '-') ?></div>
          </div>

          <div class="col-md-6">
            <small class="text-muted">Created Date</small>
            <div><?= !empty($lead['created_at']) ? date('d M Y', strtotime($lead['created_at'])) : '-' ?></div>
          </div>

          <div class="col-12">
            <small class="text-muted">Requirement</small>
            <div class="p-3 bg-light rounded mt-1"><?= nl2br(esc($lead['requirement'] ?? '-')) ?></div>
          </div>

          <div class="col-12">
            <small class="text-muted">Notes</small>
            <div class="p-3 bg-light rounded mt-1"><?= nl2br(esc($lead['notes'] ?? '-')) ?></div>
          </div>
        </div>

      </div>
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h6 class="fw-bold mb-1">
                <i class="bi bi-clock-history text-primary me-1"></i>
                Activity Timeline
              </h6>
              <div class="text-muted small">All follow-ups, calls, notes and communication history</div>
            </div>

            <span class="badge bg-light text-dark">
              <?= count($activities ?? []) ?> Activities
            </span>
          </div>

          <?php if (!empty($activities)): ?>
            <div class="lead-activity-timeline">

              <?php foreach ($activities as $activity): ?>
                <?php
                $type = $activity['action'] ?? 'note';

                $icons = [
                  'call'     => 'bi-telephone-fill',
                  'whatsapp' => 'bi-whatsapp',
                  'email'    => 'bi-envelope-fill',
                  'meeting'  => 'bi-people-fill',
                  'note'     => 'bi-journal-text',
                ];

                $colors = [
                  'call'     => 'primary',
                  'whatsapp' => 'success',
                  'email'    => 'info',
                  'meeting'  => 'warning',
                  'note'     => 'secondary',
                ];

                $icon = $icons[$type] ?? 'bi-journal-text';
                $color = $colors[$type] ?? 'secondary';
                ?>

                <div class="activity-item">
                  <div class="activity-icon bg-<?= $color ?>">
                    <i class="bi <?= $icon ?>"></i>
                  </div>

                  <div class="activity-content">
                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                      <div>
                        <div class="fw-semibold">
                          <?= ucwords(str_replace('_', ' ', $type)) ?>
                        </div>

                        <div class="text-muted small">
                          By <?= esc($activity['user_name'] ?? 'System') ?>
                          •
                          <?= !empty($activity['created_at']) ? date('d M Y, h:i A', strtotime($activity['created_at'])) : '-' ?>
                        </div>
                      </div>

                      <?php if (!empty($activity['follow_up_date'])): ?>
                        <span class="badge bg-warning text-dark align-self-start">
                          Follow up: <?= date('d M Y', strtotime($activity['follow_up_date'])) ?>
                        </span>
                      <?php endif; ?>
                    </div>

                    <?php if (!empty($activity['notes'])): ?>
                      <div class="activity-note mt-2">
                        <?= nl2br(esc($activity['notes'])) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

              <?php endforeach; ?>

            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <div class="empty-activity-icon mb-3">
                <i class="bi bi-clock-history"></i>
              </div>
              <h6 class="fw-bold mb-1">No activity yet</h6>
              <p class="text-muted small mb-0">Activity will appear here after you add calls, notes, emails or follow-ups.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body p-4">
        <h6 class="fw-bold mb-3">Quick Actions</h6>

        <form action="<?= base_url('admin/leads/convert/' . $lead['id']) ?>" method="POST" class="mb-2">
          <?= csrf_field() ?>
          <button class="btn btn-success w-100"
            <?= $lead['status'] === 'converted' ? 'disabled' : '' ?>>

            <?= $lead['status'] === 'converted'
              ? '<i class="bi bi-check-circle-fill"></i> Already Converted'
              : '<i class="bi bi-person-check"></i> Convert to Client' ?>

          </button>
        </form>

        <form action="<?= base_url('admin/leads/send-whatsapp/' . $lead['id']) ?>" method="POST" class="mb-3">
          <?= csrf_field() ?>

          <div class="mb-2">
            <label class="form-label small fw-semibold">WhatsApp Message</label>
            <textarea name="message"
              class="form-control"
              rows="3"
              placeholder="Type WhatsApp message..."
              required></textarea>
          </div>

          <button class="btn btn-outline-success w-100">
            <i class="bi bi-whatsapp me-1"></i> Send WhatsApp
          </button>
        </form>

        <form action="<?= base_url('admin/leads/send-email/' . $lead['id']) ?>" method="POST">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Subject</label>
            <input type="text"
              name="subject"
              class="form-control"
              placeholder="Enter email subject"
              required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Message</label>
            <textarea
              id="emailEditor"
              name="message"
              class="form-control"
              rows="8"></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-envelope-paper-fill me-2"></i>
            Send Email
          </button>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h6 class="fw-bold mb-3">Add Activity</h6>

        <form action="<?= base_url('admin/leads/activity/' . $lead['id']) ?>" method="POST">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Activity Type</label>
            <select name="action" class="form-select" required>
              <option value="">Select Activity</option>
              <option value="call">Call</option>
              <option value="whatsapp">WhatsApp</option>
              <option value="email">Email</option>
              <option value="meeting">Meeting</option>
              <option value="note">Note</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Follow Up Date</label>
            <input type="date" name="follow_up_date" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Note</label>
            <textarea name="notes" class="form-control" rows="3" required></textarea>
          </div>

          <button class="btn btn-primary w-100">
            <i class="bi bi-plus-circle me-1"></i> Save Activity
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>
  ClassicEditor
    .create(document.querySelector('#emailEditor'), {
      toolbar: [
        'undo', 'redo', '|',
        'heading', '|',
        'bold', 'italic', 'underline', 'strikethrough', '|',
        'fontColor', 'fontBackgroundColor', '|',
        'bulletedList', 'numberedList', '|',
        'alignment', '|',
        'link', 'insertTable', 'blockQuote', '|',
        'outdent', 'indent', '|',
        'horizontalLine', 'code', 'removeFormat'
      ],

      table: {
        contentToolbar: [
          'tableColumn',
          'tableRow',
          'mergeTableCells'
        ]
      }
    })
    .catch(error => {
      console.error(error);
    });
</script>