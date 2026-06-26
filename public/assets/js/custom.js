const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

$.ajaxSetup({ headers: { 'X-CSRF-Token': CSRF_TOKEN } });

// Select2
$(document).ready(function () {
  $('select.select2').select2({ theme: 'bootstrap-5', width: '100%' });
  setTimeout(() => document.querySelectorAll('.alert.fade.show').forEach(a => {
    bootstrap.Alert.getOrCreateInstance(a).close();
  }), 5000);
});

// Global search
let searchTimer = null;

$('#globalSearch').on('input', function () {
  clearTimeout(searchTimer);

  const q = $(this).val().trim();
  $('#globalSearchResults').remove();
  if (q.length < 2) return;
  searchTimer = setTimeout(() => {
    const BASE_URL = document.querySelector('meta[name="base-url"]')?.getAttribute('data-base-url') || '/';

    $.get(BASE_URL + 'admin/search', { q: q }, function (res) {
      $('#globalSearchResults').remove();
      let html = `
        <div id="globalSearchResults" class="dropdown-menu show shadow border-0"
             style="position:absolute;top:52px;left:0;width:320px;border-radius:16px;z-index:9999;max-height:360px;overflow-y:auto;">
      `;
      if (!res || res.length === 0) {
        html += `
          <div class="px-3 py-4 text-center text-muted small">
            <i class="bi bi-search d-block fs-3 mb-2"></i>
            No results found
          </div>
        `;
      } else {
        res.forEach(item => {
          html += `
            <a href="${item.url}" class="dropdown-item py-2">
              <div class="fw-semibold small">${item.title}</div>
              <div class="text-muted" style="font-size:12px">${item.type}</div>
            </a>
          `;
        });
      }

      html += `</div>`;

      $('#globalSearch').parent().css('position', 'relative').append(html);

    }, 'json');
  }, 300);
});

$(document).on('click', function (e) {
  if (!$(e.target).closest('#globalSearch, #globalSearchResults').length) {
    $('#globalSearchResults').remove();
  }
});

// Custom Confirm Delete Modal
let ngConfirmTarget = null;

$(document).on('click', '[data-confirm]', function (e) {
  e.preventDefault();

  ngConfirmTarget = this;

  const message = $(this).data('confirm') || 'This action cannot be undone.';
  const title = $(this).data('confirm-title') || 'Are you sure?';
  const yesText = $(this).data('confirm-yes') || 'Yes, Delete';

  $('#ngConfirmTitle').text(title);
  $('#ngConfirmMessage').text(message);
  $('#ngConfirmYes').text(yesText);

  const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal'));
  modal.show();
});

$('#ngConfirmYes').on('click', function () {
  if (!ngConfirmTarget) return;

  const modalEl = document.getElementById('ngConfirmModal');
  const modal = bootstrap.Modal.getInstance(modalEl);

  modal.hide();

  const href = $(ngConfirmTarget).attr('href');

  if ($(ngConfirmTarget).is('button[type="submit"], input[type="submit"]')) {
    $(ngConfirmTarget).closest('form').trigger('submit');
    return;
  }

  if ($(ngConfirmTarget).closest('form').length && !href) {
    $(ngConfirmTarget).closest('form').trigger('submit');
    return;
  }

  if (href && href !== '#') {
    showLoader('Deleting...');
    window.location.href = href;
  }

  ngConfirmTarget = null;
});


// Copy to clipboard - stylish
$(document).on('click', '[data-copy]', function () {
  const text = $(this).data('copy');

  if (!text) {
    showToast('Nothing to copy', 'warning');
    return;
  }

  navigator.clipboard.writeText(text)
    .then(() => {
      showToast('Copied to clipboard', 'success');
    })
    .catch(() => {
      showToast('Copy failed. Please try again.', 'error');
    });
});