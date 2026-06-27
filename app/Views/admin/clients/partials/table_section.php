<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <strong><?= esc($title) ?></strong>
    </div>

    <div class="card-body p-0">
        <?php if (!empty($items)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <th><?= ucwords(str_replace('_', ' ', $col)) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $row): ?>
                            <tr>
                                <?php foreach ($columns as $col): ?>
                                    <td><?= esc($row[$col] ?? '-') ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-3 text-muted small">
                No <?= strtolower(esc($title)) ?> found.
            </div>
        <?php endif; ?>
    </div>
</div>