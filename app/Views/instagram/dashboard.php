<form method="get" action="<?= base_url('dashboard/instagram') ?>" class="filter-bar" id="date-filter-form">
    <div class="form-group">
        <label for="from">Dari Tanggal</label>
        <input type="date" id="from" name="from" value="<?= esc($from) ?>">
    </div>
    <div class="form-group">
        <label for="to">Sampai Tanggal</label>
        <input type="date" id="to" name="to" value="<?= esc($to) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Terapkan</button>
</form>

<div class="card-row">
    <div class="card">
        <div class="stat-label">Follower Terbaru</div>
        <div class="stat-value" id="stat-followers">-</div>
    </div>
    <div class="card">
        <div class="stat-label">Total Profile Visits</div>
        <div class="stat-value" id="stat-visits">-</div>
    </div>
    <div class="card">
        <div class="stat-label">Total Reach (Profil)</div>
        <div class="stat-value" id="stat-reach">-</div>
    </div>
</div>

<div class="card">
    <h2>Follower &amp; Profile Visit Harian</h2>
    <canvas id="profile-chart" height="90"></canvas>
</div>

<div class="card">
    <h2>Insight per Konten</h2>
    <table id="content-table">
        <thead>
        <tr>
            <th>Konten</th>
            <th>Tipe</th>
            <th>Diposting</th>
            <th>Reach</th>
            <th>Impressions</th>
            <th>Likes</th>
            <th>Comments</th>
            <th>Shares</th>
            <th>Saves</th>
            <th>Plays</th>
        </tr>
        </thead>
        <tbody>
        <tr><td colspan="10" class="text-muted">Memuat data...</td></tr>
        </tbody>
    </table>
</div>

<script>
window.__IG_CHART_DATA_URL__ = <?= json_encode(base_url('dashboard/instagram/chart-data') . '?from=' . $from . '&to=' . $to) ?>;
</script>
