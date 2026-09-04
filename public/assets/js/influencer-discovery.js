(function () {
    var saveUrl = window.__DISCOVERY_SAVE_URL__;
    if (!saveUrl) {
        return;
    }

    var csrfHeader = window.__CSRF_HEADER__;
    var csrfToken = window.__CSRF_TOKEN__;

    Array.prototype.forEach.call(document.querySelectorAll('.disc-save-btn'), function (btn) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            var original = btn.innerHTML;
            btn.innerHTML = '<span class="material-symbols-outlined text-[14px] animate-spin">progress_activity</span> Menyimpan...';

            var headers = { 'X-Requested-With': 'XMLHttpRequest' };
            headers[csrfHeader] = csrfToken;

            var body = new URLSearchParams({
                platform: btn.getAttribute('data-platform') || '',
                handle: btn.getAttribute('data-handle') || '',
                profile_url: btn.getAttribute('data-profile-url') || '',
                followers: btn.getAttribute('data-followers') || '',
            });

            fetch(saveUrl, { method: 'POST', headers: headers, body: body })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.csrf_token) {
                        csrfToken = data.csrf_token;
                    }
                    if (data.success) {
                        btn.outerHTML = '<span class="text-[11px] text-tertiary flex items-center gap-1">'
                            + '<span class="material-symbols-outlined text-[14px]">check_circle</span>Tersimpan</span>';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = original;
                        alert(data.message || 'Gagal menyimpan.');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    alert('Gagal menyimpan — periksa koneksi.');
                });
        });
    });

    // Client-side refinement over the already-fetched result set — no
    // extra Apify calls, just hide/show rows already on the page.
    var minInput = document.getElementById('disc-min-followers');
    var platformSelect = document.getElementById('disc-platform-filter');
    var countLabel = document.getElementById('disc-count');
    var rows = document.querySelectorAll('#disc-table tbody tr');

    function applyFilter() {
        var min = parseInt(minInput && minInput.value, 10) || 0;
        var platform = platformSelect ? platformSelect.value : '';
        var visible = 0;

        Array.prototype.forEach.call(rows, function (row) {
            var followers = parseInt(row.getAttribute('data-followers'), 10) || 0;
            var rowPlatform = row.getAttribute('data-platform');
            var show = followers >= min && (!platform || rowPlatform === platform);
            row.style.display = show ? '' : 'none';
            if (show) {
                visible++;
            }
        });

        if (countLabel) {
            countLabel.textContent = visible + ' dari ' + rows.length + ' akun ditampilkan';
        }
    }

    if (minInput) {
        minInput.addEventListener('input', applyFilter);
    }
    if (platformSelect) {
        platformSelect.addEventListener('change', applyFilter);
    }
    applyFilter();
})();
