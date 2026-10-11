<?php
$fuelLogs = $dashboard['fuelLogs'] ?? [];
$fuelCount = count($fuelLogs);
$totalLiters = array_sum(array_map(fn ($log) => (float) preg_replace('/[^0-9.]/', '', $log['liters'] ?? '0'), $fuelLogs));
$totalCost = array_sum(array_map(fn ($log) => (float) preg_replace('/[^0-9.]/', '', $log['cost'] ?? '0'), $fuelLogs));
$averagePrice = $totalLiters > 0 ? $totalCost / $totalLiters : 0;
?>
<section class="panel fuel-page">
    <div class="panel-header">
        <div>
            <p class="eyebrow">Fleet expenses</p>
            <h3>Fuel Logs</h3>
            <p class="fuel-intro"><?= $dashboard['isAdmin'] ? 'Record refuels and keep an eye on fuel volume and spend.' : 'Review fuel records, fuel levels, and proof photos.' ?></p>
        </div>
        <?php if ($dashboard['isAdmin']): ?><button class="pill-button" type="button" onclick="openFuelModal()">+ Log fuel</button><?php endif; ?>
    </div>

    <?php if (session('status')): ?>
        <div class="alert-banner status-approved" role="status" style="padding:10px 14px;border-radius:12px;margin-bottom:14px;font-weight:600;">
            <?= htmlspecialchars(session('status'), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <?php if ($errors->any()): ?>
        <div class="alert-banner" role="alert" style="padding:10px 14px;border-radius:12px;margin-bottom:14px;font-weight:600;background:rgba(231,76,60,0.12);color:#e74c3c;">
            <?= htmlspecialchars($errors->first(), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="fuel-summary" aria-label="Summary of the latest fuel records">
        <article class="fuel-stat"><span>Records loaded</span><strong id="fuel-count"><?= $fuelCount ?></strong><small>Latest <?= $fuelCount ?> records</small></article>
        <article class="fuel-stat"><span>Fuel volume</span><strong id="fuel-liters"><?= number_format($totalLiters, 1) ?> L</strong><small>Across loaded records</small></article>
        <article class="fuel-stat"><span>Total recorded cost</span><strong id="fuel-cost">&#8369;<?= number_format($totalCost, 2) ?></strong><small>Across loaded records</small></article>
        <article class="fuel-stat"><span>Average price</span><strong id="fuel-average"><?= $totalLiters > 0 ? '&#8369;'.number_format($averagePrice, 2) : '—' ?></strong><small>Cost per liter</small></article>
    </div>

    <div class="fuel-log-panel">
        <div class="fuel-table-heading">
            <div><h4>Recent refuels</h4><p>Showing up to 30 most recent entries.</p></div>
            <label class="fuel-search-wrap" for="fuel-search">
                <input id="fuel-search" type="search" placeholder="Search driver, plate, or date" autocomplete="off">
            </label>
        </div>
        <div class="table-wrapper">
            <table id="fuel-log-table">
                <thead>
                    <tr><th>Driver</th><th>Vehicle plate no.</th><th>Date</th><th>Fuel before</th><th>Fuel after</th><th>Proof</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($fuelLogs as $log): ?>
                        <?php
                        $litersValue = (float) preg_replace('/[^0-9.]/', '', $log['liters'] ?? '0');
                        $costValue = (float) preg_replace('/[^0-9.]/', '', $log['cost'] ?? '0');
                        ?>
                        <tr class="fuel-log-row" data-search="<?= htmlspecialchars(strtolower(($log['driver'] ?? '').' '.($log['plate_number'] ?? '').' '.($log['vehicle'] ?? '').' '.($log['logged_at'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" data-liters="<?= $litersValue ?>" data-cost="<?= $costValue ?>">
                            <td><strong><?= htmlspecialchars($log['driver'] ?? 'Unknown driver', ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($log['plate_number'] ?? 'Unknown plate', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($log['logged_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= isset($log['fuel_level_before']) ? number_format((float) $log['fuel_level_before'], 1).'%' : '—' ?></td>
                            <td><?= isset($log['fuel_level_after']) ? number_format((float) $log['fuel_level_after'], 1).'%' : '—' ?></td>
                            <td><?php if (!empty($log['receipt_image'])): ?><a href="<?= route('fuel-logs.proof', $log['id']) ?>" target="_blank" rel="noopener">View photo</a><?php else: ?>No photo<?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($fuelCount === 0): ?>
                        <tr id="fuel-empty-row"><td colspan="6" class="fuel-empty">No fuel records yet. Select "Log fuel" to record the first refuel.</td></tr>
                    <?php endif; ?>
                    <tr id="fuel-no-results" hidden><td colspan="6" class="fuel-empty">No records match your search.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php if ($dashboard['isAdmin']): ?><div id="fuel-modal" class="modal-backdrop" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="fuel-modal-title">
    <div class="modal-card">
        <div class="modal-header">
            <div><p class="eyebrow">New transaction</p><h3 id="fuel-modal-title">Log a refuel</h3></div>
            <button class="close-btn" type="button" aria-label="Close form" onclick="closeFuelModal()">×</button>
        </div>
        <p class="fuel-modal-copy">Enter the fuel amount and the total paid on the receipt.</p>
        <form method="POST" action="<?= route('fuel-logs.store') ?>" id="fuel-form" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-group fuel-field">
                <label for="fuel-vehicle">Vehicle</label>
                <select class="form-control" id="fuel-vehicle" name="vehicle_id" required>
                    <option value="">Choose a vehicle</option>
                    <?php foreach ($dashboard['vehicleOptions'] ?? [] as $vehicle): ?>
                        <option value="<?= (int) $vehicle->id ?>" data-fuel-level="<?= htmlspecialchars((string) $vehicle->fuel_level, ENT_QUOTES, 'UTF-8') ?>" <?= (string) old('vehicle_id') === (string) $vehicle->id ? 'selected' : '' ?>><?= htmlspecialchars($vehicle->plate_number.' — '. $vehicle->name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($dashboard['vehicleOptions'])): ?><small class="fuel-help">Add a vehicle before recording fuel.</small><?php endif; ?>
            </div>
            <div class="form-group fuel-field">
                <label for="fuel-driver">Driver</label>
                <select class="form-control" id="fuel-driver" name="driver_id" required>
                    <option value="">Choose a driver</option>
                    <?php foreach ($dashboard['driverOptions'] ?? [] as $driver): ?>
                        <option value="<?= (int) $driver->id ?>" <?= (string) old('driver_id') === (string) $driver->id ? 'selected' : '' ?>><?= htmlspecialchars($driver->user->name ?? $driver->name ?? 'Driver', ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($dashboard['driverOptions'])): ?><small class="fuel-help">Add a driver before recording fuel.</small><?php endif; ?>
            </div>
            <div class="fuel-input-grid">
                <div class="form-group fuel-field">
                    <label for="fuel-level-before">Fuel level before (%)</label>
                    <input class="form-control" type="number" id="fuel-level-before" name="fuel_level_before" min="0" max="100" step="0.1" value="<?= htmlspecialchars((string) old('fuel_level_before', ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group fuel-field">
                    <label for="fuel-level-after">Fuel level after (%)</label>
                    <input class="form-control" type="number" id="fuel-level-after" name="fuel_level_after" min="0" max="100" step="0.1" value="<?= htmlspecialchars((string) old('fuel_level_after', ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>
            <div class="form-group fuel-field">
                <label for="fuel-proof">Fuel receipt / refuel photo (JPG, PNG, or WebP; max 5 MB)</label>
                <input class="form-control" type="file" id="fuel-proof" name="receipt_image" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <div class="fuel-input-grid">
                <div class="form-group fuel-field">
                    <label for="fuel-liters-input">Fuel added (liters)</label>
                    <input class="form-control" type="number" id="fuel-liters-input" name="liters" min="0.1" max="2000" step="0.1" inputmode="decimal" placeholder="e.g. 45.5" value="<?= htmlspecialchars((string) old('liters', ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="form-group fuel-field">
                    <label for="fuel-cost-input">Total paid (PHP)</label>
                    <input class="form-control" type="number" id="fuel-cost-input" name="cost" min="0" max="1000000" step="0.01" inputmode="decimal" placeholder="e.g. 3200.00" value="<?= htmlspecialchars((string) old('cost', ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>
            <div class="fuel-rate-preview" id="fuel-rate-preview" aria-live="polite">Enter liters and total paid to estimate the price per liter.</div>
            <div class="form-group fuel-field">
                <label for="fuel-date">Refuel date</label>
                <input class="form-control" type="date" id="fuel-date" name="logged_at" required max="<?= now()->toDateString() ?>" value="<?= htmlspecialchars((string) old('logged_at', now()->toDateString()), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="fuel-form-actions">
                <button type="button" class="btn-secondary" onclick="closeFuelModal()">Cancel</button>
                <button type="submit" class="btn-primary" <?= empty($dashboard['vehicleOptions']) || empty($dashboard['driverOptions']) ? 'disabled' : '' ?>>Save fuel log</button>
            </div>
        </form>
    </div>
</div><?php endif; ?>
<style>
    .fuel-intro { margin: 6px 0 0; color: var(--muted); font-size: .9rem; }
    .fuel-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 4px 0 20px; }
    .fuel-stat { display: flex; flex-direction: column; gap: 7px; min-width: 0; padding: 16px 18px; border: 1px solid var(--border); border-radius: 14px; background: var(--surface); }
    .fuel-stat span, .fuel-stat small { color: var(--muted); font-size: .8rem; }
    .fuel-stat strong { color: var(--text); font-size: 1.35rem; line-height: 1.2; }
    .fuel-log-panel { overflow: hidden; border: 1px solid var(--border); border-radius: 16px; background: var(--surface); }
    .fuel-table-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px; }
    .fuel-table-heading h4 { margin: 0 0 4px; color: var(--text); }
    .fuel-table-heading p { margin: 0; color: var(--muted); font-size: .82rem; }
    .fuel-search-wrap { display: flex; align-items: center; gap: 8px; width: min(100%, 270px); padding: 0 12px; border: 1px solid var(--border); border-radius: 10px; color: var(--muted); }
    .fuel-search-wrap input { width: 100%; min-width: 0; padding: 10px 0; border: 0; outline: 0; background: transparent; color: var(--text); }
    .fuel-log-panel .table-wrapper { border-top: 1px solid var(--border); }
    .fuel-log-panel table { margin: 0; }
    .fuel-log-panel th, .fuel-log-panel td { padding: 13px 20px; }
    .fuel-log-panel td strong { color: var(--text); }
    .fuel-empty { padding: 30px 20px !important; text-align: center; color: var(--muted); }
    .modal-header .eyebrow { margin: 0 0 4px; }
    .modal-header h3 { margin: 0; }
    .fuel-modal-copy { margin: 0 0 18px; color: var(--muted); font-size: .88rem; }
    .fuel-field { margin: 0 0 14px; }
    .fuel-field .form-control { width: 100%; margin-top: 6px; }
    .fuel-input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .fuel-help { display: block; margin-top: 6px; color: var(--muted); }
    .fuel-rate-preview { margin: 0 0 14px; padding: 11px 13px; border-radius: 10px; background: var(--accent-light); color: var(--text); font-size: .84rem; }
    .fuel-form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
    .fuel-form-actions button { min-width: 120px; }
    .fuel-form-actions button:disabled { opacity: .55; cursor: not-allowed; }
    @media (max-width: 900px) { .fuel-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 600px) {
        .fuel-summary { gap: 9px; }
        .fuel-stat { padding: 13px; }
        .fuel-stat strong { font-size: 1.12rem; }
        .fuel-table-heading { align-items: stretch; flex-direction: column; }
        .fuel-search-wrap { width: 100%; }
        .fuel-log-panel th, .fuel-log-panel td { padding: 11px 12px; }
        .fuel-input-grid { grid-template-columns: 1fr; gap: 0; }
    }
</style>

<script>
(() => {
    const modal = document.getElementById('fuel-modal');
    const search = document.getElementById('fuel-search');
    const rows = Array.from(document.querySelectorAll('.fuel-log-row'));
    const noResults = document.getElementById('fuel-no-results');
    const litersInput = document.getElementById('fuel-liters-input');
    const costInput = document.getElementById('fuel-cost-input');
    const vehicleSelect = document.getElementById('fuel-vehicle');
    const fuelBeforeInput = document.getElementById('fuel-level-before');
    const fuelAfterInput = document.getElementById('fuel-level-after');
    const preview = document.getElementById('fuel-rate-preview');

    window.openFuelModal = () => {
        if (!modal) return;
        modal.style.display = 'flex';
        document.getElementById('fuel-vehicle')?.focus();
    };
    window.closeFuelModal = () => { if (modal) modal.style.display = 'none'; };

    modal?.addEventListener('click', (event) => { if (event.target === modal) window.closeFuelModal(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && modal?.style.display === 'flex') window.closeFuelModal(); });

    search.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        let count = 0;
        let liters = 0;
        let cost = 0;
        rows.forEach((row) => {
            const visible = row.dataset.search.includes(query);
            row.hidden = !visible;
            if (visible) {
                count++;
                liters += Number(row.dataset.liters || 0);
                cost += Number(row.dataset.cost || 0);
            }
        });
        noResults.hidden = count !== 0 || rows.length === 0;
        document.getElementById('fuel-count').textContent = count;
        document.getElementById('fuel-liters').textContent = `${liters.toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 })} L`;
        document.getElementById('fuel-cost').textContent = `\u20B1${cost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('fuel-average').textContent = liters > 0 ? `\u20B1${(cost / liters).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '—';
    });

    const updateRate = () => {
        const liters = Number(litersInput.value);
        const cost = Number(costInput.value);
        preview.textContent = liters > 0 && cost >= 0
            ? `Estimated price per liter: \u20B1${(cost / liters).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
            : 'Enter liters and total paid to estimate the price per liter.';
    };
    litersInput?.addEventListener('input', updateRate);
    costInput?.addEventListener('input', updateRate);

    vehicleSelect?.addEventListener('change', () => {
        const selectedVehicle = vehicleSelect.selectedOptions[0];
        fuelBeforeInput.value = selectedVehicle?.dataset.fuelLevel ?? '';
        fuelAfterInput.value = '';
    });

    <?php if ($errors->any() && $dashboard['isAdmin']): ?>
    window.openFuelModal();
    <?php endif; ?>
})();
</script>
