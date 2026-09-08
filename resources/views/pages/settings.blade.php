<?php $prefs = $dashboard['user']['preferences'] ?? ['theme' => 'light', 'date_format' => 'M d, Y', 'locale' => 'en']; ?>
<section class="panel">
    <div class="panel-header">
        <div>
            <p class="eyebrow">Settings</p>
            <h3>Account & System</h3>
        </div>
    </div>

    <?php if (session('status')): ?>
        <div class="alert-banner status-approved" style="padding:10px 14px;border-radius:12px;margin-bottom:14px;font-weight:600;">
            <?= htmlspecialchars(session('status')) ?>
        </div>
    <?php endif; ?>
    <?php if ($errors->any()): ?>
        <div class="alert-banner" style="padding:10px 14px;border-radius:12px;margin-bottom:14px;font-weight:600;background:rgba(231,76,60,0.12);color:#e74c3c;">
            <?= htmlspecialchars($errors->first()) ?>
        </div>
    <?php endif; ?>

    <div class="list-stack">
        <div class="list-item" style="align-items:flex-start;">
            <div class="list-icon">⚙️</div>
            <div style="flex:1;">
                <h4>Profile Settings</h4>
                <p>Edit your account name and email.</p>
                <form method="POST" action="<?= route('settings.profile') ?>" style="margin-top:12px;max-width:420px;">
                    <?= csrf_field() ?>
                    <?= method_field('PUT') ?>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="profile-name">Full name</label>
                        <input class="form-control" type="text" id="profile-name" name="name" required maxlength="100" value="<?= htmlspecialchars($dashboard['user']['name']) ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="profile-email">Email</label>
                        <input class="form-control" type="email" id="profile-email" name="email" required maxlength="100" value="<?= htmlspecialchars($dashboard['user']['email']) ?>">
                    </div>
                    <button type="submit" class="btn-primary">Save Profile</button>
                </form>
            </div>
        </div>

        <div class="list-item" style="align-items:flex-start;">
            <div class="list-icon">🔒</div>
            <div style="flex:1;">
                <h4>Security</h4>
                <p>Change your password. Two-factor email codes are required on every sign-in.</p>
                <form method="POST" action="<?= route('settings.password') ?>" style="margin-top:12px;max-width:420px;">
                    <?= csrf_field() ?>
                    <?= method_field('PUT') ?>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="current-password">Current password</label>
                        <input class="form-control" type="password" id="current-password" name="current_password" required>
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="new-password">New password</label>
                        <input class="form-control" type="password" id="new-password" name="password" required minlength="8">
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="new-password-confirm">Confirm new password</label>
                        <input class="form-control" type="password" id="new-password-confirm" name="password_confirmation" required minlength="8">
                    </div>
                    <button type="submit" class="btn-primary">Change Password</button>
                </form>
            </div>
        </div>

        <div class="list-item" style="align-items:flex-start;">
            <div class="list-icon">🌐</div>
            <div style="flex:1;">
                <h4>System Preferences</h4>
                <p>Configure theme, date, and locale settings.</p>
                <form method="POST" action="<?= route('settings.preferences') ?>" style="margin-top:12px;max-width:420px;">
                    <?= csrf_field() ?>
                    <?= method_field('PUT') ?>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="pref-theme">Theme</label>
                        <select class="form-control" id="pref-theme" name="theme" required>
                            <option value="light" <?= $prefs['theme'] === 'light' ? 'selected' : '' ?>>Light</option>
                            <option value="dark" <?= $prefs['theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="pref-date-format">Date format</label>
                        <select class="form-control" id="pref-date-format" name="date_format" required>
                            <option value="M d, Y" <?= $prefs['date_format'] === 'M d, Y' ? 'selected' : '' ?>>Sep 08, 2026</option>
                            <option value="d/m/Y" <?= $prefs['date_format'] === 'd/m/Y' ? 'selected' : '' ?>>08/09/2026</option>
                            <option value="Y-m-d" <?= $prefs['date_format'] === 'Y-m-d' ? 'selected' : '' ?>>2026-09-08</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:16px;">
                        <label for="pref-locale">Locale</label>
                        <select class="form-control" id="pref-locale" name="locale" required>
                            <option value="en" <?= $prefs['locale'] === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="fil" <?= $prefs['locale'] === 'fil' ? 'selected' : '' ?>>Filipino</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>
</section>
