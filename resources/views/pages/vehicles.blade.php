<div class="fleet-command-wrapper">
    <!-- Fleet Command Top Bar Header -->
    <div class="fleet-command-header">
        <div>
            <h1 class="fleet-title">Fleet Command</h1>
            <p class="fleet-subtitle">Vehicle inventory, maintenance logs, and live location map</p>
        </div>
        <div class="fleet-header-actions">
            <button class="btn-export" onclick="exportFleetCSV()">
                <span class="btn-icon">📊</span> Export Fleet
            </button>
            <button class="btn-add-vehicle" onclick="openAddVehicleModal()">
                <span class="btn-icon">+</span> Add New Vehicle
            </button>
        </div>
    </div>

    <!-- Status Filters Capsule Bar -->
    <div class="status-filters-bar">
        <button class="status-filter-btn active" onclick="filterByStatus('all', this)">All Status</button>
        <button class="status-filter-btn" onclick="filterByStatus('available', this)">AVAILABLE</button>
        <button class="status-filter-btn" onclick="filterByStatus('in transit', this)">IN TRANSIT</button>
        <button class="status-filter-btn" onclick="filterByStatus('reserved', this)">RESERVED</button>
        <button class="status-filter-btn" onclick="filterByStatus('maintenance', this)">MAINTENANCE</button>
        <button class="status-filter-btn" onclick="filterByStatus('inactive', this)">INACTIVE</button>
    </div>

    <!-- Fleet Inventory Table Card -->
    <div class="fleet-table-card">
        <div class="table-responsive">
            <table class="fleet-table" id="fleet-inventory-table">
                <thead>
                    <tr>
                        <th>VEHICLE ID</th>
                        <th>PLATE NO.</th>
                        <th>TYPE</th>
                        <th>BRAND & MODEL</th>
                        <th>CAPACITY</th>
                        <th>ODOMETER</th>
                        <th>STATUS</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="vehicle-table-body">
                    <!-- Row 1 -->
                    <tr data-status="available" data-id="VHC-001">
                        <td><strong class="vehicle-id-text">VHC-001</strong></td>
                        <td><span class="plate-no-link">ABC-1234</span></td>
                        <td>Truck</td>
                        <td>Isuzu Giga (2022)</td>
                        <td>10,000 kg</td>
                        <td>45,320 km</td>
                        <td><span class="status-pill-badge badge-available">Available</span></td>
                        <td class="text-right action-links">
                            <a href="<?= htmlspecialchars($dashboard['basePath'] . '/routes?vehicle=VHC-001') ?>" class="action-btn map-link"><span class="icon-red">📍</span> Map</a>
                            <button class="action-btn view-link" onclick="viewVehicleDetails('VHC-001', 'ABC-1234', 'Truck', 'Isuzu Giga (2022)', '10,000 kg', '45,320 km', 'Available')">View</button>
                            <button class="action-btn edit-link" onclick="editVehicle('VHC-001', 'ABC-1234', 'Truck', 'Isuzu Giga (2022)', '10,000 kg', '45,320 km', 'Available')">Edit</button>
                            <button class="action-btn service-link" onclick="openLogServiceModal('VHC-001', 'Isuzu Giga (2022)')"><span class="icon-wrench">🔧</span> Log Service</button>
                            <button class="action-btn delete-link" onclick="deleteVehicleRow(this, 'VHC-001')">Delete</button>
                        </td>
                    </tr>
                    <!-- Row 2 -->
                    <tr data-status="in transit" data-id="VHC-002">
                        <td><strong class="vehicle-id-text">VHC-002</strong></td>
                        <td><span class="plate-no-link">DEF-5678</span></td>
                        <td>Van</td>
                        <td>Toyota Hiace (2021)</td>
                        <td>2,000 kg</td>
                        <td>78,100 km</td>
                        <td><span class="status-pill-badge badge-in-transit">In Transit</span></td>
                        <td class="text-right action-links">
                            <a href="<?= htmlspecialchars($dashboard['basePath'] . '/routes?vehicle=VHC-002') ?>" class="action-btn map-link"><span class="icon-red">📍</span> Map</a>
                            <button class="action-btn view-link" onclick="viewVehicleDetails('VHC-002', 'DEF-5678', 'Van', 'Toyota Hiace (2021)', '2,000 kg', '78,100 km', 'In Transit')">View</button>
                            <button class="action-btn edit-link" onclick="editVehicle('VHC-002', 'DEF-5678', 'Van', 'Toyota Hiace (2021)', '2,000 kg', '78,100 km', 'In Transit')">Edit</button>
                            <button class="action-btn service-link" onclick="openLogServiceModal('VHC-002', 'Toyota Hiace (2021)')"><span class="icon-wrench">🔧</span> Log Service</button>
                            <button class="action-btn delete-link" onclick="deleteVehicleRow(this, 'VHC-002')">Delete</button>
                        </td>
                    </tr>
                    <!-- Row 3 -->
                    <tr data-status="available" data-id="VHC-003">
                        <td><strong class="vehicle-id-text">VHC-003</strong></td>
                        <td><span class="plate-no-link">GHI-9012</span></td>
                        <td>Motorcycle</td>
                        <td>Honda CRF300L (2023)</td>
                        <td>100 kg</td>
                        <td>12,400 km</td>
                        <td><span class="status-pill-badge badge-available">Available</span></td>
                        <td class="text-right action-links">
                            <a href="<?= htmlspecialchars($dashboard['basePath'] . '/routes?vehicle=VHC-003') ?>" class="action-btn map-link"><span class="icon-red">📍</span> Map</a>
                            <button class="action-btn view-link" onclick="viewVehicleDetails('VHC-003', 'GHI-9012', 'Motorcycle', 'Honda CRF300L (2023)', '100 kg', '12,400 km', 'Available')">View</button>
                            <button class="action-btn edit-link" onclick="editVehicle('VHC-003', 'GHI-9012', 'Motorcycle', 'Honda CRF300L (2023)', '100 kg', '12,400 km', 'Available')">Edit</button>
                            <button class="action-btn service-link" onclick="openLogServiceModal('VHC-003', 'Honda CRF300L (2023)')"><span class="icon-wrench">🔧</span> Log Service</button>
                            <button class="action-btn delete-link" onclick="deleteVehicleRow(this, 'VHC-003')">Delete</button>
                        </td>
                    </tr>
                    <!-- Row 4 -->
                    <tr data-status="maintenance" data-id="VHC-004">
                        <td><strong class="vehicle-id-text">VHC-004</strong></td>
                        <td><span class="plate-no-link">JKL-3456</span></td>
                        <td>Truck</td>
                        <td>Mitsubishi Canter (2020)</td>
                        <td>5,000 kg</td>
                        <td>120,800 km</td>
                        <td><span class="status-pill-badge badge-maintenance">Maintenance</span></td>
                        <td class="text-right action-links">
                            <a href="<?= htmlspecialchars($dashboard['basePath'] . '/routes?vehicle=VHC-004') ?>" class="action-btn map-link"><span class="icon-red">📍</span> Map</a>
                            <button class="action-btn view-link" onclick="viewVehicleDetails('VHC-004', 'JKL-3456', 'Truck', 'Mitsubishi Canter (2020)', '5,000 kg', '120,800 km', 'Maintenance')">View</button>
                            <button class="action-btn edit-link" onclick="editVehicle('VHC-004', 'JKL-3456', 'Truck', 'Mitsubishi Canter (2020)', '5,000 kg', '120,800 km', 'Maintenance')">Edit</button>
                            <button class="action-btn service-link" onclick="openLogServiceModal('VHC-004', 'Mitsubishi Canter (2020)')"><span class="icon-wrench">🔧</span> Log Service</button>
                            <button class="action-btn delete-link" onclick="deleteVehicleRow(this, 'VHC-004')">Delete</button>
                        </td>
                    </tr>
                    <!-- Row 5 -->
                    <tr data-status="reserved" data-id="VHC-005">
                        <td><strong class="vehicle-id-text">VHC-005</strong></td>
                        <td><span class="plate-no-link">MNO-7890</span></td>
                        <td>Van</td>
                        <td>Ford Transit (2022)</td>
                        <td>3,500 kg</td>
                        <td>34,500 km</td>
                        <td><span class="status-pill-badge badge-reserved">Reserved</span></td>
                        <td class="text-right action-links">
                            <a href="<?= htmlspecialchars($dashboard['basePath'] . '/routes?vehicle=VHC-005') ?>" class="action-btn map-link"><span class="icon-red">📍</span> Map</a>
                            <button class="action-btn view-link" onclick="viewVehicleDetails('VHC-005', 'MNO-7890', 'Van', 'Ford Transit (2022)', '3,500 kg', '34,500 km', 'Reserved')">View</button>
                            <button class="action-btn edit-link" onclick="editVehicle('VHC-005', 'MNO-7890', 'Van', 'Ford Transit (2022)', '3,500 kg', '34,500 km', 'Reserved')">Edit</button>
                            <button class="action-btn service-link" onclick="openLogServiceModal('VHC-005', 'Ford Transit (2022)')"><span class="icon-wrench">🔧</span> Log Service</button>
                            <button class="action-btn delete-link" onclick="deleteVehicleRow(this, 'VHC-005')">Delete</button>
                        </td>
                    </tr>
                    <!-- Row 6 -->
                    <tr data-status="inactive" data-id="VHC-006">
                        <td><strong class="vehicle-id-text">VHC-006</strong></td>
                        <td><span class="plate-no-link">PQR-1234</span></td>
                        <td>Sedan</td>
                        <td>Toyota Vios (2023)</td>
                        <td>500 kg</td>
                        <td>8,200 km</td>
                        <td><span class="status-pill-badge badge-inactive">Inactive</span></td>
                        <td class="text-right action-links">
                            <a href="<?= htmlspecialchars($dashboard['basePath'] . '/routes?vehicle=VHC-006') ?>" class="action-btn map-link"><span class="icon-red">📍</span> Map</a>
                            <button class="action-btn view-link" onclick="viewVehicleDetails('VHC-006', 'PQR-1234', 'Sedan', 'Toyota Vios (2023)', '500 kg', '8,200 km', 'Inactive')">View</button>
                            <button class="action-btn edit-link" onclick="editVehicle('VHC-006', 'PQR-1234', 'Sedan', 'Toyota Vios (2023)', '500 kg', '8,200 km', 'Inactive')">Edit</button>
                            <button class="action-btn service-link" onclick="openLogServiceModal('VHC-006', 'Toyota Vios (2023)')"><span class="icon-wrench">🔧</span> Log Service</button>
                            <button class="action-btn delete-link" onclick="deleteVehicleRow(this, 'VHC-006')">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Vehicle -->
<div id="add-vehicle-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>+ Add New Vehicle</h3>
            <button class="close-btn" onclick="closeAddVehicleModal()">✕</button>
        </div>
        <form onsubmit="submitNewVehicle(event)">
            <div class="form-grid">
                <div class="form-group">
                    <label>Vehicle ID</label>
                    <input type="text" id="add-vhc-id" class="form-control" placeholder="e.g. VHC-007" required />
                </div>
                <div class="form-group">
                    <label>Plate No.</label>
                    <input type="text" id="add-plate" class="form-control" placeholder="e.g. XYZ-9988" required />
                </div>
                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select id="add-type" class="form-control" required>
                        <option value="Truck">Truck</option>
                        <option value="Van">Van</option>
                        <option value="Motorcycle">Motorcycle</option>
                        <option value="Sedan">Sedan</option>
                        <option value="Pickup">Pickup</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Brand & Model (Year)</label>
                    <input type="text" id="add-brand" class="form-control" placeholder="e.g. Isuzu Elf (2023)" required />
                </div>
                <div class="form-group">
                    <label>Payload Capacity (kg)</label>
                    <input type="text" id="add-capacity" class="form-control" placeholder="e.g. 4,500 kg" required />
                </div>
                <div class="form-group">
                    <label>Odometer Reading (km)</label>
                    <input type="text" id="add-odometer" class="form-control" placeholder="e.g. 15,200 km" required />
                </div>
                <div class="form-group full-width">
                    <label>Initial Status</label>
                    <select id="add-status" class="form-control" required>
                        <option value="Available">Available</option>
                        <option value="In Transit">In Transit</option>
                        <option value="Reserved">Reserved</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer margin-top-md">
                <button type="button" class="btn-secondary" onclick="closeAddVehicleModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Vehicle</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: View Vehicle Details -->
<div id="view-vehicle-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Vehicle Details Specifications</h3>
            <button class="close-btn" onclick="closeViewModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="details-summary-header">
                <div>
                    <span class="badge-code-lg" id="view-modal-id">VHC-001</span>
                    <h2 id="view-modal-brand" class="margin-top-xs">Isuzu Giga (2022)</h2>
                    <p class="plate-text-lg" id="view-modal-plate">Plate: ABC-1234</p>
                </div>
                <div id="view-modal-status-badge">
                    <span class="status-pill-badge badge-available">Available</span>
                </div>
            </div>
            <hr class="divider" />
            <div class="details-grid">
                <div class="details-item">
                    <span class="details-label">Type</span>
                    <strong id="view-modal-type">Truck</strong>
                </div>
                <div class="details-item">
                    <span class="details-label">Payload Capacity</span>
                    <strong id="view-modal-capacity">10,000 kg</strong>
                </div>
                <div class="details-item">
                    <span class="details-label">Odometer</span>
                    <strong id="view-modal-odometer">45,320 km</strong>
                </div>
                <div class="details-item">
                    <span class="details-label">Next Service Due</span>
                    <strong>Sep 15, 2026</strong>
                </div>
            </div>
        </div>
        <div class="modal-footer margin-top-md">
            <button class="btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- Modal: Log Service Maintenance -->
<div id="log-service-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3>🔧 Log Vehicle Service Maintenance</h3>
            <button class="close-btn" onclick="closeLogServiceModal()">✕</button>
        </div>
        <form onsubmit="submitServiceLog(event)">
            <input type="hidden" id="service-vehicle-id" />
            <div class="form-group">
                <label>Target Vehicle:</label>
                <input type="text" id="service-vehicle-name" class="form-control" readonly />
            </div>
            <div class="form-group margin-top-sm">
                <label>Service Maintenance Type:</label>
                <select id="service-type" class="form-control">
                    <option value="Routine Oil & Filter Change">Routine Oil & Filter Change</option>
                    <option value="Brake Pad Replacement">Brake Pad Replacement</option>
                    <option value="Tire Alignment & Rotation">Tire Alignment & Rotation</option>
                    <option value="Engine Overhaul & Diagnostics">Engine Overhaul & Diagnostics</option>
                </select>
            </div>
            <div class="form-group margin-top-sm">
                <label>Service Cost (PHP ₱):</label>
                <input type="number" id="service-cost" class="form-control" placeholder="e.g. 8500" required />
            </div>
            <div class="form-group margin-top-sm">
                <label>Mechanic Notes / Description:</label>
                <textarea id="service-notes" class="form-control" rows="3" placeholder="Replaced engine oil filter and calibrated brake pads."></textarea>
            </div>
            <div class="modal-footer margin-top-md">
                <button type="button" class="btn-secondary" onclick="closeLogServiceModal()">Cancel</button>
                <button type="submit" class="btn-primary">Record Maintenance</button>
            </div>
        </form>
    </div>
</div>

<!-- CSS Styling for Fleet Command Vehicles View -->
<style>
.fleet-command-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Header Top Bar */
.fleet-command-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.fleet-title {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 800;
    color: #11253f;
    letter-spacing: -0.02em;
}
.fleet-subtitle {
    margin: 4px 0 0;
    color: #6c7a93;
    font-size: 0.92rem;
    font-weight: 500;
}
.fleet-header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}
.btn-export {
    border: 1px solid #e7ebf3;
    background: #ffffff;
    color: #11253f;
    padding: 10px 18px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(20, 33, 61, 0.05);
    transition: transform 0.15s ease, background 0.15s ease;
}
.btn-export:hover {
    background: #f8fafc;
    transform: translateY(-1px);
}
.btn-add-vehicle {
    border: 0;
    background: #2563eb;
    color: #ffffff;
    padding: 10px 20px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
    transition: transform 0.15s ease, background 0.15s ease;
}
.btn-add-vehicle:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}
.btn-icon {
    margin-right: 6px;
}

/* Status Filter Bar */
.status-filters-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 4px;
}
.status-filter-btn {
    border: 0;
    background: #f1f5f9;
    color: #64748b;
    padding: 8px 18px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.status-filter-btn:hover {
    background: #e2e8f0;
    color: #334155;
}
.status-filter-btn.active {
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

/* Fleet Inventory Table Card */
.fleet-table-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 18px 35px rgba(20, 33, 61, 0.06);
    border: 1px solid #e7ebf3;
    overflow: hidden;
}
.table-responsive {
    width: 100%;
    overflow-x: auto;
}
.fleet-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
    text-align: left;
}
.fleet-table th {
    padding: 14px 16px;
    color: #64748b;
    font-weight: 800;
    font-size: 0.74rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    border-bottom: 1px solid #e2e8f0;
    background: #fafbfc;
    white-space: nowrap;
}
.fleet-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-weight: 500;
    vertical-align: middle;
    white-space: nowrap;
}
.fleet-table tbody tr {
    transition: background 0.15s ease;
}
.fleet-table tbody tr:hover {
    background: #f8fafc;
}
.vehicle-id-text {
    font-weight: 800;
    color: #0f172a;
    font-size: 0.86rem;
    white-space: nowrap;
}
.plate-no-link {
    color: #2563eb;
    font-weight: 700;
    font-family: 'Inter', monospace;
    font-size: 0.86rem;
    white-space: nowrap;
}

/* Custom Status Pills matching Screenshot */
.status-pill-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 800;
    text-align: center;
}
.badge-available {
    background: #dcfce7;
    color: #166534;
}
.badge-in-transit {
    background: #f3e8ff;
    color: #7e22ce;
}
.badge-reserved {
    background: #dbeafe;
    color: #1e40af;
}
.badge-maintenance {
    background: #ffedd5;
    color: #c2410c;
}
.badge-inactive {
    background: #f1f5f9;
    color: #64748b;
}

/* Table Action Links */
.action-links {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    white-space: nowrap;
}
.action-btn {
    border: 0;
    background: transparent;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    padding: 0;
    white-space: nowrap;
    transition: opacity 0.15s ease;
}
.action-btn:hover {
    opacity: 0.75;
}
.map-link { color: #dc2626; }
.view-link { color: #334155; }
.edit-link { color: #16a34a; }
.service-link { color: #d97706; }
.delete-link { color: #ef4444; }
.icon-red { color: #ef4444; margin-right: 2px; }
.icon-wrench { color: #d97706; margin-right: 2px; }
.text-right { text-align: right; }

/* Modals */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}
.form-group.full-width {
    grid-column: span 2;
}
.details-summary-header h2 { margin: 0; font-size: 1.3rem; }
.badge-code-lg { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 0.8rem; }
.plate-text-lg { margin: 4px 0 0; color: #64748b; font-size: 0.9rem; font-weight: 600; }
.details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.details-item { display: flex; flex-direction: column; gap: 4px; }
.details-label { font-size: 0.76rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
.margin-top-xs { margin-top: 4px; }
</style>

<!-- JavaScript Logic for Fleet Command Operations -->
<script>
function filterByStatus(statusKey, btnElem) {
    document.querySelectorAll('.status-filter-btn').forEach(btn => btn.classList.remove('active'));
    btnElem.classList.add('active');

    const rows = document.querySelectorAll('#vehicle-table-body tr');
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (statusKey === 'all' || rowStatus === statusKey) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openAddVehicleModal() {
    document.getElementById('add-vehicle-modal').style.display = 'flex';
}
function closeAddVehicleModal() {
    document.getElementById('add-vehicle-modal').style.display = 'none';
}

function submitNewVehicle(e) {
    e.preventDefault();
    const vId = document.getElementById('add-vhc-id').value;
    const plate = document.getElementById('add-plate').value;
    const type = document.getElementById('add-type').value;
    const brand = document.getElementById('add-brand').value;
    const cap = document.getElementById('add-capacity').value;
    const odo = document.getElementById('add-odometer').value;
    const status = document.getElementById('add-status').value;

    const badgeClass = status === 'Available' ? 'badge-available' :
                       (status === 'In Transit' ? 'badge-in-transit' :
                       (status === 'Reserved' ? 'badge-reserved' :
                       (status === 'Maintenance' ? 'badge-maintenance' : 'badge-inactive')));

    const tbody = document.getElementById('vehicle-table-body');
    const newTr = document.createElement('tr');
    newTr.setAttribute('data-status', status.toLowerCase());
    newTr.setAttribute('data-id', vId);

    newTr.innerHTML = `
        <td><strong class="vehicle-id-text">${vId}</strong></td>
        <td><span class="plate-no-link">${plate}</span></td>
        <td>${type}</td>
        <td>${brand}</td>
        <td>${cap}</td>
        <td>${odo}</td>
        <td><span class="status-pill-badge ${badgeClass}">${status}</span></td>
        <td class="text-right action-links">
            <a href="${basePath}/routes?vehicle=${vId}" class="action-btn map-link"><span class="icon-red">📍</span> Map</a>
            <button class="action-btn view-link" onclick="viewVehicleDetails('${vId}', '${plate}', '${type}', '${brand}', '${cap}', '${odo}', '${status}')">View</button>
            <button class="action-btn edit-link" onclick="editVehicle('${vId}', '${plate}', '${type}', '${brand}', '${cap}', '${odo}', '${status}')">Edit</button>
            <button class="action-btn service-link" onclick="openLogServiceModal('${vId}', '${brand}')"><span class="icon-wrench">🔧</span> Log Service</button>
            <button class="action-btn delete-link" onclick="deleteVehicleRow(this, '${vId}')">Delete</button>
        </td>
    `;
    tbody.prepend(newTr);

    closeAddVehicleModal();
    alert(`Vehicle ${vId} registered successfully!`);
}

function viewVehicleDetails(vId, plate, type, brand, cap, odo, status) {
    document.getElementById('view-modal-id').innerText = vId;
    document.getElementById('view-modal-brand').innerText = brand;
    document.getElementById('view-modal-plate').innerText = 'Plate: ' + plate;
    document.getElementById('view-modal-type').innerText = type;
    document.getElementById('view-modal-capacity').innerText = cap;
    document.getElementById('view-modal-odometer').innerText = odo;

    const badgeClass = status === 'Available' ? 'badge-available' :
                       (status === 'In Transit' ? 'badge-in-transit' :
                       (status === 'Reserved' ? 'badge-reserved' :
                       (status === 'Maintenance' ? 'badge-maintenance' : 'badge-inactive')));

    document.getElementById('view-modal-status-badge').innerHTML = `<span class="status-pill-badge ${badgeClass}">${status}</span>`;
    document.getElementById('view-vehicle-modal').style.display = 'flex';
}
function closeViewModal() {
    document.getElementById('view-vehicle-modal').style.display = 'none';
}

function editVehicle(vId, plate, type, brand, cap, odo, status) {
    openAddVehicleModal();
    document.getElementById('add-vhc-id').value = vId;
    document.getElementById('add-plate').value = plate;
    document.getElementById('add-type').value = type;
    document.getElementById('add-brand').value = brand;
    document.getElementById('add-capacity').value = cap;
    document.getElementById('add-odometer').value = odo;
    document.getElementById('add-status').value = status;
}

function openLogServiceModal(vId, brand) {
    document.getElementById('service-vehicle-id').value = vId;
    document.getElementById('service-vehicle-name').value = `${vId} - ${brand}`;
    document.getElementById('log-service-modal').style.display = 'flex';
}
function closeLogServiceModal() {
    document.getElementById('log-service-modal').style.display = 'none';
}

function submitServiceLog(e) {
    e.preventDefault();
    const vId = document.getElementById('service-vehicle-id').value;
    const type = document.getElementById('service-type').value;
    const cost = document.getElementById('service-cost').value;

    alert(`Service log for vehicle ${vId} (${type} - ₱${cost}) recorded successfully!`);
    closeLogServiceModal();
}

function deleteVehicleRow(btn, vId) {
    if (confirm(`Are you sure you want to delete vehicle ${vId} from fleet inventory?`)) {
        const row = btn.closest('tr');
        row.remove();
    }
}

function exportFleetCSV() {
    let csv = "VEHICLE ID,PLATE NO,TYPE,BRAND & MODEL,CAPACITY,ODOMETER,STATUS\n";
    const rows = document.querySelectorAll('#vehicle-table-body tr');
    rows.forEach(r => {
        const cols = r.querySelectorAll('td');
        if (cols.length >= 7) {
            const rowData = [
                cols[0].innerText.trim(),
                cols[1].innerText.trim(),
                cols[2].innerText.trim(),
                `"${cols[3].innerText.trim()}"`,
                cols[4].innerText.trim(),
                cols[5].innerText.trim(),
                cols[6].innerText.trim()
            ];
            csv += rowData.join(",") + "\n";
        }
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('href', url);
    a.setAttribute('download', `Fleet_Inventory_${new Date().toISOString().slice(0,10)}.csv`);
    a.click();
}
</script>
