<div class="app-topbar" style="margin-bottom: var(--space-4)">
    <div></div>
    <?php if ($canEdit): ?>
    <a href="<?= base_url('tiktok/links/new') ?>" class="btn btn-primary">+ Tambah Link</a>
    <?php endif; ?>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>URL</th>
            <th>Creator</th>
            <th>Catatan</th>
            <th>Source</th>
            <th>Views</th>
            <th>Likes</th>
            <th>Comments</th>
            <th>Shares</th>
            <th>Update Terakhir</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($links)): ?>
        <tr><td colspan="9" class="text-muted">Belum ada link TikTok.</td></tr>
        <?php endif; ?>
        <?php foreach ($links as $link): $insight = $link['latest_insight']; ?>
        <tr>
            <td><a href="<?= esc($link['url']) ?>" target="_blank" rel="noopener"><?= esc($link['url']) ?></a></td>
            <td><?= esc($link['creator_handle'] ?? '-') ?></td>
            <td><?= esc($link['affiliate_note'] ?? '-') ?></td>
            <td><span class="badge badge-<?= $link['data_source'] === 'oauth' ? 'success' : 'warning' ?>"><?= esc($link['data_source']) ?></span></td>
            <td><?= $insight['views'] ?? '-' ?></td>
            <td><?= $insight['likes'] ?? '-' ?></td>
            <td><?= $insight['comments'] ?? '-' ?></td>
            <td><?= $insight['shares'] ?? '-' ?></td>
            <td><?= esc($insight['snapshot_date'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
