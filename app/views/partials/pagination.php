<?php
/** Partial pagination. Expects: $pagination = paginate(...). */
$pg = $pagination ?? ['page' => 1, 'totalPages' => 1];
$p = (int) $pg['page'];
$tp = (int) $pg['totalPages'];
?>
<?php if ($tp > 1): ?>
<nav class="d-flex justify-content-between align-items-center mt-3">
    <span class="text-muted small">Halaman <?= $p ?> dari <?= $tp ?> (<?= (int) ($pg['total'] ?? 0) ?> data)</span>
    <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $p <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(page_url(max(1, $p - 1))) ?>">Sebelumnya</a>
        </li>
        <?php for ($i = 1; $i <= $tp; $i++): ?>
            <li class="page-item <?= $i === $p ? 'active' : '' ?>">
                <a class="page-link" href="<?= e(page_url($i)) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $p >= $tp ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e(page_url(min($tp, $p + 1))) ?>">Berikutnya</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
