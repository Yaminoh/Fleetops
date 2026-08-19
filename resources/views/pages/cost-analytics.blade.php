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
        <button class="pill-button">Download Report</button>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h3 class="card-title">Total Transport Cost</h3>
            <div class="stat-value">₱84,320</div>
            <div class="stat-desc"><span class="text-green">↓ 2.3%</span> vs last month</div>
        </div>
        <div class="card">
            <h3 class="card-title">Fuel Expenses</h3>
            <div class="stat-value">₱45,100</div>
            <div class="stat-desc"><span class="text-red">↑ 1.1%</span> vs last month</div>
        </div>
        <div class="card">
            <h3 class="card-title">Maintenance Costs</h3>
            <div class="stat-value">₱12,850</div>
            <div class="stat-desc"><span class="text-green">↓ 5.4%</span> vs last month</div>
        </div>
        <div class="card">
            <h3 class="card-title">Projected Savings</h3>
            <div class="stat-value">₱22,400</div>
            <div class="stat-desc">Next quarter optimization</div>
        </div>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Monthly Expenses Trend</h3>
                <select class="pill-button" style="padding: 0.25rem 0.5rem; border: 1px solid #e5e7eb; border-radius: 4px; background: white;"><option>Last 6 Months</option><option>This Year</option></select>
            </div>
            <div class="chart-container">
                <canvas id="expensesChart"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cost Breakdown</h3>
            </div>
            <div class="chart-container">
                <canvas id="breakdownChart"></canvas>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Expenses Trend Chart
    const ctxExpenses = document.getElementById('expensesChart');
    if (ctxExpenses) {
        new Chart(ctxExpenses.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Total Cost (₱)',
                    data: [92000, 89500, 88000, 91000, 86200, 84320],
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
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: false, suggestedMin: 80000 }
                }
            }
        });
    }

    // Cost Breakdown Chart
    const ctxBreakdown = document.getElementById('breakdownChart');
    if (ctxBreakdown) {
        new Chart(ctxBreakdown.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Fuel', 'Maintenance', 'Salaries', 'Insurance', 'Other'],
                datasets: [{
                    data: [45100, 12850, 20500, 3500, 2370],
                    backgroundColor: [
                        '#ef4444', // Fuel
                        '#f59e0b', // Maintenance
                        '#3b82f6', // Salaries
                        '#10b981', // Insurance
                        '#6b7280'  // Other
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
