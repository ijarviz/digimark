<form method="post" action="<?= base_url('login') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label for="username" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Username</label>
        <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" autofocus required
               class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
    </div>

    <div>
        <label for="password" class="block text-label-caps font-label-caps text-on-surface-variant mb-1">Password</label>
        <input type="password" id="password" name="password" required
               class="w-full h-[36px] px-3 rounded border border-outline-variant bg-surface text-body-sm font-body-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
    </div>

    <button type="submit" class="w-full h-[36px] bg-primary text-on-primary rounded text-body-sm font-body-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors">
        Login
    </button>
</form>
