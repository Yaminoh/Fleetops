<?php $isAdmin = ($dashboard['user']['role'] ?? '') === 'Admin'; ?>
<section class="panel">
    <div class="panel-header">
        <div>
            <p class="eyebrow">System Users</p>
            <h3>User Management</h3>
        </div>
        <?php if ($isAdmin): ?>
            <button class="pill-button" type="button" onclick="openAddUserModal()">Add User</button>
        <?php endif; ?>
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

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dashboard['users']) > 0): ?>
                    <?php foreach ($dashboard['users'] as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <span class="status-pill <?= $user['status'] === 'active' ? 'status-approved' : 'status-pending' ?>">
                                    <?= ucfirst(htmlspecialchars($user['status'])) ?>
                                </span>
                            </td>
                            <?php if ($isAdmin): ?>
                                <td>
                                    <button class="icon-button" title="Edit" type="button"
                                        onclick="openEditUserModal(<?= (int) $user['id'] ?>, <?= htmlspecialchars(json_encode($user['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($user['email']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($user['role']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($user['status']), ENT_QUOTES) ?>)">✏️</button>
                                    <?php if ($user['id'] !== $dashboard['user']['id']): ?>
                                        <form method="POST" action="<?= route('usermanagement.destroy', $user['id']) ?>" style="display:inline;" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($user['name'])) ?>? This cannot be undone.');">
                                            <?= csrf_field() ?>
                                            <?= method_field('DELETE') ?>
                                            <button class="icon-button" title="Delete" type="submit">🗑️</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $isAdmin ? 5 : 4 ?>" style="text-align: center; padding: 20px;">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($isAdmin): ?>
<!-- Modal: Add User -->
<div id="add-user-modal" class="modal-backdrop" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Add User</h3>
            <button class="close-btn" type="button" onclick="closeUserModal('add-user-modal')">✕</button>
        </div>
        <form method="POST" action="<?= route('usermanagement.store') ?>">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="add-name">Full name</label>
                <input class="form-control" type="text" id="add-name" name="name" required maxlength="100">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="add-email">Email</label>
                <input class="form-control" type="email" id="add-email" name="email" required maxlength="100">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="add-password">Password</label>
                <input class="form-control" type="password" id="add-password" name="password" required minlength="8">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="add-password-confirm">Confirm password</label>
                <input class="form-control" type="password" id="add-password-confirm" name="password_confirmation" required minlength="8">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="add-role">Role</label>
                <select class="form-control" id="add-role" name="role" required>
                    <?php foreach ($dashboard['userRoles'] as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label for="add-status">Status</label>
                <select class="form-control" id="add-status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Create User</button>
        </form>
    </div>
</div>

<!-- Modal: Edit User -->
<div id="edit-user-modal" class="modal-backdrop" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="close-btn" type="button" onclick="closeUserModal('edit-user-modal')">✕</button>
        </div>
        <form method="POST" id="edit-user-form" action="">
            <?= csrf_field() ?>
            <?= method_field('PUT') ?>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit-name">Full name</label>
                <input class="form-control" type="text" id="edit-name" name="name" required maxlength="100">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit-email">Email</label>
                <input class="form-control" type="email" id="edit-email" name="email" required maxlength="100">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit-password">New password <span style="font-weight:400;color:var(--muted);">(leave blank to keep current)</span></label>
                <input class="form-control" type="password" id="edit-password" name="password" minlength="8">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit-password-confirm">Confirm new password</label>
                <input class="form-control" type="password" id="edit-password-confirm" name="password_confirmation" minlength="8">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="edit-role">Role</label>
                <select class="form-control" id="edit-role" name="role" required>
                    <?php foreach ($dashboard['userRoles'] as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label for="edit-status">Status</label>
                <select class="form-control" id="edit-status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Save Changes</button>
        </form>
    </div>
</div>

<script>
    function openAddUserModal() {
        document.getElementById('add-user-modal').style.display = 'flex';
    }

    function closeUserModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openEditUserModal(id, name, email, role, status) {
        const form = document.getElementById('edit-user-form');
        form.action = <?= json_encode(url('/usermanagement')) ?> + '/' + id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-role').value = role;
        document.getElementById('edit-status').value = status;
        document.getElementById('edit-password').value = '';
        document.getElementById('edit-password-confirm').value = '';
        document.getElementById('edit-user-modal').style.display = 'flex';
    }
</script>
<?php endif; ?>
