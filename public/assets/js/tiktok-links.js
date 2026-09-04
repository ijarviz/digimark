(function () {
    var baseUrl = window.__TIKTOK_LINKS_BASE_URL__;
    if (!baseUrl) {
        return;
    }

    // The CSRF cookie is HttpOnly (by design — document.cookie can't read
    // it), so the token is tracked here as plain JS state instead: seeded
    // from the page's initial render, then replaced after every request
    // with the fresh value the server sends back in the JSON response
    // (the CSRF filter regenerates the hash on every valid submission).
    var csrfHeader = window.__CSRF_HEADER__;
    var csrfToken = window.__CSRF_TOKEN__;

    var allBtn = document.getElementById('refresh-all-btn');
    var allIcon = document.getElementById('refresh-all-icon');
    var allLabel = document.getElementById('refresh-all-label');
    var status = document.getElementById('refresh-all-status');
    var rowButtons = document.querySelectorAll('.refresh-one-btn');

    if (!allBtn && !rowButtons.length) {
        return;
    }

    // There is one scraper with a single serialized queue behind it, so the
    // "Refresh All" walk and a per-row refresh must never run at the same
    // time. This guard disables every refresh control while any refresh is
    // in flight.
    var busy = false;

    function setBusy(state) {
        busy = state;
        if (allBtn) {
            allBtn.disabled = state;
        }
        Array.prototype.forEach.call(rowButtons, function (b) {
            b.disabled = state;
        });
    }

    if (allBtn) {
        allBtn.addEventListener('click', runRefreshAll);
    }

    Array.prototype.forEach.call(rowButtons, function (b) {
        b.addEventListener('click', function () {
            if (busy) {
                return;
            }

            var id = b.getAttribute('data-link-id');
            if (!id) {
                return;
            }

            setBusy(true);
            var rowIcon = b.querySelector('.material-symbols-outlined');
            if (rowIcon) {
                rowIcon.classList.add('animate-spin');
            }
            if (status) {
                status.textContent = 'Refresh link #' + id + '...';
            }

            refreshOne(id).then(function (ok) {
                if (rowIcon) {
                    rowIcon.classList.remove('animate-spin');
                }
                if (status) {
                    status.textContent = ok
                        ? 'Link #' + id + ' berhasil diperbarui.'
                        : 'Link #' + id + ' gagal di-refresh.';
                }
                setBusy(false);
            });
        });
    });

    function runRefreshAll() {
        var rows = document.querySelectorAll('tr[data-link-id]');
        var ids = Array.prototype.map.call(rows, function (row) {
            return row.getAttribute('data-link-id');
        });

        if (!ids.length) {
            return;
        }

        setBusy(true);
        allIcon.classList.add('animate-spin');
        allLabel.textContent = 'Refreshing...';
        status.textContent = '';

        var succeeded = 0;
        var failed = 0;
        var index = 0;

        function next() {
            if (index >= ids.length) {
                finish();
                return;
            }

            var id = ids[index++];

            refreshOne(id).then(function (ok) {
                if (ok) { succeeded++; } else { failed++; }
                status.textContent = index + '/' + ids.length + ' selesai (' + failed + ' gagal)...';
                if (index >= ids.length) {
                    finish();
                    return;
                }
                // Breathe between links. The scraper serializes and paces
                // requests server-side too, but keeping the client from
                // firing back-to-back smooths the UI and the load pattern.
                setTimeout(next, 800 + Math.floor(Math.random() * 700));
            });
        }

        function finish() {
            setBusy(false);
            allIcon.classList.remove('animate-spin');
            allLabel.textContent = 'Refresh All';
            status.textContent = failed === 0
                ? succeeded + ' link berhasil diperbarui.'
                : succeeded + ' berhasil, ' + failed + ' gagal.';
        }

        next();
    }

    function refreshOne(id) {
        showSpinner(id, true);

        var headers = { 'X-Requested-With': 'XMLHttpRequest' };
        headers[csrfHeader] = csrfToken;

        return fetch(baseUrl + '/' + id + '/refresh-one', {
            method: 'POST',
            headers: headers,
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                showSpinner(id, false);
                if (data.csrf_token) {
                    csrfToken = data.csrf_token;
                }
                if (data.success) {
                    applyUpdate(id, data);
                    return true;
                }
                return false;
            })
            .catch(function () {
                showSpinner(id, false);
                return false;
            });
    }

    function showSpinner(id, visible) {
        var el = document.getElementById('spinner-' + id);
        if (el) {
            el.style.display = visible ? 'inline-flex' : 'none';
        }
    }

    function setText(elementId, text) {
        var el = document.getElementById(elementId);
        if (el && text !== null && text !== undefined) {
            el.textContent = text;
        }
    }

    function setTitle(elementId, text) {
        var el = document.getElementById(elementId);
        if (el && text !== null && text !== undefined) {
            el.title = text;
        }
    }

    function applyUpdate(id, data) {
        setText('views-' + id, data.views.compact);
        setTitle('views-' + id, data.views.exact);

        setText('likes-' + id, data.likes.compact);
        setTitle('likes-wrap-' + id, 'Likes: ' + data.likes.exact);

        setText('comments-' + id, data.comments.compact);
        setTitle('comments-wrap-' + id, 'Comments: ' + data.comments.exact);

        setText('shares-' + id, data.shares.compact);
        setTitle('shares-wrap-' + id, 'Shares: ' + data.shares.exact);

        setText('saves-' + id, data.saves.compact);
        setTitle('saves-wrap-' + id, 'Saves: ' + data.saves.exact);

        setText('snapshot-' + id, data.snapshot_date || '-');
        setText('synced-' + id, data.last_synced_at || '-');

        if (data.video_posted_at) {
            setText('posted-' + id, data.video_posted_at);
        }
    }
})();
