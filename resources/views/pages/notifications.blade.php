<?php
$notifications = $dashboard['notifications'] ?? [];
$unreadCount = count(array_filter($notifications, fn ($notification) => !($notification['read'] ?? false)));
$severityColors = [
    'warning' => '#d97706',
    'danger' => '#dc2626',
    'info' => '#2563eb',
    'success' => '#059669',
];
?>
<section class="panel notifications-page">
    <div class="panel-header notifications-header">
        <div>
            <p class="eyebrow">System activity</p>
            <h3>Notifications</h3>
            <p class="notifications-intro">Review important changes and updates across your fleet workspace.</p>
        </div>
        <?php if ($dashboard['isAdmin']): ?><button class="pill-button" id="markAllReadBtn" type="button" <?= $unreadCount === 0 ? 'disabled' : '' ?>>Mark all as read</button><?php endif; ?>
    </div>

    <?php if (session('status')): ?>
        <div class="alert-banner status-approved" role="status" style="padding:10px 14px;border-radius:12px;margin-bottom:14px;font-weight:600;">
            <?= htmlspecialchars(session('status'), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div id="notification-feedback" class="notification-feedback" role="status" aria-live="polite" hidden></div>

    <div class="notification-summary" aria-label="Notification counts">
        <div><span>Loaded</span><strong id="notification-total"><?= count($notifications) ?></strong></div>
        <div><span>Unread</span><strong id="notification-unread"><?= $unreadCount ?></strong></div>
        <p>Showing your 30 most recent notifications.</p>
    </div>

    <div class="notification-toolbar">
        <div class="notification-filters" role="group" aria-label="Filter notifications">
            <button type="button" class="notification-filter is-active" data-filter="all" aria-pressed="true">All <span><?= count($notifications) ?></span></button>
            <button type="button" class="notification-filter" data-filter="unread" aria-pressed="false">Unread <span><?= $unreadCount ?></span></button>
            <button type="button" class="notification-filter" data-filter="read" aria-pressed="false">Read</button>
        </div>
        <label class="notification-search" for="notification-search">
            <span aria-hidden="true">⌕</span>
            <input id="notification-search" type="search" placeholder="Search notifications" autocomplete="off">
        </label>
    </div>

    <div id="notification-list" class="notification-list" aria-live="polite">
        <?php foreach ($notifications as $notification): ?>
            <?php
            $isRead = (bool) ($notification['read'] ?? false);
            $severity = $notification['severity'] ?? 'info';
            $color = $severityColors[$severity] ?? '#64748b';
            $searchText = strtolower(($notification['title'] ?? '').' '.($notification['detail'] ?? '').' '.$severity);
            ?>
            <article class="notification-item <?= $isRead ? 'is-read' : 'is-unread' ?>" data-id="<?= (int) $notification['id'] ?>" data-read="<?= $isRead ? '1' : '0' ?>" data-severity="<?= htmlspecialchars($severity, ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8') ?>" style="--notification-accent:<?= $color ?>">
                <span class="notification-icon" aria-hidden="true"><?= htmlspecialchars($notification['icon'] ?? '•', ENT_QUOTES, 'UTF-8') ?></span>
                <div class="notification-content">
                    <div class="notification-title-row">
                        <h4><?= htmlspecialchars($notification['title'] ?? 'Notification', ENT_QUOTES, 'UTF-8') ?></h4>
                        <time><?= htmlspecialchars($notification['time'] ?? '', ENT_QUOTES, 'UTF-8') ?></time>
                    </div>
                    <p><?= htmlspecialchars($notification['detail'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="notification-meta">
                        <span class="severity-tag"><?= htmlspecialchars(ucfirst($severity), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="read-state"><?= $isRead ? 'Read' : 'Unread' ?></span>
                    </div>
                </div>
                <?php if (!$isRead && $dashboard['isAdmin']): ?>
                    <span class="unread-indicator" aria-label="Unread"></span>
                    <button type="button" class="mark-read-button" aria-label="Mark <?= htmlspecialchars($notification['title'] ?? 'notification', ENT_QUOTES, 'UTF-8') ?> as read">Mark read</button>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        <div id="notifications-empty" class="notifications-empty" <?= $notifications ? 'hidden' : '' ?>>
            <span aria-hidden="true">✓</span>
            <strong id="notifications-empty-title"><?= $notifications ? 'No matching notifications' : 'You’re all caught up' ?></strong>
            <p id="notifications-empty-copy"><?= $notifications ? 'Try a different search or filter.' : 'New fleet and account activity will appear here.' ?></p>
        </div>
    </div>
</section>

<style>
    .notifications-intro { margin: 6px 0 0; color: var(--muted); font-size: .9rem; }
    .notifications-header #markAllReadBtn:disabled { opacity: .55; cursor: not-allowed; }
    .notification-feedback { margin-bottom: 14px; padding: 10px 14px; border: 1px solid var(--border); border-radius: 10px; background: var(--surface); color: var(--text); font-size: .85rem; }
    .notification-feedback.is-error { border-color: rgba(220,38,38,.35); color: #dc2626; }
    .notification-summary { display: flex; align-items: center; gap: 22px; margin: 4px 0 16px; padding: 14px 16px; border: 1px solid var(--border); border-radius: 13px; background: var(--surface); }
    .notification-summary div { display: flex; align-items: center; gap: 8px; }
    .notification-summary div span { color: var(--muted); font-size: .82rem; }
    .notification-summary div strong { color: var(--text); font-size: .95rem; }
    .notification-summary p { margin: 0 0 0 auto; color: var(--muted); font-size: .78rem; }
    .notification-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 12px; }
    .notification-filters { display: flex; flex-wrap: wrap; gap: 7px; }
    .notification-filter { display: flex; align-items: center; gap: 7px; padding: 8px 12px; border: 1px solid var(--border); border-radius: 999px; background: var(--surface); color: var(--muted); font: inherit; font-size: .8rem; cursor: pointer; }
    .notification-filter span { display: inline-grid; place-items: center; min-width: 18px; height: 18px; padding: 0 4px; border-radius: 99px; background: var(--bg); font-size: .7rem; }
    .notification-filter.is-active { border-color: var(--accent); background: var(--accent-light); color: var(--accent); font-weight: 700; }
    .notification-search { display: flex; align-items: center; gap: 8px; width: min(100%, 280px); padding: 0 12px; border: 1px solid var(--border); border-radius: 10px; color: var(--muted); }
    .notification-search input { width: 100%; min-width: 0; padding: 10px 0; border: 0; outline: 0; background: transparent; color: var(--text); }
    .notification-list { display: flex; flex-direction: column; gap: 10px; }
    .notification-item { display: flex; align-items: flex-start; gap: 13px; padding: 16px; border: 1px solid var(--border); border-left: 4px solid var(--notification-accent); border-radius: 13px; background: var(--surface); transition: box-shadow .18s, transform .18s, opacity .18s; }
    .notification-item:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
    .notification-item.is-unread { background: color-mix(in srgb, var(--notification-accent) 4%, var(--surface)); }
    .notification-icon { display: grid; place-items: center; flex: 0 0 40px; width: 40px; height: 40px; border-radius: 12px; background: color-mix(in srgb, var(--notification-accent) 12%, transparent); font-size: 1.15rem; }
    .notification-content { min-width: 0; flex: 1; }
    .notification-title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .notification-title-row h4 { margin: 1px 0 0; color: var(--text); font-size: .92rem; }
    .notification-title-row time { flex: 0 0 auto; color: var(--muted); font-size: .75rem; }
    .notification-content > p { margin: 6px 0 10px; color: var(--muted); font-size: .84rem; line-height: 1.5; }
    .notification-meta { display: flex; align-items: center; gap: 9px; }
    .severity-tag { padding: 3px 8px; border-radius: 99px; background: color-mix(in srgb, var(--notification-accent) 11%, transparent); color: var(--notification-accent); font-size: .68rem; font-weight: 700; }
    .read-state { color: var(--muted); font-size: .7rem; }
    .unread-indicator { flex: 0 0 8px; width: 8px; height: 8px; margin-top: 6px; border-radius: 50%; background: var(--notification-accent); }
    .mark-read-button { align-self: center; padding: 6px 9px; border: 1px solid var(--border); border-radius: 8px; background: transparent; color: var(--accent); font: inherit; font-size: .75rem; font-weight: 700; cursor: pointer; }
    .mark-read-button:hover { background: var(--accent-light); }
    .notifications-empty { display: flex; flex-direction: column; align-items: center; gap: 7px; padding: 46px 16px; border: 1px dashed var(--border); border-radius: 14px; color: var(--muted); text-align: center; }
    .notifications-empty[hidden] { display: none; }
    .notifications-empty > span { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 50%; background: var(--accent-light); color: var(--accent); font-size: 1.25rem; }
    .notifications-empty strong { color: var(--text); }
    .notifications-empty p { margin: 0; font-size: .83rem; }
    @media (max-width: 680px) {
        .notifications-header { align-items: flex-start; gap: 12px; }
        .notification-summary { flex-wrap: wrap; gap: 10px 18px; }
        .notification-summary p { width: 100%; margin: 0; }
        .notification-toolbar { align-items: stretch; flex-direction: column; }
        .notification-search { width: 100%; }
    }
    @media (max-width: 480px) {
        .notification-item { gap: 9px; padding: 12px; }
        .notification-icon { flex-basis: 34px; width: 34px; height: 34px; }
        .notification-title-row { flex-direction: column; gap: 3px; }
        .mark-read-button { align-self: flex-start; }
    }
</style>

<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const list = document.getElementById('notification-list');
    const items = Array.from(list.querySelectorAll('.notification-item'));
    const filterButtons = Array.from(document.querySelectorAll('.notification-filter'));
    const search = document.getElementById('notification-search');
    const markAllButton = document.getElementById('markAllReadBtn');
    const feedback = document.getElementById('notification-feedback');
    const empty = document.getElementById('notifications-empty');
    let activeFilter = 'all';

    const showFeedback = (message, isError = false) => {
        feedback.textContent = message;
        feedback.hidden = false;
        feedback.classList.toggle('is-error', isError);
    };

    const unreadItems = () => items.filter((item) => item.dataset.read === '0');
    const updateCounts = () => {
        const unread = unreadItems().length;
        document.getElementById('notification-unread').textContent = unread;
        const unreadTab = document.querySelector('[data-filter="unread"] span');
        if (unreadTab) unreadTab.textContent = unread;
        if (markAllButton) markAllButton.disabled = unread === 0;
    };

    const applyView = () => {
        const query = search.value.trim().toLowerCase();
        let visibleCount = 0;
        items.forEach((item) => {
            const matchesFilter = activeFilter === 'all'
                || (activeFilter === 'unread' && item.dataset.read === '0')
                || (activeFilter === 'read' && item.dataset.read === '1');
            const visible = matchesFilter && item.dataset.search.includes(query);
            item.hidden = !visible;
            if (visible) visibleCount++;
        });
        empty.hidden = visibleCount > 0;
        document.getElementById('notifications-empty-title').textContent = items.length === 0
            ? 'You’re all caught up'
            : 'No matching notifications';
        document.getElementById('notifications-empty-copy').textContent = items.length === 0
            ? 'New fleet and account activity will appear here.'
            : 'Try a different search or filter.';
    };

    const markRead = async (item) => {
        if (!item || item.dataset.read === '1') return true;
        const button = item.querySelector('.mark-read-button');
        if (button) button.disabled = true;
        try {
            const response = await fetch(`/notifications/${encodeURIComponent(item.dataset.id)}/read`, {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            if (!response.ok) throw new Error('Could not update this notification. Please try again.');
            item.dataset.read = '1';
            item.classList.remove('is-unread');
            item.classList.add('is-read');
            item.querySelector('.unread-indicator')?.remove();
            item.querySelector('.read-state').textContent = 'Read';
            button?.remove();
            const bellBadge = document.getElementById('bellBadge');
            if (bellBadge && bellBadge.textContent !== '9+') {
                const remaining = Math.max(0, Number(bellBadge.textContent) - 1);
                if (remaining === 0) bellBadge.remove();
                else bellBadge.textContent = remaining > 9 ? '9+' : String(remaining);
            }
            updateCounts();
            applyView();
            return true;
        } catch (error) {
            if (button) button.disabled = false;
            showFeedback(error.message, true);
            return false;
        }
    };

    filterButtons.forEach((button) => button.addEventListener('click', () => {
        activeFilter = button.dataset.filter;
        filterButtons.forEach((filter) => {
            const selected = filter === button;
            filter.classList.toggle('is-active', selected);
            filter.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
        applyView();
    }));
    search.addEventListener('input', applyView);

    list.addEventListener('click', (event) => {
        const button = event.target.closest('.mark-read-button');
        if (button) {
            event.preventDefault();
            markRead(button.closest('.notification-item'));
        }
    });

    markAllButton?.addEventListener('click', async () => {
        markAllButton.disabled = true;
        try {
            const response = await fetch('<?= route('notifications.read-all') ?>', {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            if (!response.ok) throw new Error('Could not mark notifications as read. Please try again.');
            items.forEach((item) => {
                item.dataset.read = '1';
                item.classList.remove('is-unread');
                item.classList.add('is-read');
                item.querySelector('.unread-indicator')?.remove();
                item.querySelector('.mark-read-button')?.remove();
                item.querySelector('.read-state').textContent = 'Read';
            });
            updateCounts();
            applyView();
            showFeedback('All notifications marked as read.');
        } catch (error) {
            markAllButton.disabled = false;
            showFeedback(error.message, true);
        }
    });

    updateCounts();
    applyView();
})();
</script>
