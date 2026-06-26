<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<?php if (isset($errors) && !empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<div class="card border-0 shadow-sm">
  <div class="card-body">
    <p class="text-muted small">View: client/documents/index — Replace with full view content</p>
  </div>
</div>
<?= $this->endSection() ?>