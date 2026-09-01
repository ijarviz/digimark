(function () {
    var url = window.__IG_CHART_DATA_URL__;
    if (!url) {
        return;
    }

    var TD = 'p-table-cell-padding h-[48px] border-b border-outline-variant';
    var TD_RIGHT = TD + ' text-right font-data-mono text-data-mono';

    fetch(url)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            renderStats(data.profile, data.trends || {});
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

    function renderTrend(elId, change) {
        var el = document.getElementById(elId);
        if (!el) return;

        if (change === null || change === undefined) {
            el.innerHTML = '';
            return;
        }

        var up = change >= 0;
        var color = up ? 'text-[#36B37E]' : 'text-[#FF5630]';
        var icon = up ? 'arrow_upward' : 'arrow_downward';
        el.className = 'flex items-center text-body-sm font-body-sm font-medium leading-none ' + color;
        el.innerHTML = '<span class="material-symbols-outlined text-[16px]">' + icon + '</span>' + Math.abs(change) + '%';
    }

    function renderAdsStats(ads) {
        var reach = ads.reduce(function (sum, row) { return sum + Number(row.ad_reach || 0); }, 0);
        var impressions = ads.reduce(function (sum, row) { return sum + Number(row.ad_impressions || 0); }, 0);
        var spend = ads.reduce(function (sum, row) { return sum + Number(row.spend || 0); }, 0);
        // Single ad account is assumed, so currency should be consistent
        // across rows — take the first non-empty one found for display.
        var currency = (ads.find(function (row) { return row.currency; }) || {}).currency || '';

        var elReach = document.getElementById('stat-ad-reach');
        var elImpressions = document.getElementById('stat-ad-impressions');
        var elSpend = document.getElementById('stat-ad-spend');

        if (elReach) elReach.textContent = reach;
        if (elImpressions) elImpressions.textContent = impressions;
        if (elSpend) elSpend.textContent = (currency ? currency + ' ' : '') + spend.toFixed(2);
    }

    function renderAdsTable(ads) {
        var tbody = document.querySelector('#ads-table tbody');
        if (!tbody) {
            return;
        }
        tbody.innerHTML = '';

        if (!ads.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="' + TD + ' text-on-surface-variant">Tidak ada data campaign pada rentang ini.</td></tr>';
            return;
        }

        ads.forEach(function (row) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-surface-container-low transition-colors';
            var linkedContent = row.permalink
                ? '<a href="' + escapeAttr(row.permalink) + '" target="_blank" rel="noopener" class="text-primary hover:underline">' + escapeHtml((row.caption || '').substring(0, 40) || 'Lihat konten') + '</a>'
                : '<span class="text-on-surface-variant">-</span>';

            tr.innerHTML =
                '<td class="' + TD + ' font-medium text-on-surface">' + escapeHtml(row.campaign_name || '(tanpa nama)') + '</td>' +
                '<td class="' + TD + ' font-data-mono text-data-mono text-on-surface-variant">' + escapeHtml(row.campaign_id) + '</td>' +
                '<td class="' + TD_RIGHT + '">' + row.ad_reach + '</td>' +
                '<td class="' + TD_RIGHT + '">' + row.ad_impressions + '</td>' +
                '<td class="' + TD_RIGHT + '">' + (row.currency ? escapeHtml(row.currency) + ' ' : '') + Number(row.spend).toFixed(2) + '</td>' +
                '<td class="' + TD + '">' + linkedContent + '</td>';
            tbody.appendChild(tr);
        });
    }

    function renderStats(profile, trends) {
        var latest = profile.length ? profile[profile.length - 1] : null;
        var totalVisits = profile.reduce(function (sum, row) { return sum + Number(row.profile_visits || 0); }, 0);
        var totalReach = profile.reduce(function (sum, row) { return sum + Number(row.reach || 0); }, 0);

        document.getElementById('stat-followers').textContent = latest ? latest.follower_count : '-';
        document.getElementById('stat-visits').textContent = totalVisits;
        document.getElementById('stat-reach').textContent = totalReach;

        renderTrend('stat-followers-trend', trends.follower ? trends.follower.change : null);
        renderTrend('stat-reach-trend', trends.reach ? trends.reach.change : null);
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
                        borderColor: '#003d9b',
                        backgroundColor: 'rgba(0, 61, 155, 0.08)',
                        tension: 0.3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#003d9b',
                        pointBorderWidth: 2,
                        fill: true,
                    },
                    {
                        label: 'Profile Visits',
                        data: profile.map(function (row) { return row.profile_visits; }),
                        borderColor: '#36B37E',
                        backgroundColor: 'rgba(54, 179, 126, 0.08)',
                        tension: 0.3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#36B37E',
                        pointBorderWidth: 2,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#434654', font: { family: 'Inter', size: 12 } } } },
                scales: {
                    x: { ticks: { color: '#737685', font: { family: 'Inter', size: 10 } }, grid: { color: '#e1e2e4' } },
                    y: { ticks: { color: '#737685', font: { family: 'Inter', size: 10 } }, grid: { color: '#e1e2e4' } },
                },
            },
        });
    }

    function mediaTypeIcon(type) {
        var icons = { image: 'image', video: 'movie', carousel: 'view_carousel', reels: 'movie' };
        return icons[type] || 'image';
    }

    function renderContentTable(content) {
        var tbody = document.querySelector('#content-table tbody');
        tbody.innerHTML = '';

        if (!content.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="' + TD + ' text-on-surface-variant">Tidak ada data pada rentang ini.</td></tr>';
            return;
        }

        content.forEach(function (row) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-surface-container-low transition-colors';
            var caption = (row.caption || '').substring(0, 60);
            tr.innerHTML =
                '<td class="' + TD + ' max-w-[220px]"><a href="' + escapeAttr(row.permalink || '#') + '" target="_blank" rel="noopener" class="truncate block text-on-surface font-medium hover:text-primary" title="' + escapeAttr(row.caption || '') + '">' + escapeHtml(caption || '(tanpa caption)') + '</a></td>' +
                '<td class="' + TD + '"><span class="inline-flex items-center gap-1 text-on-surface-variant"><span class="material-symbols-outlined text-[14px]">' + mediaTypeIcon(row.media_type) + '</span>' + escapeHtml(row.media_type) + '</span></td>' +
                '<td class="' + TD + ' text-on-surface-variant">' + escapeHtml(row.posted_at) + '</td>' +
                '<td class="' + TD_RIGHT + '">' + row.reach + '</td>' +
                '<td class="' + TD_RIGHT + '">' + row.impressions + '</td>' +
                '<td class="' + TD_RIGHT + ' text-primary">' + row.likes + '</td>' +
                '<td class="' + TD_RIGHT + '">' + row.comments + '</td>' +
                '<td class="' + TD_RIGHT + '">' + row.shares + '</td>' +
                '<td class="' + TD_RIGHT + '">' + row.saves + '</td>' +
                '<td class="' + TD_RIGHT + '">' + (row.plays === null ? '-' : row.plays) + '</td>';
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
