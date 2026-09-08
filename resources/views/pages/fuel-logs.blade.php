<section class="panel">
    <div class="panel-header">
        <div>
            <p class="eyebrow">Fuel Logs</p>
            <h3>Fuel Consumption</h3>
        </div>
        <button class="pill-button" type="button" onclick="openFuelModal()">Log Fuel</button>
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

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Last Fill</th>
                    <th>Fuel Used</th>
                    <th>Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dashboard['fuelLogs']) === 0): ?>
                    <tr><td colspan="4" style="text-align:center;padding:20px;">No fuel logs yet</td></tr>
                <?php endif; ?>
                <?php foreach ($dashboard['fuelLogs'] as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['vehicle']) ?></td>
                        <td><?= htmlspecialchars($log['logged_at']) ?></td>
                        <td><?= htmlspecialchars($log['liters']) ?></td>
                        <td>₱<?= htmlspecialchars($log['cost']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modal: Log Fuel -->
<div id="fuel-modal" class="modal-backdrop" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Log Fuel</h3>
            <button class="close-btn" type="button" onclick="closeFuelModal()">✕</button>
        </div>
        <form method="POST" action="<?= route('fuel-logs.store') ?>">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="fuel-vehicle">Vehicle</label>
                <select class="form-control" id="fuel-vehicle" name="vehicle_id" required>
                    <?php foreach ($dashboard['vehicleOptions'] as $vehicle): ?>
                        <option value="<?= (int) $vehicle->id ?>"><?= htmlspecialchars($vehicle->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="fuel-liters">Liters</label>
                <input class="form-control" type="number" id="fuel-liters" name="liters" required min="0.1" step="0.1">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label for="fuel-cost">Cost (₱)</label>
                <input class="form-control" type="number" id="fuel-cost" name="cost" required min="0" step="0.01">
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label for="fuel-date">Date</label>
                <input class="form-control" type="date" id="fuel-date" name="logged_at" required max="<?= now()->toDateString() ?>" value="<?= now()->toDateString() ?>">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Save Fuel Log</button>
        </form>
    </div>
</div>

<script>
function openFuelModal() {
    document.getElementById('fuel-modal').style.display = 'flex';
}
function closeFuelModal() {
    document.getElementById('fuel-modal').style.display = 'none';
}
</script>
