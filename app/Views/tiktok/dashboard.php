<form method="get" action="<?= base_url('dashboard/tiktok') ?>" class="filter-bar">
    <div class="form-group">
        <label for="date">Tanggal</label>
        <input type="date" id="date" name="date" value="<?= esc($date) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Terapkan</button>
</form>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>URL</th>
            <th>Creator</th>
            <th>Source</th>
            <th>Views</th>
            <th>Likes</th>
            <th>Comments</th>
            <th>Shares</th>
            <th>Tanggal Data</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($links)): ?>
        <tr><td colspan="8" class="text-muted">Belum ada link TikTok.</td></tr>
        <?php endif; ?>
        <?php foreach ($links as $link): $insight = $link['insight']; ?>
        <tr>
            <td><a href="<?= esc($link['url']) ?>" target="_blank" rel="noopener"><?= esc($link['url']) ?></a></td>
            <td><?= esc($link['creator_handle'] ?? '-') ?></td>
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
