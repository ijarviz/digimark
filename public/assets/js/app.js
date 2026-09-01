document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.flash').forEach(function (el) {
        setTimeout(function () {
            el.style.display = 'none';
        }, 6000);
    });
});
