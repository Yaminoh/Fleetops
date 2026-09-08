<?php
$finance = $dashboard['finance'];
$cards = $finance['cards'];
$trend = $finance['trend'];
$breakdown = $finance['breakdown'];
?>

<style>
.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
.card { background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.card-title { font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0; }
.stat-value { font-size: 2rem; font-weight: 700; color: #111827; margin: 0.5rem 0; }
.stat-desc { font-size: 0.875rem; color: #6b7280; }
.text-green { color: #10b981; }
.text-red { color: #ef4444; }
.chart-container { position: relative; height: 300px; width: 100%; flex-grow: 1; }
</style>

<section class="panel" style="background: transparent; border: none; padding: 0;">
    <div class="panel-header" style="margin-bottom: 1.5rem;">
        <div>
            <p class="eyebrow">Financial Overview</p>
            <h3>Cost Analytics</h3>
        </div>
        <button class="pill-button" type="button" onclick="openExpenseModal()">Log Expense</button>
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

    <div class="dashboard-grid">
        <?php foreach ($cards as $card): ?>
            <div class="card">
                <h3 class="card-title"><?= htmlspecialchars($card['title']) ?></h3>
                <div class="stat-value">₱<?= number_format((float) $card['value'], 0) ?></div>
                <?php if (isset($card['change'])): ?>
                    <div class="stat-desc">
                        <span class="<?= (float) $card['change'] < 0 ? 'text-green' : 'text-red' ?>">
                            <?= (float) $card['change'] < 0 ? '↓' : '↑' ?> <?= abs((float) $card['change']) ?>%
                        </span>
                        <?= htmlspecialchars($card['meta']) ?>
                    </div>
                <?php else: ?>
                    <div class="stat-desc"><?= htmlspecialchars($card['meta']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Monthly Expenses Trend</h3>
                <span style="font-size:0.8rem;color:var(--muted);">Last 6 months, live</span>
            </div>
            <div class="chart-container">
                <canvas id="expensesChart"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cost Breakdown</h3>
                <span style="font-size:0.8rem;color:var(--muted);">This month</span>
            </div>
            <div class="chart-container">
                <canvas id="breakdownChart"></canvas>
            </div>
        </div>
    </div>
</section>

<!-- Modal: Log Expense (Maintenance) -->
<div id="expense-modal" class="modal-backdrop" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Log Maintenance Expense</h3>
            <button class="close-btn" type="button" onclick="closeExpenseModal()">✕</button>
        </div>
        <form method="POST" action="<?= route('maintenance.store') ?>">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="expense-vehicle">Vehicle</label>
                <select class="form-control" id="expense-vehicle" name="vehicle_id" required>
                    <?php foreach ($dashboard['vehicleOptions'] as $vehicle): ?>
                        <option value="<?= (int) $vehicle->id ?>"><?= htmlspecialchars($vehicle->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="expense-description">Description</label>
                <input class="form-control" type="text" id="expense-description" name="description" required maxlength="150" placeholder="e.g. Oil change & filter">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="expense-cost">Cost (₱)</label>
                <input class="form-control" type="number" id="expense-cost" name="cost" required min="0" step="0.01">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label for="expense-date">Date serviced</label>
                <input class="form-control" type="date" id="expense-date" name="serviced_at" required max="<?= now()->toDateString() ?>" value="<?= now()->toDateString() ?>">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Save Expense</button>
        </form>
    </div>
</div>

<script>
function openExpenseModal() {
    document.getElementById('expense-modal').style.display = 'flex';
}
function closeExpenseModal() {
    document.getElementById('expense-modal').style.display = 'none';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trendLabels = <?= json_encode($trend['labels'] ?? []) ?>;
    const trendValues = <?= json_encode($trend['values'] ?? []) ?>;
    const breakdownLabels = <?= json_encode($breakdown['labels'] ?? []) ?>;
    const breakdownValues = <?= json_encode($breakdown['values'] ?? []) ?>;
    const breakdownColors = <?= json_encode($breakdown['colors'] ?? []) ?>;

    const ctxExpenses = document.getElementById('expensesChart');
    if (ctxExpenses) {
        const minTrend = trendValues.length ? Math.min(...trendValues) : 0;
        new Chart(ctxExpenses.getContext('2d'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Total Cost (₱)',
                    data: trendValues,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: false, suggestedMin: minTrend * 0.9 } }
            }
        });
    }

    const ctxBreakdown = document.getElementById('breakdownChart');
    if (ctxBreakdown) {
        new Chart(ctxBreakdown.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: breakdownLabels,
                datasets: [{
                    data: breakdownValues,
                    backgroundColor: breakdownColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
