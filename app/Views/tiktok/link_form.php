<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-card-padding max-w-lg">
    <form method="post" action="<?= base_url('tiktok/links') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label for="url" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">URL TikTok</label>
            <input type="url" id="url" name="url" value="<?= esc(old('url')) ?>" required
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="creator_handle" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Creator Handle</label>
            <input type="text" id="creator_handle" name="creator_handle" value="<?= esc(old('creator_handle')) ?>" placeholder="@username"
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="affiliate_note" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Catatan Afiliasi / Campaign</label>
            <input type="text" id="affiliate_note" name="affiliate_note" value="<?= esc(old('affiliate_note')) ?>"
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="segment" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Segment</label>
                <select id="segment" name="segment" class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">- Pilih -</option>
                    <?php foreach ($segments as $segment): ?>
                    <option value="<?= esc($segment) ?>" <?= old('segment') === $segment ? 'selected' : '' ?>><?= esc(\App\Models\TiktokLinkModel::SEGMENT_LABELS[$segment] ?? $segment) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="gender" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Gender</label>
                <select id="gender" name="gender" class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">- Pilih -</option>
                    <?php foreach ($genders as $gender): ?>
                    <option value="<?= esc($gender) ?>" <?= old('gender') === $gender ? 'selected' : '' ?>><?= esc(ucfirst($gender)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label for="budget" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Budget + Voucher (IDR)</label>
            <input type="number" id="budget" name="budget" step="0.01" min="0" value="<?= esc(old('budget')) ?>"
                   class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <div>
            <label for="data_source" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Sumber Data</label>
            <select id="data_source" name="data_source" class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                <option value="scrape">Scrape (default)</option>
                <option value="oauth">OAuth Kreator</option>
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="h-[36px] px-4 rounded bg-primary text-on-primary text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors">Simpan</button>
            <a href="<?= base_url('tiktok/links') ?>" class="h-[36px] px-4 rounded border border-outline-variant text-body-sm font-body-sm text-on-surface-variant flex items-center hover:bg-surface-container transition-colors">Batal</a>
        </div>
    </form>
</div>
