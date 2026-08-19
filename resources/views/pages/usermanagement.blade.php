<section class="panel">
    <div class="panel-header">
        <div>
            <p class="eyebrow">System Users</p>
            <h3>User Management</h3>
        </div>
        <button class="pill-button">Add User</button>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
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
                            <td>
                                <button class="icon-button" title="Edit">✏️</button>
                                <button class="icon-button" title="Delete">🗑️</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
