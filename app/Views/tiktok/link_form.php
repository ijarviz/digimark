<div class="card" style="max-width: 480px">
    <form method="post" action="<?= base_url('tiktok/links') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="url">URL TikTok</label>
            <input type="url" id="url" name="url" value="<?= esc(old('url')) ?>" required>
        </div>

        <div class="form-group">
            <label for="creator_handle">Creator Handle</label>
            <input type="text" id="creator_handle" name="creator_handle" value="<?= esc(old('creator_handle')) ?>" placeholder="@username">
        </div>

        <div class="form-group">
            <label for="affiliate_note">Catatan Afiliasi / Campaign</label>
            <input type="text" id="affiliate_note" name="affiliate_note" value="<?= esc(old('affiliate_note')) ?>">
        </div>

        <div class="form-group">
            <label for="segment">Segment</label>
            <select id="segment" name="segment">
                <option value="">- Pilih Segment -</option>
                <?php foreach ($segments as $segment): ?>
                <option value="<?= esc($segment) ?>" <?= old('segment') === $segment ? 'selected' : '' ?>><?= esc(ucfirst($segment)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="">- Pilih Gender -</option>
                <?php foreach ($genders as $gender): ?>
                <option value="<?= esc($gender) ?>" <?= old('gender') === $gender ? 'selected' : '' ?>><?= esc(ucfirst($gender)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="budget">Budget (IDR)</label>
            <input type="number" id="budget" name="budget" step="0.01" min="0" value="<?= esc(old('budget')) ?>">
        </div>

        <div class="form-group">
            <label for="data_source">Sumber Data</label>
            <select id="data_source" name="data_source">
                <option value="scrape">Scrape (default)</option>
                <option value="oauth">OAuth Kreator</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('tiktok/links') ?>" class="btn">Batal</a>
    </form>
</div>
