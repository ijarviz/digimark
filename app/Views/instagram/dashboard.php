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

<div class="filter-bar">
    <div class="form-group">
        <label>Tampilan</label>
        <div>
            <label><input type="radio" name="view-mode" value="organic" checked> Organik</label>
            &nbsp;&nbsp;
            <label><input type="radio" name="view-mode" value="ads"> Ads</label>
            &nbsp;&nbsp;
            <label><input type="radio" name="view-mode" value="combined"> Gabungan</label>
        </div>
    </div>
</div>

<div id="organic-section">
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
        <h2>Insight per Konten (Organik)</h2>
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
</div>

<div id="ads-section" hidden>
    <div class="card-row">
        <div class="card">
            <div class="stat-label">Total Ad Reach</div>
            <div class="stat-value" id="stat-ad-reach">-</div>
        </div>
        <div class="card">
            <div class="stat-label">Total Ad Impressions</div>
            <div class="stat-value" id="stat-ad-impressions">-</div>
        </div>
        <div class="card">
            <div class="stat-label">Total Spend</div>
            <div class="stat-value" id="stat-ad-spend">-</div>
        </div>
    </div>

    <div class="card">
        <h2>Performa Campaign</h2>
        <table id="ads-table">
            <thead>
            <tr>
                <th>Campaign</th>
                <th>Campaign ID</th>
                <th>Reach</th>
                <th>Impressions</th>
                <th>Spend</th>
                <th>Konten Terkait</th>
            </tr>
            </thead>
            <tbody>
            <tr><td colspan="6" class="text-muted">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
window.__IG_CHART_DATA_URL__ = <?= json_encode(base_url('dashboard/instagram/chart-data') . '?from=' . $from . '&to=' . $to) ?>;
</script>
