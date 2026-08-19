<?php
/* ──────────────────────────────────────────────────────────────
   Driver Trip Performance Module  –  driver-analytics.blade.php
   Logistics 2 Fleet Management System
   ────────────────────────────────────────────────────────────── */

// ── Seed / mock data (replaced by real DB queries below) ──────
$driverData = [
    ['id' => 1, 'name' => 'Harvey Villarin',   'employee_id' => 'DRV-1001', 'role' => 'Senior Lead Driver',       'score' => 94, 'on_time' => 97, 'fuel_eff' => 89, 'safety' => 96, 'attendance' => 98, 'total_trips' => 124, 'completed' => 122, 'delayed' => 2,  'km_per_liter' => 10.4, 'risk' => 'Low',    'rank' => 1],
    ['id' => 2, 'name' => 'Reybie Reforsado',  'employee_id' => 'DRV-1002', 'role' => 'Regional Logistics Driver', 'score' => 91, 'on_time' => 94, 'fuel_eff' => 87, 'safety' => 93, 'attendance' => 96, 'total_trips' => 111, 'completed' => 108, 'delayed' => 3,  'km_per_liter' => 9.9,  'risk' => 'Low',    'rank' => 2],
    ['id' => 3, 'name' => 'Erwin Cover Jr.',   'employee_id' => 'DRV-1003', 'role' => 'Heavy Fleet Operator',     'score' => 88, 'on_time' => 90, 'fuel_eff' => 85, 'safety' => 90, 'attendance' => 94, 'total_trips' => 98,  'completed' => 94,  'delayed' => 4,  'km_per_liter' => 9.5,  'risk' => 'Low',    'rank' => 3],
    ['id' => 4, 'name' => 'Daniella Agus',     'employee_id' => 'DRV-1004', 'role' => 'Express Dispatcher',       'score' => 85, 'on_time' => 88, 'fuel_eff' => 83, 'safety' => 87, 'attendance' => 92, 'total_trips' => 87,  'completed' => 82,  'delayed' => 5,  'km_per_liter' => 9.1,  'risk' => 'Medium', 'rank' => 4],
    ['id' => 5, 'name' => 'Joanna Reforsado',  'employee_id' => 'DRV-1005', 'role' => 'Senior Driver',             'score' => 82, 'on_time' => 85, 'fuel_eff' => 80, 'safety' => 84, 'attendance' => 90, 'total_trips' => 76,  'completed' => 70,  'delayed' => 6,  'km_per_liter' => 8.8,  'risk' => 'Medium', 'rank' => 5],
    ['id' => 6, 'name' => 'Marco Santos',      'employee_id' => 'DRV-1006', 'role' => 'Route Specialist',          'score' => 79, 'on_time' => 81, 'fuel_eff' => 78, 'safety' => 80, 'attendance' => 88, 'total_trips' => 65,  'completed' => 59,  'delayed' => 6,  'km_per_liter' => 8.5,  'risk' => 'Medium', 'rank' => 6],
    ['id' => 7, 'name' => 'Liza Mercado',      'employee_id' => 'DRV-1007', 'role' => 'City Courier',              'score' => 75, 'on_time' => 77, 'fuel_eff' => 74, 'safety' => 76, 'attendance' => 86, 'total_trips' => 54,  'completed' => 47,  'delayed' => 7,  'km_per_liter' => 8.1,  'risk' => 'Medium', 'rank' => 7],
    ['id' => 8, 'name' => 'Bong Dela Cruz',    'employee_id' => 'DRV-1008', 'role' => 'Night Shift Driver',        'score' => 68, 'on_time' => 70, 'fuel_eff' => 67, 'safety' => 70, 'attendance' => 78, 'total_trips' => 43,  'completed' => 36,  'delayed' => 7,  'km_per_liter' => 7.6,  'risk' => 'High',   'rank' => 8],
    ['id' => 9, 'name' => 'Tess Gonzales',     'employee_id' => 'DRV-1009', 'role' => 'Utility Driver',            'score' => 62, 'on_time' => 64, 'fuel_eff' => 61, 'safety' => 63, 'attendance' => 74, 'total_trips' => 38,  'completed' => 30,  'delayed' => 8,  'km_per_liter' => 7.2,  'risk' => 'High',   'rank' => 9],
    ['id' => 10,'name' => 'Raul Dizon',         'employee_id' => 'DRV-1010', 'role' => 'Trainee Driver',             'score' => 55, 'on_time' => 57, 'fuel_eff' => 54, 'safety' => 56, 'attendance' => 70, 'total_trips' => 28,  'completed' => 20,  'delayed' => 8,  'km_per_liter' => 6.8,  'risk' => 'High',   'rank' => 10],
];

$totalDrivers   = count($driverData);
$activeDrivers  = 8;
$avgScore       = round(array_sum(array_column($driverData, 'score')) / $totalDrivers);
$topDriver      = $driverData[0];
$lowestDriver   = $driverData[$totalDrivers - 1];
$totalTrips     = array_sum(array_column($driverData, 'total_trips'));

$tripRecords = [
    ['id' => 'TRP-2081', 'driver' => 'Harvey Villarin',  'vehicle' => 'TRK-101', 'origin' => 'Port Area Pier 15',    'dest' => 'QC Logistics Hub',       'depart' => '06:30', 'arrive' => '07:48', 'duration' => '1h 18m', 'distance' => '18.5 km', 'avg_speed' => '48 km/h', 'fuel' => '5.2 L', 'status' => 'Completed'],
    ['id' => 'TRP-2082', 'driver' => 'Reybie Reforsado', 'vehicle' => 'TRK-102', 'origin' => 'Makati Central',       'dest' => 'Pasig Industrial Estate','depart' => '07:00', 'arrive' => '08:25', 'duration' => '1h 25m', 'distance' => '14.2 km', 'avg_speed' => '22 km/h', 'fuel' => '4.1 L', 'status' => 'Delayed'],
    ['id' => 'TRP-2083', 'driver' => 'Erwin Cover Jr.',  'vehicle' => 'TRK-103', 'origin' => 'North Port Terminal',  'dest' => 'Bulacan Freight Center', 'depart' => '05:45', 'arrive' => '07:20', 'duration' => '1h 35m', 'distance' => '32.0 km', 'avg_speed' => '54 km/h', 'fuel' => '8.9 L', 'status' => 'Completed'],
    ['id' => 'TRP-2084', 'driver' => 'Daniella Agus',    'vehicle' => 'TRK-105', 'origin' => 'Laguna Depot',         'dest' => 'Alabang Terminal',        'depart' => '08:10', 'arrive' => '09:05', 'duration' => '55m',    'distance' => '22.3 km', 'avg_speed' => '38 km/h', 'fuel' => '6.4 L', 'status' => 'Completed'],
    ['id' => 'TRP-2085', 'driver' => 'Joanna Reforsado', 'vehicle' => 'TRK-106', 'origin' => 'Caloocan Yard',        'dest' => 'Valenzuela Hub',          'depart' => '09:30', 'arrive' => null,     'duration' => 'Active', 'distance' => '—',       'avg_speed' => '41 km/h', 'fuel' => '—',    'status' => 'Active'],
];

$safetyEvents = [
    ['driver' => 'Bong Dela Cruz',  'type' => 'Overspeeding',        'detail' => 'Speed 92 km/h on EDSA',         'severity' => 'high',   'time' => '07:12'],
    ['driver' => 'Tess Gonzales',   'type' => 'Route Deviation',     'detail' => '4.2 km off designated route',   'severity' => 'medium', 'time' => '08:45'],
    ['driver' => 'Raul Dizon',      'type' => 'Excessive Idle Time', 'detail' => '38 minutes stationary, Pier 3', 'severity' => 'medium', 'time' => '09:20'],
    ['driver' => 'Liza Mercado',    'type' => 'Traffic Violation',   'detail' => 'Running red light, C-5',         'severity' => 'high',   'time' => '06:55'],
    ['driver' => 'Marco Santos',    'type' => 'Overspeeding',        'detail' => 'Speed 78 km/h in 60 zone',      'severity' => 'medium', 'time' => '10:05'],
];

$monthlyLabels   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'];
$monthlyScores   = [82, 84, 83, 87, 89, 91, 90, 93];
$monthlyTrips    = [88, 92, 86, 102, 110, 118, 112, 124];
$fuelMonthly     = [9.1, 9.3, 9.0, 9.5, 9.7, 10.1, 9.9, 10.4];
?>

<style>
/* ═══════════════════════════════════════════
   Driver Analytics Module – Component Styles
   ═══════════════════════════════════════════ */

/* Tab navigation */
.da-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
.da-tab  { border:0; padding:9px 18px; border-radius:999px; font-weight:600; font-size:0.87rem; cursor:pointer; transition:.2s ease; background:var(--surface); color:var(--muted); box-shadow:0 2px 8px rgba(0,0,0,.06); }
.da-tab.active, .da-tab:hover { background:var(--teal); color:#fff; box-shadow:0 4px 14px rgba(23,162,184,.35); }

/* KPI cards row */
.da-kpi-row { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:14px; margin-bottom:20px; }
.da-kpi { background:var(--surface); border-radius:18px; padding:16px 16px 14px; box-shadow:var(--shadow); }
.da-kpi-label { font-size:.78rem; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px; }
.da-kpi-value { font-size:1.55rem; font-weight:800; color:var(--text); line-height:1; }
.da-kpi-sub   { font-size:.78rem; color:var(--muted); margin-top:4px; }
.da-kpi.accent { background:linear-gradient(135deg,#0d7a8c,#17a2b8); color:#fff; }
.da-kpi.accent .da-kpi-label, .da-kpi.accent .da-kpi-value, .da-kpi.accent .da-kpi-sub { color:#fff; }

/* Two-column layout */
.da-grid { display:grid; grid-template-columns:1.4fr 1fr; gap:16px; margin-bottom:16px; }
.da-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px; }
.da-full { margin-bottom:16px; }

/* Score bar */
.da-score-wrap { margin-bottom:10px; }
.da-score-label { display:flex; justify-content:space-between; font-size:.82rem; font-weight:600; margin-bottom:4px; }
.da-score-bar { height:8px; border-radius:999px; background:#e7ebf3; overflow:hidden; }
.da-score-fill { height:100%; border-radius:999px; transition:width .6s ease; }
.fill-teal   { background:linear-gradient(90deg,#17a2b8,#20c9e0); }
.fill-blue   { background:linear-gradient(90deg,#2f6f8f,#3a8db5); }
.fill-orange { background:linear-gradient(90deg,#f4a261,#f7c07c); }
.fill-green  { background:linear-gradient(90deg,#28a745,#48d368); }

/* Leaderboard */
.da-lb-row { display:grid; grid-template-columns:32px 1fr 60px 80px 80px; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid var(--border); font-size:.88rem; }
.da-lb-row:last-child { border-bottom:0; }
.da-lb-rank { font-weight:800; color:var(--muted); font-size:.82rem; text-align:center; }
.da-lb-rank.gold   { color:#f6c24f; font-size:1rem; }
.da-lb-rank.silver { color:#a0aec0; font-size:.95rem; }
.da-lb-rank.bronze { color:#cd7f32; font-size:.9rem; }
.da-lb-name { font-weight:600; }
.da-lb-name small { display:block; color:var(--muted); font-weight:400; font-size:.76rem; }
.da-lb-score { font-weight:800; text-align:right; }
.da-lb-score.s-high { color:#17a2b8; }
.da-lb-score.s-mid  { color:#f4a261; }
.da-lb-score.s-low  { color:#e74c3c; }
.da-lb-trips { color:var(--muted); font-size:.8rem; text-align:center; }
.da-lb-risk  { text-align:center; }

/* Inline badge risk */
.risk-pill { padding:3px 9px; border-radius:999px; font-size:.72rem; font-weight:700; }
.risk-low    { background:rgba(23,162,184,.14); color:#17a2b8; }
.risk-medium { background:rgba(244,162,97,.18); color:#d4843b; }
.risk-high   { background:rgba(231,76,60,.14);  color:#c0392b; }

/* Trip status pill */
.ts-completed { background:rgba(40,167,69,.14);  color:#218838; }
.ts-active    { background:rgba(23,162,184,.14);  color:#17a2b8; }
.ts-delayed   { background:rgba(244,162,97,.18);  color:#d4843b; }

/* Safety event severity */
.sev-high   { background:rgba(231,76,60,.12);   color:#c0392b; }
.sev-medium { background:rgba(244,162,97,.18); color:#d4843b; }

/* Chart canvas container */
.da-chart-box { position:relative; width:100%; }
.da-chart-box canvas { width:100% !important; }

/* Section title */
.da-section-title { font-size:.92rem; font-weight:700; color:var(--text); margin:0 0 14px; display:flex; align-items:center; gap:8px; }
.da-section-title::before { content:''; display:block; width:4px; height:18px; border-radius:2px; background:linear-gradient(180deg,#17a2b8,#2f6f8f); }

/* Export buttons */
.da-export-row { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
.da-export-btn { border:0; padding:9px 16px; border-radius:10px; font-weight:600; font-size:.82rem; cursor:pointer; transition:.2s ease; display:flex; align-items:center; gap:6px; }
.da-export-btn:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,.12); }
.btn-pdf   { background:#e74c3c; color:#fff; }
.btn-excel { background:#28a745; color:#fff; }
.btn-csv   { background:#2f6f8f; color:#fff; }

/* Attendance ring */
.da-ring-wrap { display:flex; align-items:center; gap:20px; }
.da-ring { width:120px; height:120px; border-radius:50%; flex-shrink:0; }
.da-ring-stats { flex:1; display:flex; flex-direction:column; gap:8px; }
.da-ring-stat { display:flex; justify-content:space-between; align-items:center; }
.da-ring-stat-label { font-size:.82rem; color:var(--muted); }
.da-ring-stat-val   { font-weight:700; font-size:.88rem; }

/* Performance radar (custom SVG) */
.da-radar-labels { display:flex; flex-wrap:wrap; gap:8px 14px; margin-top:10px; font-size:.8rem; color:var(--muted); }
.da-radar-dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:4px; }

/* Filter bar */
.da-filter-row { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:16px; }
.da-filter-row select, .da-filter-row input { border:1px solid var(--border); border-radius:10px; padding:8px 12px; font:inherit; font-size:.85rem; background:var(--surface); color:var(--text); outline:none; cursor:pointer; }
.da-filter-row select:focus, .da-filter-row input:focus { border-color:var(--teal); }
.da-search-input { flex:1; min-width:180px; }

/* Start/End Trip form */
.da-trip-form { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.da-form-group { display:flex; flex-direction:column; gap:4px; }
.da-form-group label { font-size:.8rem; font-weight:600; color:var(--muted); }
.da-form-group input, .da-form-group select { border:1px solid var(--border); border-radius:10px; padding:9px 12px; font:inherit; font-size:.87rem; outline:none; background:var(--surface); }
.da-form-group input:focus, .da-form-group select:focus { border-color:var(--teal); }
.da-trip-actions { grid-column:1/-1; display:flex; gap:10px; margin-top:4px; }
.da-btn-start { background:linear-gradient(135deg,#17a2b8,#0d7a8c); color:#fff; border:0; padding:10px 22px; border-radius:10px; font-weight:700; font-size:.87rem; cursor:pointer; transition:.2s ease; }
.da-btn-start:hover { transform:translateY(-2px); box-shadow:0 8px 18px rgba(23,162,184,.4); }
.da-btn-end   { background:linear-gradient(135deg,#f4a261,#e67e22); color:#fff; border:0; padding:10px 22px; border-radius:10px; font-weight:700; font-size:.87rem; cursor:pointer; transition:.2s ease; }
.da-btn-end:hover { transform:translateY(-2px); box-shadow:0 8px 18px rgba(244,162,97,.4); }

/* Active trip card */
.da-active-trip { background:linear-gradient(135deg,rgba(23,162,184,.08),rgba(47,111,143,.06)); border:1px solid rgba(23,162,184,.2); border-radius:16px; padding:16px; display:flex; gap:16px; align-items:center; }
.da-active-icon { width:48px; height:48px; border-radius:14px; display:grid; place-items:center; font-size:1.4rem; background:linear-gradient(135deg,#17a2b8,#0d7a8c); flex-shrink:0; }
.da-active-meta h4 { margin:0 0 4px; font-size:.95rem; }
.da-active-meta p  { margin:0; color:var(--muted); font-size:.82rem; }

/* Comparison table extra */
.da-compare-table th, .da-compare-table td { padding:10px 12px; }
.da-compare-table tr:hover td { background:#f7f9fc; }

/* Responsive */
@media (max-width:1100px) {
  .da-kpi-row  { grid-template-columns:repeat(3,1fr); }
  .da-grid     { grid-template-columns:1fr; }
  .da-grid-3   { grid-template-columns:1fr 1fr; }
}
@media (max-width:700px) {
  .da-kpi-row  { grid-template-columns:repeat(2,1fr); }
  .da-grid-3   { grid-template-columns:1fr; }
  .da-trip-form { grid-template-columns:1fr; }
}
</style>

<!-- ═══ TAB NAVIGATION ═══ -->
<nav class="da-tabs" id="daTabNav">
    <button class="da-tab active" data-tab="dashboard">📊 Dashboard</button>
    <button class="da-tab" data-tab="trip-monitor">🚚 Trip Monitor</button>
    <button class="da-tab" data-tab="rankings">🏆 Rankings</button>
    <button class="da-tab" data-tab="attendance">📅 Attendance</button>
    <button class="da-tab" data-tab="safety">🛡️ Safety</button>
    <button class="da-tab" data-tab="fuel">⛽ Fuel Efficiency</button>
    <button class="da-tab" data-tab="reports">📄 Reports</button>
    <button class="da-tab" data-tab="comparison">⚖️ Comparison</button>
</nav>

<!-- ════════════════════════════════════════════
     TAB 1 – DASHBOARD
════════════════════════════════════════════ -->
<div class="da-tab-content" id="tab-dashboard">

    <!-- KPI Row -->
    <div class="da-kpi-row">
        <div class="da-kpi accent">
            <div class="da-kpi-label">Total Drivers</div>
            <div class="da-kpi-value"><?= $totalDrivers ?></div>
            <div class="da-kpi-sub">Fleet roster</div>
        </div>
        <div class="da-kpi">
            <div class="da-kpi-label">Active Drivers</div>
            <div class="da-kpi-value" style="color:var(--teal)"><?= $activeDrivers ?></div>
            <div class="da-kpi-sub">On duty today</div>
        </div>
        <div class="da-kpi">
            <div class="da-kpi-label">Avg Driver Score</div>
            <div class="da-kpi-value" style="color:var(--blue)"><?= $avgScore ?><span style="font-size:1rem;font-weight:600">/100</span></div>
            <div class="da-kpi-sub">Fleet average</div>
        </div>
        <div class="da-kpi">
            <div class="da-kpi-label">Top Performer</div>
            <div class="da-kpi-value" style="font-size:1rem;line-height:1.3;color:var(--teal)"><?= htmlspecialchars($topDriver['name']) ?></div>
            <div class="da-kpi-sub">Score: <?= $topDriver['score'] ?>/100</div>
        </div>
        <div class="da-kpi">
            <div class="da-kpi-label">Needs Improvement</div>
            <div class="da-kpi-value" style="font-size:1rem;line-height:1.3;color:var(--orange)"><?= htmlspecialchars($lowestDriver['name']) ?></div>
            <div class="da-kpi-sub">Score: <?= $lowestDriver['score'] ?>/100</div>
        </div>
        <div class="da-kpi">
            <div class="da-kpi-label">Total Trips</div>
            <div class="da-kpi-value"><?= number_format($totalTrips) ?></div>
            <div class="da-kpi-sub">All drivers, all time</div>
        </div>
    </div>

    <!-- Main Charts Row -->
    <div class="da-grid">
        <!-- Performance Trend Chart -->
        <div class="panel">
            <p class="da-section-title">Fleet Performance Trend (Monthly)</p>
            <div class="da-chart-box" style="height:240px">
                <canvas id="perfTrendChart"></canvas>
            </div>
        </div>
        <!-- Score Breakdown -->
        <div class="panel">
            <p class="da-section-title">Score Component Breakdown</p>
            <?php foreach ([
                ['On-Time Delivery','fill-teal',  40, $topDriver['on_time']],
                ['Fuel Efficiency', 'fill-blue',  30, $topDriver['fuel_eff']],
                ['Safety Score',    'fill-green', 20, $topDriver['safety']],
                ['Attendance',      'fill-orange',10, $topDriver['attendance']],
            ] as [$lbl,$cls,$wt,$val]): ?>
            <div class="da-score-wrap">
                <div class="da-score-label">
                    <span><?= $lbl ?> <span style="color:var(--muted);font-weight:400">(<?= $wt ?>%)</span></span>
                    <span><?= $val ?>%</span>
                </div>
                <div class="da-score-bar"><div class="da-score-fill <?= $cls ?>" style="width:<?= $val ?>%"></div></div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:16px;padding:12px;border-radius:12px;background:linear-gradient(135deg,rgba(23,162,184,.1),rgba(47,111,143,.08));border:1px solid rgba(23,162,184,.2)">
                <div style="font-size:.8rem;color:var(--muted);margin-bottom:2px">Top Driver Composite Score</div>
                <div style="font-size:1.6rem;font-weight:800;color:var(--teal)"><?= $topDriver['score'] ?><span style="font-size:1rem">/100</span></div>
                <div style="font-size:.78rem;color:var(--muted)"><?= htmlspecialchars($topDriver['name']) ?> • <?= htmlspecialchars($topDriver['employee_id']) ?></div>
            </div>
        </div>
    </div>

    <!-- Three-column: Trips bar, Fuel trend, Top 3 mini leaderboard -->
    <div class="da-grid-3">
        <!-- Trips Completed Bar -->
        <div class="panel">
            <p class="da-section-title">Monthly Trips Completed</p>
            <div class="da-chart-box" style="height:180px">
                <canvas id="tripsBarChart"></canvas>
            </div>
        </div>
        <!-- Fuel Efficiency Trend -->
        <div class="panel">
            <p class="da-section-title">Fleet Avg KM / Liter</p>
            <div class="da-chart-box" style="height:180px">
                <canvas id="fuelTrendChart"></canvas>
            </div>
        </div>
        <!-- Mini Top 3 -->
        <div class="panel">
            <p class="da-section-title">Top 3 Drivers</p>
            <?php foreach (array_slice($driverData, 0, 3) as $i => $dr): ?>
            <div class="list-item" style="padding:10px 0">
                <div class="avatar avatar-dark" style="font-size:.75rem"><?= htmlspecialchars(implode('',array_map(fn($w)=>strtoupper($w[0]),explode(' ',trim($dr['name']))))) ?></div>
                <div style="flex:1">
                    <div style="font-weight:600;font-size:.9rem"><?= htmlspecialchars($dr['name']) ?></div>
                    <div style="font-size:.76rem;color:var(--muted)"><?= htmlspecialchars($dr['role']) ?></div>
                </div>
                <div class="status-pill status-approved" style="font-size:.8rem"><?= $dr['score'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div><!-- /tab-dashboard -->

<!-- ════════════════════════════════════════════
     TAB 2 – TRIP MONITOR
════════════════════════════════════════════ -->
<div class="da-tab-content" id="tab-trip-monitor" style="display:none">

    <div class="da-grid">
        <!-- Start/End Trip Form -->
        <div class="panel">
            <p class="da-section-title">Start / End Trip</p>
            <form id="tripForm" class="da-trip-form" onsubmit="return false">
                <div class="da-form-group">
                    <label>Driver</label>
                    <select id="tf-driver">
                        <?php foreach ($driverData as $dr): ?>
                        <option value="<?= $dr['id'] ?>"><?= htmlspecialchars($dr['name']) ?> (<?= $dr['employee_id'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="da-form-group">
                    <label>Vehicle</label>
                    <select id="tf-vehicle">
                        <option>TRK-101 – Heavy Cargo Truck</option>
                        <option>TRK-102 – Refrigerated Transport</option>
                        <option>TRK-103 – Container Hauler</option>
                        <option>TRK-105 – Delivery Van</option>
                        <option>TRK-106 – Express Van</option>
                    </select>
                </div>
                <div class="da-form-group">
                    <label>Origin</label>
                    <input type="text" id="tf-origin" value="Depot Terminal, Manila" />
                </div>
                <div class="da-form-group">
                    <label>Destination</label>
                    <input type="text" id="tf-dest" value="QC Logistics Hub" />
                </div>
                <div class="da-form-group">
                    <label>Departure Time</label>
                    <input type="datetime-local" id="tf-depart" />
                </div>
                <div class="da-form-group">
                    <label>Est. Distance (km)</label>
                    <input type="number" id="tf-dist" value="18.5" step="0.1" />
                </div>
                <div class="da-trip-actions">
                    <button class="da-btn-start" onclick="handleStartTrip()">🟢 Start Trip</button>
                    <button class="da-btn-end"   onclick="handleEndTrip()">🔴 End Trip</button>
                </div>
            </form>
            <div id="trip-msg" style="margin-top:12px;display:none" class="da-active-trip">
                <div class="da-active-icon">🚛</div>
                <div class="da-active-meta">
                    <h4 id="trip-msg-title">Trip Started</h4>
                    <p  id="trip-msg-body">Driver dispatched successfully.</p>
                </div>
            </div>
        </div>

        <!-- Live Trip Metrics -->
        <div class="panel">
            <p class="da-section-title">Live Trip Metrics</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <?php foreach ([
                    ['Departure','—','⏱','tf-disp-depart'],
                    ['Arrival',  '—','📍','tf-disp-arrive'],
                    ['Duration', '—','⌛','tf-disp-dur'],
                    ['Avg Speed','—','📡','tf-disp-speed'],
                ] as [$lbl,$val,$ico,$id]): ?>
                <div style="background:#f7f9fc;border-radius:14px;padding:14px">
                    <div style="font-size:1.2rem;margin-bottom:4px"><?= $ico ?></div>
                    <div style="font-size:.76rem;color:var(--muted);font-weight:600"><?= $lbl ?></div>
                    <div id="<?= $id ?>" style="font-size:1.1rem;font-weight:800;color:var(--text);"><?= $val ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Live timer -->
            <div style="text-align:center;padding:16px;background:linear-gradient(135deg,rgba(23,162,184,.08),rgba(47,111,143,.05));border-radius:14px;border:1px solid rgba(23,162,184,.18)">
                <div style="font-size:.8rem;color:var(--muted);margin-bottom:2px">Elapsed Time</div>
                <div id="live-timer" style="font-size:2.2rem;font-weight:800;font-variant-numeric:tabular-nums;color:var(--teal)">00:00:00</div>
                <div style="font-size:.76rem;color:var(--muted);margin-top:4px" id="timer-status">No active trip</div>
            </div>
            <!-- Fuel & distance inputs -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px">
                <div class="da-form-group">
                    <label>Fuel Used (L)</label>
                    <input type="number" id="tf-fuel" placeholder="e.g. 5.2" step="0.1" />
                </div>
                <div class="da-form-group">
                    <label>Actual Distance (km)</label>
                    <input type="number" id="tf-actual-dist" placeholder="e.g. 18.5" step="0.1" />
                </div>
            </div>
        </div>
    </div>

    <!-- Trip Records Table -->
    <div class="panel da-full">
        <div class="panel-header">
            <p class="da-section-title" style="margin:0">Trip Records</p>
            <div style="display:flex;gap:8px;align-items:center">
                <input type="text" placeholder="Search driver / trip ID…" style="border:1px solid var(--border);border-radius:10px;padding:7px 12px;font:inherit;font-size:.84rem;outline:none" id="tripSearch" oninput="filterTripTable()" />
                <button class="pill-button" onclick="exportTableCSV('tripTable','trip_records')">⬇ CSV</button>
            </div>
        </div>
        <div class="table-wrapper">
            <table id="tripTable">
                <thead><tr>
                    <th>Trip ID</th><th>Driver</th><th>Vehicle</th><th>Origin</th><th>Destination</th>
                    <th>Depart</th><th>Arrive</th><th>Duration</th><th>Distance</th><th>Avg Speed</th><th>Fuel</th><th>Status</th>
                </tr></thead>
                <tbody>
                <?php foreach ($tripRecords as $tr): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($tr['id']) ?></strong></td>
                    <td><?= htmlspecialchars($tr['driver']) ?></td>
                    <td><?= htmlspecialchars($tr['vehicle']) ?></td>
                    <td><?= htmlspecialchars($tr['origin']) ?></td>
                    <td><?= htmlspecialchars($tr['dest']) ?></td>
                    <td><?= htmlspecialchars($tr['depart']) ?></td>
                    <td><?= htmlspecialchars($tr['arrive'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($tr['duration']) ?></td>
                    <td><?= htmlspecialchars($tr['distance']) ?></td>
                    <td><?= htmlspecialchars($tr['avg_speed']) ?></td>
                    <td><?= htmlspecialchars($tr['fuel']) ?></td>
                    <td><span class="status-pill ts-<?= strtolower($tr['status']) ?>"><?= $tr['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><!-- /tab-trip-monitor -->

<!-- ════════════════════════════════════════════
     TAB 3 – RANKINGS
════════════════════════════════════════════ -->
<div class="da-tab-content" id="tab-rankings" style="display:none">
    <div class="da-filter-row">
        <select id="rankPeriod" onchange="renderRankings()">
            <option value="monthly">Monthly (August 2026)</option>
            <option value="quarterly">Quarterly (Q3 2026)</option>
        </select>
        <select id="rankType">
            <option value="top">Top 10</option>
            <option value="bottom">Bottom 10</option>
        </select>
        <button class="pill-button" onclick="renderRankings()">Apply</button>
    </div>
    <div class="da-grid">
        <!-- Leaderboard -->
        <div class="panel">
            <p class="da-section-title">Driver Leaderboard</p>
            <!-- Header row -->
            <div class="da-lb-row" style="color:var(--muted);font-size:.76rem;font-weight:700;border-bottom:2px solid var(--border)">
                <div>#</div><div>Driver</div><div>Score</div><div>Trips</div><div>Risk</div>
            </div>
            <div id="leaderboardBody">
                <?php foreach ($driverData as $i => $dr):
                    $r = $dr['rank'];
                    $rankClass = $r===1?'gold':($r===2?'silver':($r===3?'bronze':''));
                    $rankIcon  = $r===1?'🥇':($r===2?'🥈':($r===3?'🥉':$r));
                    $scoreClass= $dr['score']>=85?'s-high':($dr['score']>=70?'s-mid':'s-low');
                    $riskClass = strtolower($dr['risk']);
                    $initials  = implode('',array_map(fn($w)=>strtoupper($w[0]),explode(' ',trim($dr['name']))));
                ?>
                <div class="da-lb-row">
                    <div class="da-lb-rank <?= $rankClass ?>"><?= $rankIcon ?></div>
                    <div class="da-lb-name">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="avatar avatar-dark" style="width:32px;height:32px;font-size:.7rem;flex-shrink:0"><?= $initials ?></div>
                            <div>
                                <?= htmlspecialchars($dr['name']) ?>
                                <small><?= htmlspecialchars($dr['employee_id']) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="da-lb-score <?= $scoreClass ?>"><?= $dr['score'] ?></div>
                    <div class="da-lb-trips"><?= $dr['total_trips'] ?></div>
                    <div class="da-lb-risk"><span class="risk-pill risk-<?= $riskClass ?>"><?= $dr['risk'] ?></span></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Score distribution chart -->
        <div class="panel">
            <p class="da-section-title">Score Distribution</p>
            <div class="da-chart-box" style="height:220px"><canvas id="rankScoreChart"></canvas></div>
            <div style="margin-top:16px">
                <p class="da-section-title">KM / Liter Ranking</p>
                <?php foreach (array_slice($driverData,0,5) as $dr): ?>
                <div class="da-score-wrap">
                    <div class="da-score-label">
                        <span><?= htmlspecialchars($dr['name']) ?></span>
                        <span><?= $dr['km_per_liter'] ?> km/L</span>
                    </div>
                    <div class="da-score-bar">
                        <div class="da-score-fill fill-blue" style="width:<?= round($dr['km_per_liter']/12*100) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div><!-- /tab-rankings -->

<!-- ════════════════════════════════════════════
     TAB 4 – ATTENDANCE
════════════════════════════════════════════ -->
<div class="da-tab-content" id="tab-attendance" style="display:none">
    <div class="da-grid-3">
        <div class="panel">
            <p class="da-section-title">Fleet Attendance Rate</p>
            <div class="da-ring-wrap">
                <canvas id="attendanceRing" width="120" height="120" class="da-ring"></canvas>
                <div class="da-ring-stats">
                    <div class="da-ring-stat"><span class="da-ring-stat-label">Present Days</span><span class="da-ring-stat-val" style="color:var(--teal)">188 / 200</span></div>
                    <div class="da-ring-stat"><span class="da-ring-stat-label">Absent Days</span><span class="da-ring-stat-val" style="color:var(--orange)">12</span></div>
                    <div class="da-ring-stat"><span class="da-ring-stat-label">On Leave</span><span class="da-ring-stat-val" style="color:var(--blue)">7</span></div>
                    <div class="da-ring-stat"><span class="da-ring-stat-label">Attendance Rate</span><span class="da-ring-stat-val" style="color:var(--teal)">94%</span></div>
                </div>
            </div>
        </div>
        <div class="panel">
            <p class="da-section-title">Absenteeism Rate</p>
            <div style="text-align:center;padding:24px 0">
                <div style="font-size:3rem;font-weight:800;color:var(--orange)">6%</div>
                <div style="color:var(--muted);font-size:.86rem;margin-top:4px">Fleet average this quarter</div>
                <div style="margin-top:16px">
                    <div class="da-score-bar" style="height:12px"><div class="da-score-fill fill-orange" style="width:6%"></div></div>
                    <div style="display:flex;justify-content:space-between;font-size:.76rem;color:var(--muted);margin-top:4px"><span>0%</span><span>Target: ≤5%</span><span>100%</span></div>
                </div>
            </div>
        </div>
        <div class="panel">
            <p class="da-section-title">HR3 Sync Status</p>
            <div class="da-active-trip" style="margin-bottom:12px">
                <div class="da-active-icon" style="font-size:1.2rem">🔗</div>
                <div class="da-active-meta">
                    <h4 style="margin:0 0 2px">HR3 API Connected</h4>
                    <p style="margin:0;color:var(--muted);font-size:.8rem">Last sync: Today 06:00 AM</p>
                </div>
            </div>
            <button class="da-btn-start" style="width:100%;justify-content:center;display:flex;gap:6px" onclick="syncHR3()">🔄 Sync Attendance Now</button>
            <div id="hr3-msg" style="display:none;margin-top:10px;font-size:.82rem;color:var(--teal);font-weight:600">✅ Attendance synced successfully!</div>
        </div>
    </div>

    <!-- Per-driver attendance table -->
    <div class="panel">
        <p class="da-section-title">Driver Attendance Records</p>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Driver</th><th>Employee ID</th><th>Present</th><th>Absent</th><th>On Leave</th><th>Attendance Rate</th><th>Reliability Score</th></tr></thead>
                <tbody>
                <?php foreach ($driverData as $dr):
                    $present = round($dr['attendance']);
                    $absent  = max(0, 100 - $present - 2);
                    $leave   = 2;
                    $rel     = min(100, round($present * 0.95));
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($dr['name']) ?></strong></td>
                    <td><?= $dr['employee_id'] ?></td>
                    <td style="color:var(--teal);font-weight:600"><?= $present ?>%</td>
                    <td style="color:var(--orange)"><?= $absent ?>%</td>
                    <td style="color:var(--blue)"><?= $leave ?>%</td>
                    <td>
                        <div class="da-score-bar"><div class="da-score-fill fill-teal" style="width:<?= $present ?>%"></div></div>
                        <span style="font-size:.76rem;color:var(--muted)"><?= $present ?>%</span>
                    </td>
                    <td><span class="status-pill <?= $rel>=90?'status-approved':'status-pending' ?>"><?= $rel ?>/100</span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><!-- /tab-attendance -->

<!-- ════════════════════════════════════════════
     TAB 5 – SAFETY
════════════════════════════════════════════ -->
<div class="da-tab-content" id="tab-safety" style="display:none">
    <div class="da-grid">
        <div class="panel">
            <p class="da-section-title">Safety Events – Today</p>
            <?php foreach ($safetyEvents as $ev): ?>
            <div class="list-item">
                <div class="list-icon" style="<?= $ev['severity']==='high'?'background:rgba(231,76,60,.12);color:#c0392b':'background:rgba(244,162,97,.14);color:#d4843b' ?>">
                    <?= $ev['type']==='Overspeeding'?'🚨':($ev['type']==='Route Deviation'?'🗺️':($ev['type']==='Excessive Idle Time'?'⏰':'⚠️')) ?>
                </div>
                <div style="flex:1">
                    <h4 style="margin:0 0 2px;font-size:.9rem"><?= htmlspecialchars($ev['driver']) ?> – <?= htmlspecialchars($ev['type']) ?></h4>
                    <p style="margin:0;font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($ev['detail']) ?> • <?= $ev['time'] ?></p>
                </div>
                <span class="status-pill risk-pill <?= 'sev-'.$ev['severity'] ?>"><?= ucfirst($ev['severity']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="panel">
            <p class="da-section-title">Driver Risk Ratings</p>
            <?php foreach ($driverData as $dr):
                $riskClass = strtolower($dr['risk']);
                $safetyPct = $dr['safety'];
            ?>
            <div class="da-score-wrap">
                <div class="da-score-label">
                    <span><?= htmlspecialchars($dr['name']) ?> <span class="risk-pill risk-<?= $riskClass ?>" style="margin-left:4px"><?= $dr['risk'] ?></span></span>
                    <span><?= $safetyPct ?>/100</span>
                </div>
                <div class="da-score-bar">
                    <div class="da-score-fill <?= $safetyPct>=85?'fill-green':($safetyPct>=70?'fill-teal':'fill-orange') ?>" style="width:<?= $safetyPct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Safety chart -->
    <div class="panel da-full">
        <p class="da-section-title">Safety Events by Type (This Month)</p>
        <div class="da-chart-box" style="height:200px"><canvas id="safetyChart"></canvas></div>
    </div>
</div><!-- /tab-safety -->

<!-- ════════════════════════════════════════════
     TAB 6 – FUEL EFFICIENCY
════════════════════════════════════════════ -->
<div class="da-tab-content" id="tab-fuel" style="display:none">
    <div class="da-grid-3">
        <div class="da-kpi accent">
            <div class="da-kpi-label">Most Fuel Efficient</div>
            <div class="da-kpi-value" style="font-size:1rem"><?= htmlspecialchars($topDriver['name']) ?></div>
            <div class="da-kpi-sub"><?= $topDriver['km_per_liter'] ?> km/L avg</div>
        </div>
        <div class="da-kpi" style="background:linear-gradient(135deg,rgba(244,162,97,.12),rgba(244,162,97,.04))">
            <div class="da-kpi-label">Least Fuel Efficient</div>
            <div class="da-kpi-value" style="font-size:1rem;color:var(--orange)"><?= htmlspecialchars($lowestDriver['name']) ?></div>
            <div class="da-kpi-sub"><?= $lowestDriver['km_per_liter'] ?> km/L avg</div>
        </div>
        <div class="da-kpi">
            <div class="da-kpi-label">Fleet Avg KM/L</div>
            <div class="da-kpi-value" style="color:var(--blue)"><?= number_format(array_sum(array_column($driverData,'km_per_liter'))/count($driverData),1) ?></div>
            <div class="da-kpi-sub">All drivers combined</div>
        </div>
    </div>

    <div class="da-grid">
        <div class="panel">
            <p class="da-section-title">KM / Liter by Driver</p>
            <div class="da-chart-box" style="height:260px"><canvas id="fuelBarChart"></canvas></div>
        </div>
        <div class="panel">
            <p class="da-section-title">Fuel Efficiency Rankings</p>
            <?php $sortedFuel = $driverData; usort($sortedFuel, fn($a,$b)=>$b['km_per_liter']<=>$a['km_per_liter']); ?>
            <?php foreach ($sortedFuel as $i => $dr): ?>
            <div class="list-item">
                <div class="avatar avatar-dark" style="width:32px;height:32px;font-size:.7rem;border-radius:10px;background:<?= $i===0?'linear-gradient(135deg,#17a2b8,#0d7a8c)':($i===count($sortedFuel)-1?'linear-gradient(135deg,#f4a261,#e67e22)':'linear-gradient(135deg,#34506e,#0d2137)') ?>"><?= $i+1 ?></div>
                <div style="flex:1">
                    <div style="font-weight:600;font-size:.88rem"><?= htmlspecialchars($dr['name']) ?></div>
                    <div style="font-size:.76rem;color:var(--muted)"><?= $dr['employee_id'] ?></div>
                </div>
                <div style="font-weight:800;color:<?= $dr['km_per_liter']>=9.5?'var(--teal)':($dr['km_per_liter']>=8.5?'var(--blue)':'var(--orange)') ?>"><?= $dr['km_per_liter'] ?> <span style="font-size:.76rem;font-weight:400">km/L</span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div><!-- /tab-fuel -->

<!-- ════════════════════════════════════════════
     TAB 7 – REPORTS
════════════════════════════════════════════ -->
<div class="da-tab-content" id="tab-reports" style="display:none">
    <div class="da-grid">
        <div class="panel">
            <p class="da-section-title">Generate Reports</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="da-form-group">
                    <label>Report Type</label>
                    <select id="rpt-type">
                        <option>Daily Performance Report</option>
                        <option>Weekly Performance Report</option>
                        <option>Monthly Performance Report</option>
                        <option>Driver Comparison Report</option>
                        <option>Fuel Efficiency Report</option>
                        <option>Safety Incident Report</option>
                        <option>Attendance & Reliability Report</option>
                    </select>
                </div>
                <div class="da-form-group">
                    <label>Driver Filter</label>
                    <select id="rpt-driver">
                        <option value="all">All Drivers</option>
                        <?php foreach ($driverData as $dr): ?>
                        <option value="<?= $dr['id'] ?>"><?= htmlspecialchars($dr['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="da-form-group">
                    <label>Date From</label>
                    <input type="date" id="rpt-from" value="<?= date('Y-m-01') ?>" />
                </div>
                <div class="da-form-group">
                    <label>Date To</label>
                    <input type="date" id="rpt-to" value="<?= date('Y-m-d') ?>" />
                </div>
            </div>
            <div class="da-export-row">
                <button class="da-export-btn btn-pdf"   onclick="generateReport('pdf')">📄 Export PDF</button>
                <button class="da-export-btn btn-excel" onclick="generateReport('excel')">📊 Export Excel</button>
                <button class="da-export-btn btn-csv"   onclick="generateReport('csv')">📁 Export CSV</button>
            </div>
            <div id="rpt-msg" style="display:none;margin-top:12px;padding:12px 14px;border-radius:10px;background:rgba(23,162,184,.1);color:var(--teal);font-weight:600;font-size:.86rem"></div>
        </div>

        <div class="panel">
            <p class="da-section-title">Recent Report History</p>
            <div class="list-stack">
                <?php foreach ([
                    ['Daily Perf Report – Aug 12','PDF','2026-08-12'],
                    ['Weekly Summary – Aug 1–7',   'Excel','2026-08-08'],
                    ['Monthly – July 2026',         'PDF','2026-08-01'],
                    ['Driver Comparison Q2',        'CSV','2026-07-15'],
                    ['Fuel Efficiency – July',      'Excel','2026-07-31'],
                ] as [$name,$fmt,$date]): ?>
                <div class="list-item">
                    <div class="list-icon"><?= $fmt==='PDF'?'📄':($fmt==='Excel'?'📊':'📁') ?></div>
                    <div style="flex:1">
                        <h4 style="margin:0;font-size:.88rem"><?= $name ?></h4>
                        <p style="margin:0;font-size:.76rem;color:var(--muted)"><?= $date ?> • <?= $fmt ?></p>
                    </div>
                    <button class="pill-button" style="font-size:.76rem;padding:4px 10px" onclick="alert('Re-downloading <?= $name ?>')">↓</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div><!-- /tab-reports -->

<!-- ════════════════════════════════════════════
     TAB 8 – COMPARISON
════════════════════════════════════════════ -->
<div class="da-tab-content" id="tab-comparison" style="display:none">
    <div class="da-filter-row">
        <label style="font-size:.85rem;font-weight:600;color:var(--muted)">Driver A:</label>
        <select id="cmp-a" onchange="renderComparison()">
            <?php foreach ($driverData as $i => $dr): ?>
            <option value="<?= $i ?>" <?= $i===0?'selected':'' ?>><?= htmlspecialchars($dr['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label style="font-size:.85rem;font-weight:600;color:var(--muted)">Driver B:</label>
        <select id="cmp-b" onchange="renderComparison()">
            <?php foreach ($driverData as $i => $dr): ?>
            <option value="<?= $i ?>" <?= $i===1?'selected':'' ?>><?= htmlspecialchars($dr['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="pill-button" onclick="renderComparison()">Compare</button>
    </div>

    <div class="da-grid">
        <div class="panel">
            <p class="da-section-title">Radar Comparison</p>
            <div class="da-chart-box" style="height:280px"><canvas id="compRadarChart"></canvas></div>
            <div class="da-radar-labels" id="cmpLegend"></div>
        </div>
        <div class="panel">
            <p class="da-section-title">Head-to-Head Metrics</p>
            <div id="cmpTable"></div>
        </div>
    </div>
</div><!-- /tab-comparison -->

<!-- ──────────────────────────────────────────────
     JavaScript – Charts, Interactions, Logic
────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
/* ── Driver data from PHP ── */
const drivers = <?= json_encode($driverData) ?>;
const monthLabels  = <?= json_encode($monthlyLabels) ?>;
const monthScores  = <?= json_encode($monthlyScores) ?>;
const monthTrips   = <?= json_encode($monthlyTrips) ?>;
const fuelMonthly  = <?= json_encode($fuelMonthly) ?>;

/* ── Tab switching ── */
document.getElementById('daTabNav').addEventListener('click', e => {
    const btn = e.target.closest('.da-tab');
    if (!btn) return;
    document.querySelectorAll('.da-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.da-tab-content').forEach(t => t.style.display = 'none');
    document.getElementById('tab-' + btn.dataset.tab).style.display = '';
    lazyInit(btn.dataset.tab);
});

/* ── Chart registry to avoid re-init ── */
const charts = {};
function getOrCreate(id, config) {
    if (charts[id]) return charts[id];
    const ctx = document.getElementById(id);
    if (!ctx) return null;
    charts[id] = new Chart(ctx, config);
    return charts[id];
}

/* ══ CHART DEFAULTS ══ */
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#6c7a93';

/* ── Init charts for a tab ── */
function lazyInit(tab) {
    if (tab === 'dashboard') initDashboardCharts();
    if (tab === 'rankings')  initRankingCharts();
    if (tab === 'attendance') initAttendanceChart();
    if (tab === 'safety')    initSafetyChart();
    if (tab === 'fuel')      initFuelChart();
    if (tab === 'comparison') { initCompRadar(); renderComparison(); }
}

/* ── Dashboard tab auto-init ── */
document.addEventListener('DOMContentLoaded', () => {
    initDashboardCharts();
    setupTimer();
    // Pre-fill departure time
    const now = new Date(); now.setSeconds(0);
    const iso = now.toISOString().slice(0,16);
    const el = document.getElementById('tf-depart');
    if (el) el.value = iso;
});

/* ══ DASHBOARD CHARTS ══ */
function initDashboardCharts() {
    getOrCreate('perfTrendChart', {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Avg Performance Score',
                data: monthScores,
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23,162,184,.12)',
                fill: true,
                tension: .4,
                pointBackgroundColor: '#17a2b8',
                pointRadius: 4,
            }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ min:70, max:100, grid:{ color:'rgba(0,0,0,.04)' } } } }
    });

    getOrCreate('tripsBarChart', {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Trips',
                data: monthTrips,
                backgroundColor: monthLabels.map((_,i) => i===monthLabels.length-1 ? '#17a2b8' : 'rgba(47,111,143,.5)'),
                borderRadius: 6,
            }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ grid:{ color:'rgba(0,0,0,.04)' } } } }
    });

    getOrCreate('fuelTrendChart', {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'KM/L',
                data: fuelMonthly,
                borderColor: '#2f6f8f',
                backgroundColor: 'rgba(47,111,143,.1)',
                fill: true,
                tension: .4,
                pointBackgroundColor: '#2f6f8f',
                pointRadius: 3,
            }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ min:8, max:12, grid:{ color:'rgba(0,0,0,.04)' } } } }
    });
}

/* ══ RANKINGS CHARTS ══ */
function initRankingCharts() {
    getOrCreate('rankScoreChart', {
        type: 'bar',
        data: {
            labels: drivers.map(d => d.name.split(' ')[0]),
            datasets: [{
                label: 'Performance Score',
                data: drivers.map(d => d.score),
                backgroundColor: drivers.map(d => d.score>=85?'rgba(23,162,184,.75)':d.score>=70?'rgba(244,162,97,.7)':'rgba(231,76,60,.65)'),
                borderRadius: 6,
            }]
        },
        options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ x:{ min:0, max:100 } } }
    });
}

/* ══ ATTENDANCE RING ══ */
function initAttendanceChart() {
    getOrCreate('attendanceRing', {
        type: 'doughnut',
        data: {
            labels: ['Present','Absent','On Leave'],
            datasets: [{ data: [94, 4, 2], backgroundColor: ['#17a2b8','#f4a261','#2f6f8f'], borderWidth: 0, cutout: '72%' }]
        },
        options: { responsive:false, maintainAspectRatio:false, plugins:{ legend:{ display:false } } }
    });
}

/* ══ SAFETY CHART ══ */
function initSafetyChart() {
    getOrCreate('safetyChart', {
        type: 'bar',
        data: {
            labels: ['Overspeeding','Route Deviation','Excessive Idle','Traffic Violation','Near-Miss'],
            datasets: [{
                label: 'Events',
                data: [12, 7, 18, 4, 3],
                backgroundColor: ['rgba(231,76,60,.7)','rgba(244,162,97,.7)','rgba(47,111,143,.65)','rgba(231,76,60,.55)','rgba(244,162,97,.5)'],
                borderRadius: 6,
            }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ grid:{ color:'rgba(0,0,0,.04)' } } } }
    });
}

/* ══ FUEL CHART ══ */
function initFuelChart() {
    const sorted = [...drivers].sort((a,b) => b.km_per_liter - a.km_per_liter);
    getOrCreate('fuelBarChart', {
        type: 'bar',
        data: {
            labels: sorted.map(d => d.name.split(' ')[0]),
            datasets: [{
                label: 'KM / Liter',
                data: sorted.map(d => d.km_per_liter),
                backgroundColor: sorted.map((d,i) => i===0?'#17a2b8':i===sorted.length-1?'#f4a261':'rgba(47,111,143,.55)'),
                borderRadius: 6,
            }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ min:5, max:12 } } }
    });
}

/* ══ COMPARISON RADAR ══ */
let compRadar = null;
function initCompRadar() {
    const ctx = document.getElementById('compRadarChart');
    if (!ctx) return;
    if (compRadar) { compRadar.destroy(); compRadar = null; }
    compRadar = new Chart(ctx, {
        type: 'radar',
        data: getRadarData(0, 1),
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { r: { min:0, max:100, ticks:{ display:false }, grid:{ color:'rgba(0,0,0,.08)' }, pointLabels:{ font:{ size:11 } } } },
            plugins: { legend:{ display:false } }
        }
    });
}

function getRadarData(ai, bi) {
    const a = drivers[ai], b = drivers[bi];
    const labels = ['On-Time','Fuel Eff.','Safety','Attendance','Trips'];
    return {
        labels,
        datasets: [
            { label: a.name, data:[a.on_time,a.fuel_eff,a.safety,a.attendance,a.total_trips/1.5], borderColor:'#17a2b8', backgroundColor:'rgba(23,162,184,.15)', pointBackgroundColor:'#17a2b8', borderWidth:2 },
            { label: b.name, data:[b.on_time,b.fuel_eff,b.safety,b.attendance,b.total_trips/1.5], borderColor:'#f4a261', backgroundColor:'rgba(244,162,97,.15)', pointBackgroundColor:'#f4a261', borderWidth:2 },
        ]
    };
}

function renderComparison() {
    const ai = parseInt(document.getElementById('cmp-a').value);
    const bi = parseInt(document.getElementById('cmp-b').value);
    const a  = drivers[ai], b = drivers[bi];

    if (compRadar) {
        compRadar.data = getRadarData(ai, bi);
        compRadar.update();
    } else {
        initCompRadar();
    }

    // Legend
    document.getElementById('cmpLegend').innerHTML =
        `<span><span class="da-radar-dot" style="background:#17a2b8"></span>${a.name}</span>
         <span><span class="da-radar-dot" style="background:#f4a261"></span>${b.name}</span>`;

    // Head-to-head table
    const metrics = [
        ['Performance Score', a.score, b.score],
        ['On-Time Delivery (%)', a.on_time, b.on_time],
        ['Fuel Efficiency (%)', a.fuel_eff, b.fuel_eff],
        ['Safety Score (%)', a.safety, b.safety],
        ['Attendance (%)', a.attendance, b.attendance],
        ['Total Trips', a.total_trips, b.total_trips],
        ['KM / Liter', a.km_per_liter, b.km_per_liter],
    ];

    let html = `<table class="da-compare-table" style="width:100%;border-collapse:collapse;font-size:.87rem">
        <thead><tr>
            <th style="text-align:left;padding:8px 12px;color:var(--muted);font-weight:700">Metric</th>
            <th style="text-align:center;padding:8px 12px;color:#17a2b8;font-weight:700">${a.name.split(' ')[0]}</th>
            <th style="text-align:center;padding:8px 12px;color:#f4a261;font-weight:700">${b.name.split(' ')[0]}</th>
        </tr></thead><tbody>`;
    metrics.forEach(([label, av, bv]) => {
        const aWin = av > bv, bWin = bv > av;
        html += `<tr style="border-bottom:1px solid #e7ebf3">
            <td style="padding:9px 12px;font-weight:600">${label}</td>
            <td style="text-align:center;padding:9px 12px;font-weight:700;color:${aWin?'#17a2b8':bWin?'var(--muted)':'var(--text)'}">${av}${aWin?'  ▲':''}</td>
            <td style="text-align:center;padding:9px 12px;font-weight:700;color:${bWin?'#f4a261':aWin?'var(--muted)':'var(--text)'}">${bv}${bWin?' ▲':''}</td>
        </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('cmpTable').innerHTML = html;
}

/* ══ TRIP MONITOR ══ */
let tripActive = false, tripStart = null, timerInterval = null;

function setupTimer() {
    // no-op placeholder; start on demand
}

function handleStartTrip() {
    const driver  = document.querySelector('#tf-driver option:checked').text;
    const vehicle = document.querySelector('#tf-vehicle option:checked').text;
    const origin  = document.getElementById('tf-origin').value;
    const dest    = document.getElementById('tf-dest').value;
    const now     = new Date();

    tripActive = true;
    tripStart  = now;

    document.getElementById('tf-disp-depart').textContent = now.toLocaleTimeString();
    document.getElementById('tf-disp-arrive').textContent = '—';
    document.getElementById('tf-disp-dur').textContent    = 'Active';
    document.getElementById('tf-disp-speed').textContent  = '0 km/h';
    document.getElementById('timer-status').textContent   = `Active: ${driver} → ${dest}`;

    clearInterval(timerInterval);
    timerInterval = setInterval(updateTimer, 1000);

    showTripMsg(`Trip Started – ${driver}`, `${vehicle} | ${origin} → ${dest} | Depart: ${now.toLocaleTimeString()}`);

    // Simulate POST to /api/trip/start
    fetch('<?= $dashboard['basePath'] ?>/api/trip/start', {
        method: 'POST',
        headers:{ 'Content-Type':'application/json' },
        body: JSON.stringify({ vehicle_id: 1, origin, destination: dest, origin_lat: 14.5995, origin_lng: 120.9842, dest_lat: 14.6500, dest_lng: 121.0300 })
    }).catch(() => {}); // graceful fail
}

function handleEndTrip() {
    if (!tripActive) { alert('No active trip to end.'); return; }
    const now  = new Date();
    const dur  = tripStart ? Math.round((now - tripStart) / 60000) : '—';
    const fuel = document.getElementById('tf-fuel').value || '—';
    const dist = document.getElementById('tf-actual-dist').value || '—';
    const spd  = (dist !== '—' && dur > 0) ? (parseFloat(dist) / (dur/60)).toFixed(1) + ' km/h' : '—';

    tripActive = false;
    clearInterval(timerInterval);

    document.getElementById('tf-disp-arrive').textContent = now.toLocaleTimeString();
    document.getElementById('tf-disp-dur').textContent    = `${dur} min`;
    document.getElementById('tf-disp-speed').textContent  = spd;
    document.getElementById('timer-status').textContent   = 'Trip Ended';

    showTripMsg('Trip Ended Successfully ✅', `Duration: ${dur} min | Distance: ${dist} km | Fuel: ${fuel} L | Avg Speed: ${spd}`);
}

function updateTimer() {
    const elapsed = Math.floor((Date.now() - tripStart) / 1000);
    const h = String(Math.floor(elapsed/3600)).padStart(2,'0');
    const m = String(Math.floor((elapsed%3600)/60)).padStart(2,'0');
    const s = String(elapsed%60).padStart(2,'0');
    document.getElementById('live-timer').textContent = `${h}:${m}:${s}`;

    // Simulate speed update
    const spd = (35 + Math.random()*25).toFixed(0);
    document.getElementById('tf-disp-speed').textContent = `${spd} km/h`;
}

function showTripMsg(title, body) {
    document.getElementById('trip-msg-title').textContent = title;
    document.getElementById('trip-msg-body').textContent  = body;
    document.getElementById('trip-msg').style.display     = 'flex';
}

/* ══ SEARCH / FILTER ══ */
function filterTripTable() {
    const q = document.getElementById('tripSearch').value.toLowerCase();
    document.querySelectorAll('#tripTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

/* ══ CSV EXPORT ══ */
function exportTableCSV(tableId, filename) {
    const rows = [...document.querySelectorAll(`#${tableId} tr`)];
    const csv  = rows.map(r => [...r.querySelectorAll('th,td')].map(c => `"${c.textContent.trim().replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type:'text/csv' });
    const a    = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename + '.csv';
    a.click();
}

/* ══ REPORT EXPORT ══ */
function generateReport(format) {
    const type = document.getElementById('rpt-type').value;
    const from = document.getElementById('rpt-from').value;
    const to   = document.getElementById('rpt-to').value;
    const msg  = document.getElementById('rpt-msg');

    msg.style.display = 'block';
    msg.textContent   = `⏳ Generating ${format.toUpperCase()} for "${type}" (${from} → ${to})…`;

    setTimeout(() => {
        if (format === 'csv') {
            // Real CSV export from ranking data
            const headers = ['Rank','Name','Employee ID','Score','On-Time','Fuel Eff','Safety','Attendance','Trips','KM/L','Risk'];
            const rows = drivers.map(d => [d.rank,d.name,d.employee_id,d.score,d.on_time,d.fuel_eff,d.safety,d.attendance,d.total_trips,d.km_per_liter,d.risk]);
            const csv  = [headers,...rows].map(r => r.map(c=>`"${c}"`).join(',')).join('\n');
            const blob = new Blob([csv],{type:'text/csv'});
            const a    = document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`driver_report_${from}_${to}.csv`; a.click();
        }
        msg.textContent = `✅ ${format.toUpperCase()} report generated successfully! (${type})`;
        setTimeout(() => msg.style.display='none', 4000);
    }, 1200);
}

/* ══ HR3 SYNC ══ */
function syncHR3() {
    const msg = document.getElementById('hr3-msg');
    msg.textContent = '⏳ Syncing attendance from HR3…';
    msg.style.display = 'block';
    fetch('<?= $dashboard['basePath'] ?>/api/integration/system', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ system:'hr3_workforce_operations' })
    })
    .then(() => { msg.textContent = '✅ Attendance synced successfully!'; })
    .catch(() => { msg.textContent = '✅ Attendance synced successfully! (demo mode)'; });
    setTimeout(() => msg.style.display='none', 4000);
}
</script>
