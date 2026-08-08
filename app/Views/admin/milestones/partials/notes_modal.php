<!-- Milestone Notes / Q&A Modal (shared) -->
<div class="modal fade" id="msNotesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h6 class="modal-title fw-semibold"><i class="bi bi-chat-left-text me-2 text-primary"></i>Notes — <span id="msNotesTitle"></span></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="msNotesThread" class="mb-3" style="max-height:320px;overflow-y:auto;">
          <div class="text-center text-muted small py-3">Loading…</div>
        </div>
        <form id="msNoteForm" class="d-flex gap-2">
          <input type="hidden" id="msNoteMilestoneId" value="">
          <input type="text" id="msNoteMessage" class="form-control" placeholder="Write a note…" required>
          <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  const NOTES_BASE = '<?= base_url('admin/milestones/notes/') ?>';
  function esc(s) { return $('<div>').text(s || '').html(); }
  function renderThread(notes) {
    if (!notes.length) return '<div class="text-center text-muted small py-3">No notes yet — ask a question or leave an update.</div>';
    return notes.map(n => `
      <div class="d-flex mb-2 ${n.is_admin == 1 ? 'justify-content-end' : ''}">
        <div class="p-2 rounded-3 ${n.is_admin == 1 ? 'bg-primary text-white' : 'bg-light border'}" style="max-width:80%;">
          <div class="small ${n.is_admin == 1 ? 'text-white-50' : 'text-muted'}">${esc(n.user_name || (n.is_admin == 1 ? 'You' : 'Client'))} · ${new Date(n.created_at).toLocaleString('en-IN', {day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'})}</div>
          <div>${esc(n.message)}</div>
        </div>
      </div>`).join('');
  }
  function loadThread(id) {
    $('#msNotesThread').html('<div class="text-center text-muted small py-3">Loading…</div>');
    $.get(NOTES_BASE + id, res => {
      $('#msNotesThread').html(res.success ? renderThread(res.notes) : '<div class="text-center text-danger small py-3">Could not load notes.</div>');
      $('#msNotesThread').scrollTop($('#msNotesThread')[0].scrollHeight);
    }).fail(() => {
      $('#msNotesThread').html('<div class="text-center text-danger small py-3">Could not load notes.</div>');
    });
  }
  $(document).on('click', '.btn-ms-notes', function () {
    const id = $(this).data('id');
    $('#msNotesTitle').text($(this).data('title') || '');
    $('#msNoteMilestoneId').val(id);
    loadThread(id);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('msNotesModal')).show();
  });
  $('#msNoteForm').on('submit', function (e) {
    e.preventDefault();
    const id = $('#msNoteMilestoneId').val();
    const msg = $('#msNoteMessage').val().trim();
    if (!msg) return;
    $.post(NOTES_BASE + id, { message: msg, csrf_test_name: CSRF_TOKEN }, res => {
      if (res.success) { $('#msNoteMessage').val(''); loadThread(id); }
    }).fail(() => {
      alert('Could not send note. Please try again.');
    });
  });
})();
</script>
