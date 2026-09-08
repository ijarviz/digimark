<?php
/** @var string $mode  'create' | 'edit' */
/** @var array<string,mixed>|null $item */
$isEdit = ($mode ?? 'create') === 'edit';
$action = $isEdit ? base_url('publish/' . $item['id'] . '/update') : base_url('publish');

$curType    = old('media_type') ?? ($item['media_type'] ?? 'image');
$curCaption = old('caption')    ?? ($item['caption'] ?? '');

$oldUrls = old('media_urls');
if (is_array($oldUrls)) {
    $curUrls = array_values(array_filter($oldUrls, static fn ($u) => trim((string) $u) !== ''));
} else {
    $curUrls = $item['media_urls'] ?? [];
}
if ($curUrls === []) {
    $curUrls = [''];
}

// In edit mode a scheduled item defaults to "keep the schedule"; in create
// mode "publish now" is the default.
$publishNowChecked = $isEdit ? (old('publish_now') === '1') : (old('publish_now') === null || old('publish_now') === '1');
$curScheduled      = old('scheduled_at') ?? (! empty($item['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($item['scheduled_at'])) : '');
?>

<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding max-w-xl">
    <form method="post" action="<?= $action ?>" id="publish-form" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label for="media_type" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Tipe Konten</label>
            <select id="media_type" name="media_type"
                    class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                <?php foreach (['image' => 'Single Image', 'carousel' => 'Carousel', 'reels' => 'Reels (Video)', 'story' => 'Story'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $curType === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-label-caps font-label-caps text-on-surface-variant mb-1">URL Media (harus publik/sudah di-hosting)</label>
            <div id="media-urls-container" class="space-y-2">
                <?php foreach ($curUrls as $i => $u): ?>
                <input type="url" name="media_urls[]" placeholder="https://..." value="<?= esc($u, 'attr') ?>" <?= $i === 0 ? 'required' : '' ?>
                       class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-url-btn" <?= $curType === 'carousel' ? '' : 'hidden' ?>
                    class="mt-2 h-[32px] px-3 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant hover:bg-surface-container transition-colors inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Tambah URL (carousel)
            </button>
        </div>

        <div id="caption-group" <?= $curType === 'story' ? 'hidden' : '' ?>>
            <label for="caption" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Caption</label>
            <textarea id="caption" name="caption" rows="4"
                      class="w-full px-3 py-2 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary"><?= esc($curCaption) ?></textarea>
        </div>
        <p class="text-body-sm font-body-sm text-on-surface-variant" id="story-note" <?= $curType === 'story' ? '' : 'hidden' ?>>Caption tidak didukung untuk Story oleh Instagram Content Publishing API.</p>

        <div>
            <label class="flex items-center gap-2 text-body-sm font-body-sm text-on-surface">
                <input type="checkbox" id="publish_now" name="publish_now" value="1" <?= $publishNowChecked ? 'checked' : '' ?> class="rounded border-outline-variant text-primary focus:ring-primary">
                Publish sekarang
            </label>
        </div>

        <div id="scheduled-group" <?= $publishNowChecked ? 'hidden' : '' ?>>
            <label for="scheduled_at" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Jadwal</label>
            <input type="datetime-local" id="scheduled_at" name="scheduled_at" value="<?= esc($curScheduled, 'attr') ?>" <?= $publishNowChecked ? '' : 'required' ?>
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan ke Antrian' ?></button>
            <a href="<?= base_url('publish') ?>" class="h-[36px] px-4 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant flex items-center hover:bg-surface-container transition-colors">Batal</a>
        </div>
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

    var INPUT_CLASS = 'w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary';

    function makeUrlInput(required) {
        var input = document.createElement('input');
        input.type = 'url';
        input.name = 'media_urls[]';
        input.placeholder = 'https://...';
        input.required = !!required;
        input.className = INPUT_CLASS;
        return input;
    }

    function addUrlInput() {
        urlsContainer.appendChild(makeUrlInput(false));
    }

    function resetUrlInputs() {
        urlsContainer.innerHTML = '';
        urlsContainer.appendChild(makeUrlInput(true));
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
