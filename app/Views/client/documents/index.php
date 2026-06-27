<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php if (empty($documents)): ?>
<div class="text-center text-muted py-5">
  <i class="bi bi-folder fs-2 d-block mb-2 opacity-25"></i>No documents shared yet.
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>File</th><th>Category</th><th>Size</th><th>Date</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($documents as $doc): ?>
        <?php
          $ext = strtolower(pathinfo($doc['file_name'],PATHINFO_EXTENSION));
          $icon = match($ext) {
            'pdf' => 'bi-file-earmark-pdf text-danger',
            'doc','docx' => 'bi-file-earmark-word text-primary',
            'xls','xlsx','csv' => 'bi-file-earmark-excel text-success',
            'png','jpg','jpeg','gif' => 'bi-file-earmark-image text-info',
            'zip','rar' => 'bi-file-earmark-zip text-warning',
            default => 'bi-file-earmark text-muted',
          };
        ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="bi <?= $icon ?> fs-5"></i>
              <div>
                <div class="fw-semibold small"><?= esc($doc['title'] ?: $doc['file_name']) ?></div>
                <div class="text-muted" style="font-size:11px"><?= esc($doc['file_name']) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$doc['category']??'other')) ?></span></td>
          <td class="small text-muted"><?= round(($doc['file_size']??0)/1024,1) ?> KB</td>
          <td class="small text-muted"><?= date('d M Y',strtotime($doc['created_at'])) ?></td>
          <td>
            <a href="<?= base_url('portal/documents/download/'.$doc['id']) ?>" class="btn btn-xs btn-outline-primary">
              <i class="bi bi-download me-1"></i>Download
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
