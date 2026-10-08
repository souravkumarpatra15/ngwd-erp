<?php /** Single WhatsApp bubble. Expects $m (formatted row). */ ?>
<?php $mid = (int) ($m['id'] ?? 0); ?>
<div class="bubble <?= ! empty($m['is_me']) ? 'me' : 'them' ?>" data-mid="<?= $mid ?>">
  <?php if (($m['message_type'] ?? '') === 'image' && ! empty($m['media_url'])): ?>
    <?php $src = str_starts_with((string) $m['media_url'], 'http') ? $m['media_url'] : base_url('admin/chat/serve/' . $mid); ?>
    <a href="<?= esc($src) ?>" target="_blank"><img src="<?= esc($src) ?>" class="img-fluid rounded mb-1" style="max-height:220px" alt=""></a>
  <?php endif; ?>
  <?php if (($m['message_type'] ?? '') === 'template'): ?>
    <div class="small fw-semibold"><?= esc($m['template_name'] ?? 'template') ?></div>
    <?php foreach ((array) ($m['template_params'] ?? []) as $p): ?><div class="small"><?= esc($p) ?></div><?php endforeach; ?>
  <?php elseif (trim((string) ($m['message_text'] ?? '')) !== ''): ?>
    <div class="small"><?= esc($m['message_text']) ?></div>
  <?php endif; ?>
  <div class="text-end text-muted" style="font-size:10px"><?= esc($m['clock'] ?? $m['time'] ?? '') ?>
    <?php if (! empty($m['is_me'])): ?>
      <?php if (($m['status'] ?? '') === 'failed'): ?><span class="text-danger" title="Failed">⚠</span>
      <?php elseif (($m['status'] ?? '') === 'read'): ?><i class="tick read" title="Read">✓✓</i>
      <?php elseif (($m['status'] ?? '') === 'delivered'): ?><i class="tick" title="Delivered">✓✓</i>
      <?php else: ?><i class="tick text-muted" title="Sent">✓</i><?php endif; ?>
    <?php endif; ?>
  </div>
  <?php if (! empty($m['is_me']) && ($m['status'] ?? '') === 'failed'): ?>
    <div><button class="btn btn-sm btn-link text-danger p-0 retry-btn" data-mid="<?= $mid ?>">Retry</button></div>
  <?php endif; ?>
</div>
