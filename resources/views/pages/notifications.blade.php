<section class="panel" style="background: transparent; border: none; padding: 0;">
    <div class="panel-header" style="margin-bottom: 1.5rem;">
        <div>
            <p class="eyebrow">System Alerts</p>
            <h3>Notifications</h3>
        </div>
        <button class="pill-button" onclick="markAllRead()">Mark All Read</button>
    </div>

    <div id="notif-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
        <?php
        $notifications = [
            ['icon' => '🔧', 'title' => 'Maintenance Due', 'detail' => 'TRK-101 is due for scheduled oil change', 'time' => '2 mins ago', 'type' => 'warning', 'read' => false],
            ['icon' => '⛽', 'title' => 'Fuel Alert', 'detail' => 'TRK-204 fuel level below 15%', 'time' => '18 mins ago', 'type' => 'danger', 'read' => false],
            ['icon' => '📋', 'title' => 'New Reservation', 'detail' => 'Reservation #RSV-2091 submitted by Daniella Agus', 'time' => '45 mins ago', 'type' => 'info', 'read' => false],
            ['icon' => '✅', 'title' => 'Trip Completed', 'detail' => 'Harvey Villarin completed TRP-2081 on time', 'time' => '1 hour ago', 'type' => 'success', 'read' => true],
            ['icon' => '📄', 'title' => 'Registration Expiry', 'detail' => 'TRK-305 registration expires in 7 days', 'time' => '3 hours ago', 'type' => 'warning', 'read' => true],
            ['icon' => '🛡️', 'title' => 'Insurance Expiry', 'detail' => 'TRK-412 insurance expires in 14 days', 'time' => '5 hours ago', 'type' => 'warning', 'read' => true],
        ];

        $typeColors = [
            'warning' => '#f59e0b',
            'danger'  => '#ef4444',
            'info'    => '#3b82f6',
            'success' => '#10b981',
        ];

        foreach ($notifications as $n):
            $color = $typeColors[$n['type']] ?? '#6b7280';
            $bg = $n['read'] ? '#f9fafb' : '#ffffff';
            $border = $n['read'] ? '#e5e7eb' : $color;
        ?>
        <div class="notif-item" style="
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
            <span style="font-size: 1.5rem; line-height:1;"><?= $n['icon'] ?></span>
            <div style="flex: 1; min-width: 0;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.25rem;">
                    <strong style="font-size: 0.9rem; color: #111827;"><?= htmlspecialchars($n['title']) ?></strong>
                    <span style="font-size: 0.75rem; color: #9ca3af; white-space: nowrap;"><?= htmlspecialchars($n['time']) ?></span>
                </div>
                <p style="font-size: 0.85rem; color: #4b5563; margin: 0;"><?= htmlspecialchars($n['detail']) ?></p>
            </div>
            <?php if (!$n['read']): ?>
            <span style="width: 8px; height: 8px; background: <?= $color ?>; border-radius: 50%; flex-shrink: 0; margin-top: 4px;"></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
function markAllRead() {
    document.querySelectorAll('.notif-item').forEach(function(item) {
        item.style.background = '#f9fafb';
        item.style.borderColor = '#e5e7eb';
        var dot = item.querySelector('span[style*="border-radius: 50%"]');
        if (dot) dot.remove();
    });
}
</script>
