(function () {
    var url = window.__IG_CHART_DATA_URL__;
    if (!url) {
        return;
    }

    fetch(url)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            renderStats(data.profile);
            renderProfileChart(data.profile);
            renderContentTable(data.content);
            renderAdsStats(data.ads || []);
            renderAdsTable(data.ads || []);
        })
        .catch(function (err) {
            console.error('Failed to load IG chart data', err);
        });

    setupViewToggle();

    function setupViewToggle() {
        var organic = document.getElementById('organic-section');
        var ads = document.getElementById('ads-section');
        var radios = document.querySelectorAll('input[name="view-mode"]');

        if (!organic || !ads || !radios.length) {
            return;
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                var mode = this.value;
                organic.hidden = mode === 'ads';
                ads.hidden = mode === 'organic';
            });
        });
    }

    function renderAdsStats(ads) {
        var reach = ads.reduce(function (sum, row) { return sum + Number(row.ad_reach || 0); }, 0);
        var impressions = ads.reduce(function (sum, row) { return sum + Number(row.ad_impressions || 0); }, 0);
        var spend = ads.reduce(function (sum, row) { return sum + Number(row.spend || 0); }, 0);

        var elReach = document.getElementById('stat-ad-reach');
        var elImpressions = document.getElementById('stat-ad-impressions');
        var elSpend = document.getElementById('stat-ad-spend');

        if (elReach) elReach.textContent = reach;
        if (elImpressions) elImpressions.textContent = impressions;
        if (elSpend) elSpend.textContent = spend.toFixed(2);
    }

    function renderAdsTable(ads) {
        var tbody = document.querySelector('#ads-table tbody');
        if (!tbody) {
            return;
        }
        tbody.innerHTML = '';

        if (!ads.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-muted">Tidak ada data campaign pada rentang ini.</td></tr>';
            return;
        }

        ads.forEach(function (row) {
            var tr = document.createElement('tr');
            var linkedContent = row.permalink
                ? '<a href="' + escapeAttr(row.permalink) + '" target="_blank" rel="noopener">' + escapeHtml((row.caption || '').substring(0, 40) || 'Lihat konten') + '</a>'
                : '-';

            tr.innerHTML =
                '<td>' + escapeHtml(row.campaign_name || '(tanpa nama)') + '</td>' +
                '<td>' + escapeHtml(row.campaign_id) + '</td>' +
                '<td>' + row.ad_reach + '</td>' +
                '<td>' + row.ad_impressions + '</td>' +
                '<td>' + Number(row.spend).toFixed(2) + '</td>' +
                '<td>' + linkedContent + '</td>';
            tbody.appendChild(tr);
        });
    }

    function renderStats(profile) {
        var latest = profile.length ? profile[profile.length - 1] : null;
        var totalVisits = profile.reduce(function (sum, row) { return sum + Number(row.profile_visits || 0); }, 0);
        var totalReach = profile.reduce(function (sum, row) { return sum + Number(row.reach || 0); }, 0);

        document.getElementById('stat-followers').textContent = latest ? latest.follower_count : '-';
        document.getElementById('stat-visits').textContent = totalVisits;
        document.getElementById('stat-reach').textContent = totalReach;
    }

    function renderProfileChart(profile) {
        var ctx = document.getElementById('profile-chart');
        if (!ctx || typeof Chart === 'undefined') {
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: profile.map(function (row) { return row.snapshot_date; }),
                datasets: [
                    {
                        label: 'Follower',
                        data: profile.map(function (row) { return row.follower_count; }),
                        borderColor: '#4f8dfd',
                        backgroundColor: 'rgba(79, 141, 253, 0.15)',
                        tension: 0.25,
                    },
                    {
                        label: 'Profile Visits',
                        data: profile.map(function (row) { return row.profile_visits; }),
                        borderColor: '#34c77b',
                        backgroundColor: 'rgba(52, 199, 123, 0.15)',
                        tension: 0.25,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#e6e8eb' } } },
                scales: {
                    x: { ticks: { color: '#9aa1ad' }, grid: { color: '#2a2f3a' } },
                    y: { ticks: { color: '#9aa1ad' }, grid: { color: '#2a2f3a' } },
                },
            },
        });
    }

    function renderContentTable(content) {
        var tbody = document.querySelector('#content-table tbody');
        tbody.innerHTML = '';

        if (!content.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-muted">Tidak ada data pada rentang ini.</td></tr>';
            return;
        }

        content.forEach(function (row) {
            var tr = document.createElement('tr');
            var caption = (row.caption || '').substring(0, 60);
            tr.innerHTML =
                '<td><a href="' + escapeAttr(row.permalink || '#') + '" target="_blank" rel="noopener">' + escapeHtml(caption || '(tanpa caption)') + '</a></td>' +
                '<td>' + escapeHtml(row.media_type) + '</td>' +
                '<td>' + escapeHtml(row.posted_at) + '</td>' +
                '<td>' + row.reach + '</td>' +
                '<td>' + row.impressions + '</td>' +
                '<td>' + row.likes + '</td>' +
                '<td>' + row.comments + '</td>' +
                '<td>' + row.shares + '</td>' +
                '<td>' + row.saves + '</td>' +
                '<td>' + (row.plays === null ? '-' : row.plays) + '</td>';
            tbody.appendChild(tr);
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function escapeAttr(str) {
        return escapeHtml(str).replace(/"/g, '&quot;');
    }
})();
