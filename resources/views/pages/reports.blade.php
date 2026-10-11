<?php
$stats = $dashboard['stats'] ?? [];
$finance = $dashboard['finance'] ?? [];
$drivers = $dashboard['drivers'] ?? [];
$trend = $finance['trend'] ?? ['labels' => [], 'values' => []];
$costCards = $finance['cards'] ?? [];
$totalVehicles = $stats[0]['value'] ?? 0;
$activeVehicles = $stats[1]['value'] ?? 0;
$maintenanceVehicles = $stats[2]['value'] ?? 0;
$pendingReservations = $stats[3]['value'] ?? 0;
$fuelThisMonth = $finance['totals']['fuel_this_month'] ?? 0;
$maintenanceThisMonth = $finance['totals']['maintenance_this_month'] ?? 0;
$transportThisMonth = $finance['totals']['total_this_month'] ?? 0;
$peakCost = max(array_map('floatval', $trend['values'] ?: [0]));
$reportData = [
    'fleet' => [
        ['Metric', 'Value'],
        ['Vehicles in fleet', $totalVehicles],
        ['Active vehicles', $activeVehicles],
        ['Vehicles in maintenance', $maintenanceVehicles],
        ['Pending reservations', $pendingReservations],
        ['Snapshot date', now()->format('Y-m-d')],
    ],
    'drivers' => array_merge([['Rank', 'Driver', 'Role', 'Dispatches', 'Score']], array_map(
        fn ($driver, $index) => [$index + 1, $driver['name'] ?? '', $driver['role'] ?? '', $driver['dispatches'] ?? '', $driver['score'] ?? ''],
        $drivers,
        array_keys($drivers)
    )),
    'costs' => array_merge([['Month', 'Transport cost (PHP)']], array_map(
        fn ($label, $index) => [$label, $trend['values'][$index] ?? 0],
        $trend['labels'],
        array_keys($trend['labels'])
    )),
];
?>
<section class="panel reports-page">
    <div class="panel-header reports-header">
        <div>
            <p class="eyebrow">Reports</p>
            <h3>Fleet reports</h3>
            <p class="reports-intro">Review current fleet operations, driver performance, and transport costs.</p>
        </div>
        <div class="reports-actions">
            <button class="btn-secondary" type="button" onclick="window.print()">Print reports</button>
            <button class="pill-button" type="button" onclick="downloadAllReports()">Download CSV</button>
        </div>
    </div>

    <div class="reports-notice" role="note">
        <strong>Live snapshot</strong>
        <span>Fleet and driver figures reflect the current dashboard data. Cost history shows the last six months. CSV downloads include the values shown here.</span>
        <span class="reports-date">Prepared <?= htmlspecialchars(now()->format('M d, Y'), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="reports-grid">
        <article class="report-card">
            <div class="report-card-top">
                <span class="report-icon" aria-hidden="true">🚚</span>
                <span class="report-tag">Fleet status</span>
            </div>
            <h4>Fleet snapshot</h4>
            <p class="report-description">A quick view of vehicle availability and reservation workload right now.</p>
            <div class="report-metrics">
                <div><span>Total vehicles</span><strong><?= number_format((int) $totalVehicles) ?></strong></div>
                <div><span>Active</span><strong><?= number_format((int) $activeVehicles) ?></strong></div>
                <div><span>In maintenance</span><strong><?= number_format((int) $maintenanceVehicles) ?></strong></div>
                <div><span>Pending reservations</span><strong><?= number_format((int) $pendingReservations) ?></strong></div>
            </div>
            <div class="report-card-actions">
                <a href="<?= htmlspecialchars($dashboard['basePath'] . '/vehicles') ?>">View vehicles <span aria-hidden="true">→</span></a>
                <button type="button" class="report-download" onclick="downloadReport('fleet')">Export CSV</button>
            </div>
        </article>

        <article class="report-card">
            <div class="report-card-top">
                <span class="report-icon" aria-hidden="true">🏅</span>
                <span class="report-tag">Top <?= count($drivers) ?> shown</span>
            </div>
            <h4>Driver scorecard</h4>
            <p class="report-description">Compare the highest-scoring drivers currently returned by the dashboard.</p>
            <?php if (count($drivers)): ?>
                <ol class="driver-score-list">
                    <?php foreach ($drivers as $index => $driver): ?>
                        <li>
                            <span class="driver-rank"><?= $index + 1 ?></span>
                            <span class="driver-summary"><strong><?= htmlspecialchars($driver['name'] ?? 'Driver', ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars(($driver['role'] ?? 'Driver').' · '.($driver['dispatches'] ?? '0 dispatches'), ENT_QUOTES, 'UTF-8') ?></small></span>
                            <span class="driver-score"><?= htmlspecialchars((string) ($driver['score'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p class="report-empty">No driver score data is available yet.</p>
            <?php endif; ?>
            <div class="report-card-actions">
                <button type="button" class="report-download" onclick="downloadReport('drivers')">Export CSV</button>
            </div>
        </article>

        <article class="report-card report-card-wide">
            <div class="report-card-top">
                <span class="report-icon" aria-hidden="true">📈</span>
                <span class="report-tag">Last six months</span>
            </div>
            <div class="cost-report-heading">
                <div><h4>Monthly transport costs</h4><p class="report-description">Fuel and maintenance spend combined by month.</p></div>
                <div class="cost-total"><span>This month</span><strong>₱<?= number_format((float) $transportThisMonth, 2) ?></strong></div>
            </div>
            <div class="cost-breakdown">
                <span>Fuel <strong>₱<?= number_format((float) $fuelThisMonth, 2) ?></strong></span>
                <span>Maintenance <strong>₱<?= number_format((float) $maintenanceThisMonth, 2) ?></strong></span>
            </div>
            <div class="cost-chart" aria-label="Monthly transport costs for the last six months">
                <?php foreach ($trend['labels'] as $index => $label): ?>
                    <?php $value = (float) ($trend['values'][$index] ?? 0); $height = $peakCost > 0 ? max(4, ($value / $peakCost) * 100) : 4; ?>
                    <div class="cost-month">
                        <div class="cost-bar-area" title="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>: ₱<?= number_format($value, 2) ?>">
                            <span class="cost-bar" style="height:<?= min(100, $height) ?>%"></span>
                        </div>
                        <strong>₱<?= number_format($value, 0) ?></strong>
                        <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="report-card-actions">
                <a href="<?= htmlspecialchars($dashboard['basePath'] . '/cost-analytics') ?>">Open cost analytics <span aria-hidden="true">→</span></a>
                <a href="<?= htmlspecialchars($dashboard['basePath'] . '/fuel-logs') ?>">Review fuel logs <span aria-hidden="true">→</span></a>
                <button type="button" class="report-download" onclick="downloadReport('costs')">Export CSV</button>
            </div>
        </article>
    </div>
</section>

<style>
    .reports-intro { margin: 6px 0 0; color: var(--muted); font-size: .9rem; }
    .reports-actions { display: flex; align-items: center; gap: 10px; }
    .reports-notice { display: flex; align-items: center; gap: 10px; margin: 0 0 18px; padding: 13px 16px; border: 1px solid var(--border); border-radius: 12px; background: var(--surface); color: var(--muted); font-size: .83rem; line-height: 1.45; }
    .reports-notice strong { color: var(--text); white-space: nowrap; }
    .reports-date { margin-left: auto; white-space: nowrap; }
    .reports-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .report-card { display: flex; flex-direction: column; min-width: 0; padding: 20px; border: 1px solid var(--border); border-radius: 16px; background: var(--surface); }
    .report-card-wide { grid-column: 1 / -1; }
    .report-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .report-icon { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 13px; background: var(--accent-light); font-size: 1.15rem; }
    .report-tag { padding: 5px 9px; border-radius: 20px; background: var(--accent-light); color: var(--accent); font-size: .72rem; font-weight: 700; }
    .report-card h4 { margin: 0; color: var(--text); font-size: 1.04rem; }
    .report-description { margin: 7px 0 16px; color: var(--muted); font-size: .84rem; line-height: 1.5; }
    .report-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: auto 0 18px; }
    .report-metrics div { display: flex; flex-direction: column; gap: 4px; padding: 11px 12px; border-radius: 10px; background: var(--bg); }
    .report-metrics span, .cost-total span { color: var(--muted); font-size: .75rem; }
    .report-metrics strong { color: var(--text); font-size: 1.08rem; }
    .report-card-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-top: auto; padding-top: 14px; border-top: 1px solid var(--border); }
    .report-card-actions a, .report-download { padding: 0; border: 0; background: transparent; color: var(--accent); font: inherit; font-size: .82rem; font-weight: 700; text-decoration: none; cursor: pointer; }
    .report-card-actions a:hover, .report-download:hover { text-decoration: underline; }
    .driver-score-list { display: flex; flex-direction: column; gap: 2px; margin: 0 0 16px; padding: 0; list-style: none; }
    .driver-score-list li { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--border); }
    .driver-score-list li:last-child { border-bottom: 0; }
    .driver-rank { display: grid; place-items: center; width: 26px; height: 26px; flex: 0 0 26px; border-radius: 50%; background: var(--bg); color: var(--muted); font-size: .75rem; font-weight: 700; }
    .driver-summary { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
    .driver-summary strong { overflow: hidden; color: var(--text); font-size: .84rem; text-overflow: ellipsis; white-space: nowrap; }
    .driver-summary small { color: var(--muted); font-size: .73rem; }
    .driver-score { color: var(--accent); font-weight: 800; }
    .report-empty { color: var(--muted); font-size: .85rem; }
    .cost-report-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .cost-total { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; white-space: nowrap; }
    .cost-total strong { color: var(--text); font-size: 1.18rem; }
    .cost-breakdown { display: flex; flex-wrap: wrap; gap: 18px; margin: 2px 0 12px; color: var(--muted); font-size: .8rem; }
    .cost-breakdown strong { margin-left: 4px; color: var(--text); }
    .cost-chart { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; height: 180px; padding: 8px 0 14px; }
    .cost-month { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 5px; min-width: 0; }
    .cost-bar-area { display: flex; align-items: flex-end; justify-content: center; width: 100%; height: 115px; border-bottom: 1px solid var(--border); }
    .cost-bar { display: block; width: min(42px, 65%); min-height: 4px; border-radius: 7px 7px 2px 2px; background: linear-gradient(180deg, var(--accent), #4cc9f0); }
    .cost-month strong { overflow: hidden; max-width: 100%; color: var(--text); font-size: .73rem; text-overflow: ellipsis; white-space: nowrap; }
    .cost-month > span { color: var(--muted); font-size: .73rem; }
    @media (max-width: 760px) {
        .reports-grid { grid-template-columns: 1fr; }
        .report-card-wide { grid-column: auto; }
        .reports-notice { align-items: flex-start; flex-wrap: wrap; }
        .reports-date { width: 100%; margin-left: 0; }
    }
    @media (max-width: 540px) {
        .reports-header { align-items: flex-start; flex-direction: column; gap: 12px; }
        .reports-actions { width: 100%; }
        .reports-actions button { flex: 1; }
        .report-card { padding: 16px; }
        .cost-chart { gap: 5px; }
        .cost-month strong { font-size: .65rem; }
    }
    @media print {
        .sidebar, .topbar, .reports-actions, .report-card-actions { display: none !important; }
        .main-panel { width: 100% !important; padding: 0 !important; }
        .panel { box-shadow: none !important; }
        .reports-grid { display: block; }
        .report-card { margin-bottom: 12px; break-inside: avoid; }
        .reports-notice { background: #fff !important; }
    }
</style>

<script>
const reportData = <?= json_encode($reportData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

function downloadReport(type) {
    const rows = reportData[type];
    if (!rows) return;
    const csv = rows.map((row) => row.map((value) => `"${String(value ?? '').replaceAll('"', '""')}"`).join(',')).join('\r\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${type}_report_<?= now()->format('Y-m-d') ?>.csv`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(link.href);
}

function downloadAllReports() {
    const escapeCell = (value) => `"${String(value ?? '').replaceAll('"', '""')}"`;
    const rows = [];
    Object.entries(reportData).forEach(([type, section], index) => {
        if (index > 0) rows.push([]);
        rows.push([`${type.charAt(0).toUpperCase()}${type.slice(1)} report`]);
        rows.push(...section);
    });
    const csv = rows.map((row) => row.map(escapeCell).join(',')).join('\r\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `fleet_reports_<?= now()->format('Y-m-d') ?>.csv`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(link.href);
}
</script>
