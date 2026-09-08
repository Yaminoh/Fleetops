<section class="panel" style="background: transparent; border: none; padding: 0;">
    <div class="panel-header" style="margin-bottom: 1.5rem;">
        <div>
            <p class="eyebrow">System Alerts</p>
            <h3>Notifications</h3>
        </div>
        <button class="pill-button" id="markAllReadBtn" type="button">Mark All Read</button>
    </div>

    <?php if (session('status')): ?>
        <div class="alert-banner status-approved" style="padding:10px 14px;border-radius:12px;margin-bottom:14px;font-weight:600;">
            <?= htmlspecialchars(session('status')) ?>
        </div>
    <?php endif; ?>

    <div id="notif-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
        <?php
        $typeColors = [
            'warning' => '#f59e0b',
            'danger'  => '#ef4444',
            'info'    => '#3b82f6',
            'success' => '#10b981',
        ];
        ?>
        <?php if (empty($dashboard['notifications'])): ?>
            <div class="panel" style="text-align:center;color:var(--muted);padding:2rem;">No notifications yet.</div>
        <?php endif; ?>
        <?php foreach ($dashboard['notifications'] as $n): ?>
            <?php
            $color = $typeColors[$n['severity']] ?? '#6b7280';
            $bg = $n['read'] ? '#f9fafb' : '#ffffff';
            $border = $n['read'] ? '#e5e7eb' : $color;
            ?>
            <div class="notif-item" data-id="<?= (int) $n['id'] ?>" data-read="<?= $n['read'] ? '1' : '0' ?>" style="
                background: <?= $bg ?>;
                border: 1px solid <?= $border ?>;
                border-left: 4px solid <?= $color ?>;
                border-radius: 10px;
                padding: 1rem 1.25rem;
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                cursor: pointer;
                transition: box-shadow 0.2s;
            " onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                <span style="font-size: 1.5rem; line-height:1;"><?= htmlspecialchars($n['icon']) ?></span>
                <div style="flex: 1; min-width: 0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.25rem;">
                        <strong style="font-size: 0.9rem; color: #111827;"><?= htmlspecialchars($n['title']) ?></strong>
                        <span style="font-size: 0.75rem; color: #9ca3af; white-space: nowrap;"><?= htmlspecialchars($n['time']) ?></span>
                    </div>
                    <p style="font-size: 0.85rem; color: #4b5563; margin: 0;"><?= htmlspecialchars($n['detail']) ?></p>
                </div>
                <?php if (! $n['read']): ?>
                    <span class="unread-dot" style="width: 8px; height: 8px; background: <?= $color ?>; border-radius: 50%; flex-shrink: 0; margin-top: 4px;"></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        ?? '<?= csrf_token() ?>';

    function markItemRead(item) {
        if (item.dataset.read === '1') return;
        const id = item.dataset.id;

        fetch(`/notifications/${id}/read`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        }).then(function (res) {
            if (!res.ok) return;
            item.dataset.read = '1';
            item.style.background = '#f9fafb';
            item.style.borderColor = '#e5e7eb';
            const dot = item.querySelector('.unread-dot');
            if (dot) dot.remove();
        });
    }

    document.querySelectorAll('.notif-item').forEach(function (item) {
        item.addEventListener('click', function () { markItemRead(item); });
    });

    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
            fetch('/notifications/read-all', {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            }).then(function (res) {
                if (!res.ok) return;
                document.querySelectorAll('.notif-item').forEach(function (item) {
                    item.dataset.read = '1';
                    item.style.background = '#f9fafb';
                    item.style.borderColor = '#e5e7eb';
                    const dot = item.querySelector('.unread-dot');
                    if (dot) dot.remove();
                });
                const bellBadge = document.getElementById('bellBadge');
                if (bellBadge) bellBadge.remove();
            });
        });
    }
})();
</script>
