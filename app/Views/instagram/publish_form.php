<div class="card" style="max-width: 560px">
    <form method="post" action="<?= base_url('publish') ?>" id="publish-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="media_type">Tipe Konten</label>
            <select id="media_type" name="media_type">
                <option value="image">Single Image</option>
                <option value="carousel">Carousel</option>
                <option value="reels">Reels (Video)</option>
                <option value="story">Story</option>
            </select>
        </div>

        <div class="form-group">
            <label>URL Media (harus publik/sudah di-hosting)</label>
            <div id="media-urls-container">
                <input type="url" name="media_urls[]" placeholder="https://..." required style="margin-bottom: var(--space-2)">
            </div>
            <button type="button" id="add-url-btn" class="btn" hidden>+ Tambah URL (carousel)</button>
        </div>

        <div class="form-group" id="caption-group">
            <label for="caption">Caption</label>
            <textarea id="caption" name="caption" rows="4"></textarea>
        </div>
        <p class="text-muted" id="story-note" hidden>Caption tidak didukung untuk Story oleh Instagram Content Publishing API.</p>

        <div class="form-group">
            <label><input type="checkbox" id="publish_now" name="publish_now" value="1" checked> Publish sekarang</label>
        </div>

        <div class="form-group" id="scheduled-group" hidden>
            <label for="scheduled_at">Jadwal</label>
            <input type="datetime-local" id="scheduled_at" name="scheduled_at">
        </div>

        <button type="submit" class="btn btn-primary">Simpan ke Antrian</button>
        <a href="<?= base_url('publish') ?>" class="btn">Batal</a>
    </form>
</div>

<script>
(function () {
    var mediaType = document.getElementById('media_type');
    var urlsContainer = document.getElementById('media-urls-container');
    var addUrlBtn = document.getElementById('add-url-btn');
    var captionGroup = document.getElementById('caption-group');
    var storyNote = document.getElementById('story-note');
    var publishNow = document.getElementById('publish_now');
    var scheduledGroup = document.getElementById('scheduled-group');

    function addUrlInput() {
        var input = document.createElement('input');
        input.type = 'url';
        input.name = 'media_urls[]';
        input.placeholder = 'https://...';
        input.style.marginBottom = 'var(--space-2)';
        urlsContainer.appendChild(input);
    }

    function resetUrlInputs() {
        urlsContainer.innerHTML = '';
        var input = document.createElement('input');
        input.type = 'url';
        input.name = 'media_urls[]';
        input.placeholder = 'https://...';
        input.required = true;
        input.style.marginBottom = 'var(--space-2)';
        urlsContainer.appendChild(input);
    }

    mediaType.addEventListener('change', function () {
        var isCarousel = this.value === 'carousel';
        var isStory = this.value === 'story';

        addUrlBtn.hidden = !isCarousel;
        resetUrlInputs();

        captionGroup.hidden = isStory;
        storyNote.hidden = !isStory;
    });

    addUrlBtn.addEventListener('click', addUrlInput);

    publishNow.addEventListener('change', function () {
        scheduledGroup.hidden = this.checked;
        document.getElementById('scheduled_at').required = !this.checked;
    });
})();
</script>
