<form method="post" action="<?= base_url('login') ?>">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" autofocus required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%">Login</button>
</form>
