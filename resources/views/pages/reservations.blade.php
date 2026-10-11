<?php
/**
 * Phase 1B – Dispatch Hub
 *
 * Variables injected by PageController when $page === 'reservations':
 *   $dashboard['pendingReservations']   – array of stdClass
 *   $dashboard['approvedReservations']  – array of stdClass
 *   $dashboard['scheduledDispatches']   – array of stdClass (joined vehicle + user)
 *   $dashboard['activeDispatches']      – array of stdClass (joined vehicle + user)
 *   $dashboard['availableVehicles']     – array of stdClass
 *   $dashboard['availableDrivers']      – array of stdClass  (driver_name from users.name)
 */
$pendingRes   = $dashboard['pendingReservations']   ?? [];
$rejectedRes  = $dashboard['rejectedReservations']  ?? [];
$dispatchedRes = $dashboard['dispatchedReservations'] ?? [];
$approvedRes  = $dashboard['approvedReservations']  ?? [];
$scheduledDis = $dashboard['scheduledDispatches']   ?? [];
$activeDis    = $dashboard['activeDispatches']       ?? [];
$vehicles     = $dashboard['availableVehicles']      ?? [];
$drivers      = $dashboard['availableDrivers']       ?? [];
?>

<style>
/* ── Dispatch Hub Page Styles ──────────────────────────── */
.hub-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.hub-kpi-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 20px 14px;
    box-shadow: var(--shadow);
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.hub-kpi-card .kpi-label {
    font-size: 0.71rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--muted);
}
.hub-kpi-card .kpi-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text);
    line-height: 1.1;
}
.hub-kpi-card .kpi-value.kpi-blue   { color: #4361ee; }
.hub-kpi-card .kpi-value.kpi-amber  { color: #f59e0b; }
.hub-kpi-card .kpi-value.kpi-green  { color: #22c55e; }
.hub-kpi-card .kpi-value.kpi-red    { color: #e5484d; }
.hub-kpi-card .kpi-sub {
    font-size: 0.73rem;
    color: var(--muted);
    font-weight: 500;
}

.hub-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.hub-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 22px;
}
.hub-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px 12px;
    border-bottom: 1px solid var(--border);
    gap: 10px;
    flex-wrap: wrap;
}
.hub-section-header .hs-title {
    font-size: 0.85rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--muted);
}
.hub-section-header .hs-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 7px;
    border-radius: 999px;
    background: var(--accent-light);
    color: var(--accent);
    font-size: 0.72rem;
    font-weight: 800;
    margin-left: 8px;
}

/* Status pills */
.sp { display: inline-block; padding: 3px 11px; border-radius: 999px; font-size: 0.73rem; font-weight: 700; letter-spacing: 0.03em; }
.sp-pending    { background: rgba(245,158,11,0.13);  color: #d97706; }
.sp-approved   { background: rgba(67,97,238,0.13);   color: #4361ee; }
.sp-dispatched { background: rgba(34,197,94,0.13);   color: #16a34a; }
.sp-rejected   { background: rgba(229,72,77,0.12);   color: #e5484d; }
.sp-scheduled  { background: rgba(76,201,240,0.13);  color: #0891b2; }
.sp-active     { background: rgba(34,197,94,0.13);   color: #16a34a; }
.sp-completed  { background: rgba(107,114,128,0.13); color: #6b7280; }
.sp-cancelled  { background: rgba(229,72,77,0.12);   color: #e5484d; }

/* Priority badges */
.pri { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 0.68rem; font-weight: 700; }
.pri-normal  { background: rgba(107,114,128,0.10); color: #6b7280; }
.pri-high    { background: rgba(245,158,11,0.13);  color: #d97706; }
.pri-urgent  { background: rgba(229,72,77,0.14);   color: #e5484d; }

/* Compact action buttons */
.act-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 0.74rem;
    font-weight: 700;
    border: 1.5px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease;
    background: none;
    white-space: nowrap;
}
.act-btn:hover { opacity: 0.85; transform: translateY(-1px); }
.act-approve  { background: rgba(67,97,238,0.1);  color: #4361ee;  border-color: rgba(67,97,238,0.2); }
.act-reject   { background: rgba(229,72,77,0.08); color: #e5484d; border-color: rgba(229,72,77,0.18); }
.act-convert  { background: rgba(34,197,94,0.10); color: #16a34a; border-color: rgba(34,197,94,0.2); }
.act-start    { background: rgba(34,197,94,0.10); color: #16a34a; border-color: rgba(34,197,94,0.2); }
.act-complete { background: rgba(67,97,238,0.1);  color: #4361ee;  border-color: rgba(67,97,238,0.2); }
.act-cancel   { background: rgba(229,72,77,0.08); color: #e5484d; border-color: rgba(229,72,77,0.18); }

/* Empty row */
.empty-row td { text-align: center; padding: 28px 20px; color: var(--muted); font-size: 0.83rem; font-style: italic; }

/* Flash messages */
.hub-flash-success { background: rgba(34,197,94,0.1); color: #16a34a; border: 1px solid rgba(34,197,94,0.25); padding: 11px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; font-size: 0.85rem; }
.hub-flash-error   { background: rgba(229,72,77,0.09); color: #e5484d; border: 1px solid rgba(229,72,77,0.2);  padding: 11px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; font-size: 0.85rem; }

/* Modal overrides (add to existing modal-backdrop / modal-card) */
.modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(10,18,35,0.55);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9000;
    padding: 20px;
    backdrop-filter: blur(3px);
}
@media (max-width: 560px) {
    .modal-backdrop { padding: 10px; align-items: flex-end; }
    .modal-card { max-height: 92vh; border-radius: 18px 18px 0 0; }
    .modal-card [style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
    .hub-kpi-grid { grid-template-columns: 1fr 1fr !important; }
}
.modal-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(20,33,61,0.22);
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 0;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px 14px;
    border-bottom: 1px solid var(--border);
}
.modal-header h3 { font-size: 1rem; font-weight: 800; color: var(--text); margin: 0; }
.modal-body { padding: 20px 22px; }
.modal-footer { padding: 14px 22px 18px; display: flex; gap: 10px; justify-content: flex-end; }
.close-btn {
    background: none; border: none; cursor: pointer;
    color: var(--muted); font-size: 1.1rem; line-height: 1;
    padding: 4px 6px; border-radius: 6px;
    transition: background 0.15s;
}
.close-btn:hover { background: rgba(229,72,77,0.08); color: #e5484d; }
.employee-combobox { position: relative; }
.employee-options {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 12000;
    max-height: 180px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 16px 40px rgba(20,33,61,.18);
    margin: 0;
    padding: 6px;
    list-style: none;
}
.employee-combobox.open .employee-options { display: block; }
.employee-options li { padding: 9px 10px; border-radius: 8px; cursor: pointer; text-align: left; }
.employee-options li.active, .employee-options li:hover { background: rgba(67,97,238,0.10); }
.employee-option-empty { color: var(--muted); cursor: default; }
@media (max-width: 560px) { .employee-options { max-height: 150px; } }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 0.78rem; font-weight: 700; color: var(--muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.05em; }
.form-control {
    width: 100%; padding: 9px 12px; border-radius: 9px;
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text); font-size: 0.86rem; font-family: inherit;
    transition: border-color 0.15s;
    box-sizing: border-box;
}
.form-control:focus { outline: none; border-color: var(--accent); }
.btn-primary {
    padding: 10px 20px; border-radius: 10px; border: none;
    background: linear-gradient(135deg,#4361ee,#3a0ca3);
    color: #fff; font-weight: 700; font-size: 0.86rem;
    cursor: pointer; transition: opacity 0.15s, transform 0.15s;
    box-shadow: 0 6px 14px rgba(67,97,238,0.35);
}
.btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
.btn-secondary {
    padding: 10px 18px; border-radius: 10px;
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text); font-weight: 600; font-size: 0.86rem;
    cursor: pointer; transition: opacity 0.15s;
}
.btn-secondary:hover { opacity: 0.8; }
.btn-danger {
    padding: 10px 18px; border-radius: 10px; border: none;
    background: linear-gradient(135deg,#e5484d,#b91c1c);
    color: #fff; font-weight: 700; font-size: 0.86rem;
    cursor: pointer; transition: opacity 0.15s, transform 0.15s;
    box-shadow: 0 6px 14px rgba(229,72,77,0.3);
}
.btn-danger:hover { opacity: 0.9; transform: translateY(-1px); }

[data-theme="dark"] .form-control { background: #1a2840 !important; border-color: #1e2e45 !important; color: #e4eaf5 !important; }
[data-theme="dark"] .hub-kpi-card { background: #162032 !important; border-color: #1e2e45 !important; }
[data-theme="dark"] .hub-section  { background: #162032 !important; border-color: #1e2e45 !important; }
[data-theme="dark"] .hub-section-header { border-color: #1e2e45 !important; }
</style>

<?php /* ── Page Wrapper ───────────────────────────────────── */ ?>
<section class="panel" style="background:transparent;border:none;box-shadow:none;padding:0;">

    <?php /* ── Flash Messages ─────────────────────────────── */ ?>
    <?php if (session('success')): ?>
        <div class="hub-flash-success">✓ <?= htmlspecialchars(session('success')) ?></div>
    <?php endif; ?>
    <?php if ($errors->any()): ?>
        <div class="hub-flash-error">⚠ <?= htmlspecialchars($errors->first()) ?></div>
    <?php endif; ?>

    <?php /* ── Top Header Row ──────────────────────────────── */ ?>
    <div class="panel-header" style="margin-bottom:18px;">
        <div>
            <p class="eyebrow">Operations Center</p>
            <h3 style="margin:0;font-size:1.3rem;font-weight:800;color:var(--text);">Dispatch Hub</h3>
        </div>
        <div class="hub-actions">
            <button type="button" class="pill-button" onclick="hubModal('modal-new-reservation')">
                + New Reservation
            </button>
            <?php if ($dashboard['isAdmin']): ?><button type="button" class="btn-primary" style="padding:8px 18px;font-size:0.84rem;" onclick="hubModal('modal-direct-dispatch')">
                🚐 Direct Dispatch
            </button><?php endif; ?>
        </div>
    </div>

    <?php /* ── KPI Summary Cards ───────────────────────────── */ ?>
    <div class="hub-kpi-grid">
        <div class="hub-kpi-card">
            <span class="kpi-label">Pending Reservations</span>
            <span class="kpi-value kpi-amber"><?= count($pendingRes) ?></span>
            <span class="kpi-sub">Awaiting approval</span>
        </div>
        <div class="hub-kpi-card">
            <span class="kpi-label">Approved Reservations</span>
            <span class="kpi-value kpi-blue"><?= count($approvedRes) ?></span>
            <span class="kpi-sub">Ready to dispatch</span>
        </div>
        <div class="hub-kpi-card">
            <span class="kpi-label">Scheduled Dispatches</span>
            <span class="kpi-value kpi-blue"><?= count($scheduledDis) ?></span>
            <span class="kpi-sub">Not yet started</span>
        </div>
        <div class="hub-kpi-card">
            <span class="kpi-label">Active Dispatches</span>
            <span class="kpi-value kpi-green"><?= count($activeDis) ?></span>
            <span class="kpi-sub">Currently en route</span>
        </div>
    </div>

    <?php /* ════════════════════════════════════════════════
              RESERVATION QUEUE
           ════════════════════════════════════════════════ */ ?>

    <?php /* ── Pending Reservations ────────────────────────── */ ?>
    <div class="hub-section">
        <div class="hub-section-header">
            <div>
                <span class="hs-title">Pending Reservations</span>
                <span class="hs-count"><?= count($pendingRes) ?></span>
            </div>
        </div>
        <div class="table-wrapper" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Res No.</th>
                        <th>Requester / Employee ID</th>
                        <th>Destination</th>
                        <th>Date</th>
                        <th>Vehicle Type</th>
                        <th>Pax</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pendingRes) === 0): ?>
                        <tr class="empty-row"><td colspan="8">No pending reservations.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pendingRes as $r): ?>
                            <tr>
                                <td style="font-weight:700;font-size:0.8rem;"><?= htmlspecialchars($r->reservation_no ?? '—') ?></td>
                                <td><?= htmlspecialchars($r->employee_id ?? '—') ?></td>
                                <td><?= htmlspecialchars($r->destination ?? '—') ?></td>
                                <td><?= $r->requested_date ? date('M d, Y', strtotime($r->requested_date)) : '—' ?></td>
                                <td><?= htmlspecialchars($r->vehicle_type ?? 'Any') ?></td>
                                <td><?= (int)($r->passenger_count ?? 1) ?></td>
                                <td><span class="sp sp-pending">Pending</span></td>
                                <td>
                                    <?php if ($dashboard['isAdmin']): ?><div style="display:flex;gap:6px;flex-wrap:wrap;">
                                        <?php /* Approve */ ?>
                                        <form method="POST" action="<?= route('reservations.approve', $r->id) ?>" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="act-btn act-approve" title="Approve">✓ Approve</button>
                                        </form>
                                        <?php /* Reject */ ?>
                                        <form method="POST" action="<?= route('reservations.reject', $r->id) ?>" style="display:inline;"
                                              onsubmit="return confirm('Reject this reservation?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="act-btn act-reject" title="Reject">✕ Reject</button>
                                        </form>
                                    </div><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php /* ── Approved Reservations ──────────────────────── */ ?>
    <div class="hub-section">
        <div class="hub-section-header">
            <div>
                <span class="hs-title">Approved Reservations</span>
                <span class="hs-count"><?= count($approvedRes) ?></span>
            </div>
        </div>
        <div class="table-wrapper" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Res No.</th>
                        <th>Requester / Employee ID</th>
                        <th>Destination</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($approvedRes) === 0): ?>
                        <tr class="empty-row"><td colspan="6">No approved reservations awaiting dispatch.</td></tr>
                    <?php else: ?>
                        <?php foreach ($approvedRes as $r): ?>
                            <tr>
                                <td style="font-weight:700;font-size:0.8rem;"><?= htmlspecialchars($r->reservation_no ?? '—') ?></td>
                                <td><?= htmlspecialchars($r->employee_id ?? '—') ?></td>
                                <td><?= htmlspecialchars($r->destination ?? '—') ?></td>
                                <td><?= $r->requested_date ? date('M d, Y', strtotime($r->requested_date)) : '—' ?></td>
                                <td><span class="sp sp-approved">Approved</span></td>
                                <td>
                                    <?php if ($dashboard['isAdmin']): ?><button type="button" class="act-btn act-convert"
                                            onclick="openConvertModal(<?= (int)$r->id ?>, '<?= htmlspecialchars(addslashes($r->reservation_no ?? ''), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($r->destination ?? ''), ENT_QUOTES) ?>')">
                                        🚐 Convert to Dispatch
                                    </button><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php /* ════════════════════════════════════════════════
              DISPATCH QUEUE
           ════════════════════════════════════════════════ */ ?>

    <?php /* ── Scheduled Dispatches ──────────────────────── */ ?>
    <div class="hub-section">
        <div class="hub-section-header">
            <div>
                <span class="hs-title">Scheduled Dispatches</span>
                <span class="hs-count"><?= count($scheduledDis) ?></span>
            </div>
        </div>
        <div class="table-wrapper" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Dispatch No.</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Destination</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($scheduledDis) === 0): ?>
                        <tr class="empty-row"><td colspan="6">No scheduled dispatches.</td></tr>
                    <?php else: ?>
                        <?php foreach ($scheduledDis as $d): ?>
                            <tr>
                                <td style="font-weight:700;font-size:0.8rem;"><?= htmlspecialchars($d->dispatch_no ?? '—') ?></td>
                                <td><?= htmlspecialchars($d->plate_number ?? '—') ?></td>
                                <td><?= htmlspecialchars($d->driver_name ?? '—') ?></td>
                                <td><?= htmlspecialchars($d->destination ?? '—') ?></td>
                                <td>
                                    <?php
                                    $pri = strtolower($d->priority ?? 'normal');
                                    $priClass = match($pri) { 'high' => 'pri-high', 'urgent' => 'pri-urgent', default => 'pri-normal' };
                                    ?>
                                    <span class="pri <?= $priClass ?>"><?= ucfirst($d->priority ?? 'Normal') ?></span>
                                </td>
                                <td><span class="sp sp-scheduled">Scheduled</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php /* ── Active Dispatches ────────────────────────────── */ ?>
    <div class="hub-section">
        <div class="hub-section-header">
            <div>
                <span class="hs-title">Active Dispatches</span>
                <span class="hs-count"><?= count($activeDis) ?></span>
            </div>
        </div>
        <div class="table-wrapper" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Dispatch No.</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Destination</th>
                        <th>Started At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($activeDis) === 0): ?>
                        <tr class="empty-row"><td colspan="6">No active dispatches.</td></tr>
                    <?php else: ?>
                        <?php foreach ($activeDis as $d): ?>
                            <tr>
                                <td style="font-weight:700;font-size:0.8rem;"><?= htmlspecialchars($d->dispatch_no ?? '—') ?></td>
                                <td><?= htmlspecialchars($d->plate_number ?? '—') ?></td>
                                <td><?= htmlspecialchars($d->driver_name ?? '—') ?></td>
                                <td><?= htmlspecialchars($d->destination ?? '—') ?></td>
                                <td style="font-size:0.8rem;color:var(--muted);">
                                    <?= $d->updated_at ? date('M d, H:i', strtotime($d->updated_at)) : '—' ?>
                                </td>
                                <td><span class="sp sp-active">Active</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (count($dispatchedRes) > 0): ?>
    <div class="hub-section">
        <div class="hub-section-header"><div><span class="hs-title">Dispatched Reservations</span><span class="hs-count"><?= count($dispatchedRes) ?></span></div></div>
        <div class="table-wrapper" style="overflow-x:auto;">
            <table>
                <thead><tr><th>Res No.</th><th>Requester / Employee ID</th><th>Destination</th><th>Requested Date</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($dispatchedRes as $r): ?>
                        <tr>
                            <td style="font-weight:700;font-size:0.8rem;"><?= htmlspecialchars($r->reservation_no ?? '—') ?></td>
                            <td><?= htmlspecialchars($r->requester_name ?? $r->employee_id ?? '—') ?></td>
                            <td><?= htmlspecialchars($r->destination ?? '—') ?></td>
                            <td><?= $r->requested_date ? date('M d, Y', strtotime($r->requested_date)) : '—' ?></td>
                            <td><span class="sp sp-active">Dispatched</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($rejectedRes) > 0): ?>
    <div class="hub-section">
        <div class="hub-section-header">
            <div>
                <span class="hs-title">Rejected Reservation History</span>
                <span class="hs-count"><?= count($rejectedRes) ?></span>
            </div>
        </div>
        <div class="table-wrapper" style="overflow-x:auto;">
            <table>
                <thead><tr><th>Res No.</th><th>Employee</th><th>Destination</th><th>Requested Date</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($rejectedRes as $r): ?>
                        <tr>
                            <td style="font-weight:700;font-size:0.8rem;"><?= htmlspecialchars($r->reservation_no ?? '—') ?></td>
                            <td><?= htmlspecialchars($r->employee_id ?? '—') ?></td>
                            <td><?= htmlspecialchars($r->destination ?? '—') ?></td>
                            <td><?= $r->requested_date ? date('M d, Y', strtotime($r->requested_date)) : '—' ?></td>
                            <td><span class="sp sp-rejected">Rejected</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php /* ════════════════════════════════════════════════════
          MODAL 1 – New Reservation
       ════════════════════════════════════════════════════ */ ?>
<div id="modal-new-reservation" class="modal-backdrop" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>New Reservation</h3>
            <button type="button" class="close-btn" onclick="closeHubModal('modal-new-reservation')">✕</button>
        </div>
        <form id="new-reservation-form" method="POST" action="<?= route($dashboard['isAdmin'] ? 'reservations.store' : 'users.reservations.store') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <?php
                $oldEmployeeId = old('employee_id', '');
                $oldEmployeeLabel = '';
                foreach ($drivers as $driverOption) {
                    if ($driverOption->employee_id === $oldEmployeeId) {
                        $oldEmployeeLabel = $driverOption->driver_name . ' — ' . $driverOption->employee_id;
                        break;
                    }
                }
                ?>
                <div class="form-group">
                    <label for="res-employee-search">Employee ID</label>
                    <?php if ($dashboard['isAdmin']): ?>
                        <div class="employee-combobox" id="res-employee-combobox">
                            <input class="form-control" type="text" id="res-employee-search" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="res-employee-options" autocomplete="off" value="<?= htmlspecialchars($oldEmployeeLabel, ENT_QUOTES, 'UTF-8') ?>" required placeholder="Search driver name or employee ID">
                            <input type="hidden" name="employee_id" id="res-employee-id" value="<?= htmlspecialchars($oldEmployeeId, ENT_QUOTES, 'UTF-8') ?>">
                            <ul class="employee-options" id="res-employee-options" role="listbox">
                                <?php foreach ($drivers as $driverOption): ?>
                                    <?php $driverLabel = htmlspecialchars($driverOption->driver_name . ' — ' . $driverOption->employee_id, ENT_QUOTES, 'UTF-8'); ?>
                                    <li role="option" data-id="<?= (int) $driverOption->id ?>" data-value="<?= htmlspecialchars($driverOption->employee_id, ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars(strtolower($driverOption->driver_name . ' ' . $driverOption->employee_id), ENT_QUOTES, 'UTF-8') ?>"><?= $driverLabel ?></li>
                                <?php endforeach; ?>
                                <?php if (count($drivers) === 0): ?>
                                    <li class="employee-option-empty">No active drivers available</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <input class="form-control" type="text" id="res-employee-id" value="<?= htmlspecialchars($dashboard['user']['name'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="res-destination">Destination</label>
                    <input class="form-control" type="text" id="res-destination" name="destination" value="<?= htmlspecialchars(old('destination', ''), ENT_QUOTES, 'UTF-8') ?>" required maxlength="255" placeholder="e.g. Cebu City Hall">
                </div>
                <div class="form-group">
                    <label for="res-purpose">Purpose</label>
                    <input class="form-control" type="text" id="res-purpose" name="purpose" value="<?= htmlspecialchars(old('purpose', ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="255" placeholder="e.g. Site inspection" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label for="res-requested-date">Requested Date</label>
                        <input class="form-control" type="date" id="res-requested-date" name="requested_date" value="<?= htmlspecialchars(old('requested_date', ''), ENT_QUOTES, 'UTF-8') ?>" min="<?= now()->toDateString() ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="res-requested-time">Requested Time</label>
                        <input class="form-control" type="time" id="res-requested-time" name="requested_time" value="<?= htmlspecialchars(old('requested_time', ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label for="res-vehicle-type">Vehicle Preference</label>
                        <select class="form-control" id="res-vehicle-type" name="vehicle_type">
                            <option value="Any / No Preference" <?= old('vehicle_type', 'Any / No Preference') === 'Any / No Preference' ? 'selected' : '' ?>>Any / No Preference</option>
                            <?php foreach ($vehicles as $v): ?>
                                <?php $vehiclePreference = ($v->type ?? 'Vehicle') . ' (' . $v->plate_number . ')'; ?>
                                <option value="<?= htmlspecialchars($vehiclePreference, ENT_QUOTES, 'UTF-8') ?>" <?= old('vehicle_type') === $vehiclePreference ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($v->plate_number . ' - ' . ($v->type ?? 'N/A')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="res-passenger-count">Passengers</label>
                        <input class="form-control" type="number" id="res-passenger-count" name="passenger_count" value="<?= htmlspecialchars(old('passenger_count', '1'), ENT_QUOTES, 'UTF-8') ?>" min="1" max="60" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="res-remarks">Remarks</label>
                    <textarea class="form-control" id="res-remarks" name="remarks" rows="2" placeholder="Optional notes…"><?= htmlspecialchars(old('remarks', ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeHubModal('modal-new-reservation')">Cancel</button>
                <button type="submit" class="btn-primary">Submit Reservation</button>
            </div>
        </form>
    </div>
</div>

<?php /* ════════════════════════════════════════════════════
          MODAL 2 – Direct Dispatch (no reservation)
       ════════════════════════════════════════════════════ */ ?>
<?php if ($dashboard['isAdmin']): ?>
<div id="modal-direct-dispatch" class="modal-backdrop" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Direct Dispatch</h3>
            <button type="button" class="close-btn" onclick="closeHubModal('modal-direct-dispatch')">✕</button>
        </div>
        <form method="POST" action="<?= route('dispatches.store') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="dd-vehicle">Vehicle</label>
                    <select class="form-control" id="dd-vehicle" name="vehicle_id" required>
                        <option value="">— Select Vehicle —</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= (int)$v->id ?>"><?= htmlspecialchars($v->plate_number . ' (' . ($v->type ?? 'N/A') . ')') ?></option>
                        <?php endforeach; ?>
                        <?php if (count($vehicles) === 0): ?>
                            <option disabled>No available vehicles</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dd-driver">Driver</label>
                    <select class="form-control" id="dd-driver" name="driver_id" required>
                        <option value="">— Select Driver —</option>
                        <?php foreach ($drivers as $dr): ?>
                            <option value="<?= (int)$dr->id ?>"><?= htmlspecialchars($dr->driver_name) ?></option>
                        <?php endforeach; ?>
                        <?php if (count($drivers) === 0): ?>
                            <option disabled>No available drivers</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dd-origin">Origin</label>
                    <input class="form-control" type="text" id="dd-origin" name="origin" maxlength="255" placeholder="e.g. Main Office">
                </div>
                <div class="form-group">
                    <label for="dd-destination">Destination</label>
                    <input class="form-control" type="text" id="dd-destination" name="destination" required maxlength="255" placeholder="e.g. Cebu South Bus Terminal">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="dd-priority">Priority</label>
                    <select class="form-control" id="dd-priority" name="priority">
                        <option value="Normal">Normal</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeHubModal('modal-direct-dispatch')">Cancel</button>
                <button type="submit" class="btn-primary">Create Dispatch</button>
            </div>
        </form>
    </div>
</div>

<?php /* ════════════════════════════════════════════════════
          MODAL 3 – Convert Reservation → Dispatch
          (dynamically set action URL via JS)
       ════════════════════════════════════════════════════ */ ?>
<div id="modal-convert-dispatch" class="modal-backdrop" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Convert to Dispatch</h3>
            <button type="button" class="close-btn" onclick="closeHubModal('modal-convert-dispatch')">✕</button>
        </div>
        <form id="convert-dispatch-form" method="POST" action="">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="background:var(--accent-light);border-radius:10px;padding:11px 14px;margin-bottom:16px;font-size:0.84rem;">
                    <strong>Reservation:</strong> <span id="convert-res-no">—</span><br>
                    <strong>Destination:</strong> <span id="convert-res-dest">—</span>
                </div>
                <div class="form-group">
                    <label for="cv-vehicle">Vehicle</label>
                    <select class="form-control" id="cv-vehicle" name="vehicle_id" required>
                        <option value="">— Select Vehicle —</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= (int)$v->id ?>"><?= htmlspecialchars($v->plate_number . ' (' . ($v->type ?? 'N/A') . ')') ?></option>
                        <?php endforeach; ?>
                        <?php if (count($vehicles) === 0): ?>
                            <option disabled>No available vehicles</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="cv-driver">Driver</label>
                    <select class="form-control" id="cv-driver" name="driver_id" required>
                        <option value="">— Select Driver —</option>
                        <?php foreach ($drivers as $dr): ?>
                            <option value="<?= (int)$dr->id ?>"><?= htmlspecialchars($dr->driver_name) ?></option>
                        <?php endforeach; ?>
                        <?php if (count($drivers) === 0): ?>
                            <option disabled>No available drivers</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeHubModal('modal-convert-dispatch')">Cancel</button>
                <button type="submit" class="btn-primary">Dispatch Now</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php /* ════════════════════════════════════════════════════
          JavaScript
       ════════════════════════════════════════════════════ */ ?>
<script>
    // Base URL for convert route (without the reservation ID)
    var convertBaseUrl = <?= json_encode(url('/dispatches/convert')) ?>;

    // Employee ID combobox (admin New Reservation)
    (function () {
        const comboInput = document.getElementById('res-employee-search');
        const comboList  = document.getElementById('res-employee-options');
        const hiddenInput = document.getElementById('res-employee-id');
        const combobox = document.getElementById('res-employee-combobox');
        const form = document.getElementById('new-reservation-form');
        if (!comboInput || !comboList || !hiddenInput || !combobox) return;

        let activeIndex = -1;

        function options() {
            return Array.from(comboList.querySelectorAll('li[data-value]'));
        }
        function visibleOptions() {
            return options().filter(li => li.style.display !== 'none');
        }
        function openList() { combobox.classList.add('open'); comboInput.setAttribute('aria-expanded', 'true'); }
        function closeList() { combobox.classList.remove('open'); comboInput.setAttribute('aria-expanded', 'false'); activeIndex = -1; highlight(); }
        function highlight() {
            const vis = visibleOptions();
            options().forEach(li => li.classList.remove('active'));
            if (activeIndex >= 0 && activeIndex < vis.length) vis[activeIndex].classList.add('active');
        }
        function filterList() {
            const query = comboInput.value.trim().toLowerCase();
            options().forEach(li => {
                const text = (li.dataset.search || li.textContent).toLowerCase();
                li.style.display = (!query || text.includes(query)) ? '' : 'none';
            });
            activeIndex = -1;
            filterNoResults();
            openList();
        }
        function filterNoResults() {
            const empty = comboList.querySelector('.employee-option-empty');
            if (empty) empty.style.display = visibleOptions().length === 0 ? '' : 'none';
        }
        function choose(item) {
            comboInput.value = item.textContent.trim();
            hiddenInput.value = item.dataset.value;
            comboInput.setCustomValidity('');
            closeList();
        }

        comboInput.addEventListener('focus', function () { activeIndex = -1; filterList(); });
        comboInput.addEventListener('input', function () {
            hiddenInput.value = '';
            filterList();
        });
        comboInput.addEventListener('blur', function () { setTimeout(closeList, 150); });
        comboInput.addEventListener('keydown', function (e) {
            const vis = visibleOptions();
            if (e.key === 'ArrowDown') { activeIndex = Math.min(activeIndex + 1, vis.length - 1); highlight(); e.preventDefault(); }
            else if (e.key === 'ArrowUp') { activeIndex = Math.max(activeIndex - 1, 0); highlight(); e.preventDefault(); }
            else if (e.key === 'Enter') {
                if (combobox.classList.contains('open') && activeIndex >= 0 && vis[activeIndex]) { choose(vis[activeIndex]); e.preventDefault(); }
            }
            else if (e.key === 'Escape') { closeList(); e.preventDefault(); }
        });
        comboList.addEventListener('mousedown', function (e) {
            const item = e.target.closest('li[data-value]');
            if (item) choose(item);
        });
        if (form) {
            form.addEventListener('submit', function (e) {
                if (comboInput && hiddenInput && !hiddenInput.value) {
                    const query = comboInput.value.trim().toLowerCase();
                    const match = options().find(item => {
                        const label = item.textContent.trim().toLowerCase();
                        const search = (item.dataset.search || label).toLowerCase();
                        return search === query || label === query || (item.dataset.value || '').toLowerCase() === query;
                    });
                    if (match) { choose(match); return; }
                    comboInput.setCustomValidity('Please select an employee from the list.');
                    comboInput.reportValidity();
                    e.preventDefault();
                }
            });
        }
    })();

    function hubModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeHubModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openConvertModal(reservationId, resNo, destination) {
        document.getElementById('convert-dispatch-form').action = convertBaseUrl + '/' + reservationId;
        document.getElementById('convert-res-no').textContent   = resNo;
        document.getElementById('convert-res-dest').textContent = destination;
        // Reset dropdowns
        document.getElementById('cv-vehicle').value = '';
        document.getElementById('cv-driver').value  = '';
        hubModal('modal-convert-dispatch');
    }

    // Close modal when clicking backdrop
    document.addEventListener('click', function (e) {
        var modals = document.querySelectorAll('.modal-backdrop');
        modals.forEach(function (modal) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop').forEach(function (modal) {
                modal.style.display = 'none';
            });
        }
    });

    <?php if ($errors->any() && (old('employee_id') !== null || ! $dashboard['isAdmin'])): ?>
        hubModal('modal-new-reservation');
    <?php endif; ?>
</script>
