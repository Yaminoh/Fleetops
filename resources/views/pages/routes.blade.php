<!-- Leaflet.js CSS & FontAwesome Icons -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<style>
    .route-selection-panel {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
    }
    .route-selection-panel[hidden] { display: none; }
    .route-selection-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 9px;
    }
    .route-selection-heading h4 { margin: 0; font-size: 0.9rem; color: var(--text); }
    .route-selection-count { color: var(--muted); font-size: 0.75rem; }
    .route-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 120px), 1fr));
        gap: 8px;
    }
    .route-option {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        gap: 4px;
        min-width: 0;
        min-height: 58px;
        padding: 9px 11px;
        border: 1px solid var(--border);
        border-radius: 9px;
        background: var(--surface);
        color: var(--text);
        text-align: left;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease, opacity 0.15s ease;
    }
    .route-option:hover { border-color: var(--teal); }
    .route-option:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
    .route-option.is-selected {
        border-color: var(--teal);
        box-shadow: inset 3px 0 0 var(--teal);
        background: rgba(23, 162, 184, 0.09);
    }
    .route-option-label { font-size: 0.8rem; font-weight: 800; }
    .route-option-summary { color: var(--muted); font-size: 0.74rem; line-height: 1.3; }
    .route-destination-icon { background: transparent; border: 0; }
    .route-destination-pin {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 3px solid #fff;
        border-radius: 50% 50% 50% 4px;
        background: #e76f51;
        box-shadow: 0 3px 9px rgba(20, 33, 61, 0.35);
        color: #fff;
        transform: rotate(-45deg);
    }
    .route-destination-pin span { transform: rotate(45deg); font-size: 0.72rem; font-weight: 900; }
    .dispatch-item { width: 100%; color: var(--text); font: inherit; text-align: left; }
    .dispatch-item:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
    .dispatch-item[aria-disabled="true"] { cursor: default; opacity: 0.65; }
    .dispatch-info { min-width: 0; }
    .dispatch-info h4, .dispatch-info p { overflow-wrap: anywhere; }
    .dispatch-trip-status { display: block; margin-top: 4px; color: var(--muted); font-size: 0.68rem; text-align: right; }
    .map-header-tools { justify-content: flex-end; }
    @media (max-width: 420px) {
        .route-options { grid-template-columns: 1fr; }
    }
    /* Compact Smart Routing on phones */
    @media (max-width: 768px) {
        .tracking-control-bar { padding: 12px 16px; gap: 8px; }
        .control-left h2 { font-size: 1.05rem; }
        .control-right .btn-primary, .control-right .btn-secondary { padding: 8px 12px; font-size: 0.8rem; }
        .map-container-grid { gap: 12px; }
        .panel-card { padding: 14px; }
        .gps-metrics-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .tracking-analytics-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .analytics-card { padding: 10px 12px; gap: 8px; }
        .analytics-icon { width: 34px; height: 34px; border-radius: 10px; font-size: 1rem; }
        .analytics-label { font-size: 0.65rem; }
        .analytics-value { font-size: 1.05rem; }
        .analytics-sub { font-size: 0.68rem; }
        .leaflet-map-canvas { height: 320px !important; }
        .map-filters, .map-action-buttons { flex-wrap: wrap; }
    }
    @media (max-width: 420px) {
        .tracking-analytics-grid { grid-template-columns: 1fr; }
        .tracking-control-bar { flex-direction: column; align-items: stretch; }
        .control-right { flex-direction: column; }
        .leaflet-map-canvas { height: 280px !important; }
    }
</style>

<div class="tracking-module-wrapper">
    <!-- Main Module Control Bar -->
    <div class="tracking-control-bar">
        <div class="control-left">
            <h2>Real-Time Fleet Map</h2>
            <div class="live-pulse-badge">
                <span class="pulse-dot"></span> Auto-refreshing (5s)
            </div>
        </div>
        <div class="control-right">
            <button class="btn-secondary" onclick="toggleDriverMobileModal()" hidden>
                📱 Driver Mobile GPS Tracker
            </button>
            <button class="btn-secondary" onclick="toggleIntegrationModal()" hidden>
                🔗 System Integration
            </button>
            <button class="btn-primary" onclick="toggleNotificationsDrawer()" hidden>
                🔔 Live Alerts <span class="badge-count" id="notif-count">2</span>
            </button>
        </div>
    </div>

    <!-- Main Interactive Map & Info Split Screen -->
    <div class="map-container-grid">
        <!-- Interactive Leaflet Map Box -->
        <div class="map-view-card">
            <div class="map-header-tools">
                <div class="map-action-buttons">
                    <button class="map-btn" onclick="recenterMap()" title="Recenter Map">🎯 Recenter</button>
                    <button class="map-btn" onclick="toggleFullscreenMap()" title="Full Screen">⛶ Fullscreen</button>
                </div>
            </div>
            <div id="fleet-map" class="leaflet-map-canvas"></div>

            <!-- Route Legend Overlay -->
            <div class="map-route-legend">
                <div class="legend-item"><span class="legend-line green"></span> On Time</div>
                <div class="legend-item"><span class="legend-line yellow"></span> Delayed</div>
                <div class="legend-item"><span class="legend-line red"></span> Critical Delay</div>
            </div>
        </div>

        <!-- Sidebar Panel: Live Vehicle Info & Route ETA -->
        <div class="vehicle-info-sidebar">
            <!-- Default placeholder or vehicle info content -->
            <div id="vehicle-details-card" class="panel-card shadow-sm">
                <div class="card-empty-state" id="empty-state">
                    <div class="empty-icon">📍</div>
                    <h4>Select a Vehicle</h4>
                    <p>Click any truck marker on the map to inspect live speed, fuel, driver, and ETA route visualization.</p>
                </div>

                <div id="active-vehicle-content" style="display: none;">
                    <!-- Vehicle Info Header -->
                    <div class="vehicle-card-header">
                        <div>
                            <span class="badge-code" id="info-vehicle-code">—</span>
                            <h3 id="info-vehicle-type">Select a vehicle</h3>
                            <p class="plate-text" id="info-plate">Plate: —</p>
                        </div>
                        <div class="status-badge" id="info-status-badge">Active</div>
                    </div>

                    <hr class="divider" />

                    <!-- Live Vehicle Information List -->
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Driver Name</span>
                            <strong class="info-val" id="info-driver">—</strong>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Employee ID</span>
                            <strong class="info-val" id="info-emp-id">—</strong>
                        </div>
                        <div class="info-item full">
                            <span class="info-label">Trip Route</span>
                            <strong class="info-val" id="info-trip-route">— → —</strong>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Speed</span>
                            <strong class="info-val text-teal" id="info-speed">0 km/h</strong>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fuel Level</span>
                            <div class="fuel-progress-wrapper">
                                <div class="fuel-bar"><div class="fuel-fill" id="info-fuel-bar" style="width: 88%;"></div></div>
                                <span class="fuel-text" id="info-fuel-val">88%</span>
                            </div>
                        </div>
                        <div class="info-item full">
                            <span class="info-label">Current Location (GPS)</span>
                            <span class="info-val-sm" id="info-location">No live GPS fix</span>
                        </div>
                        <div class="info-item full">
                            <span class="info-label">Destination</span>
                            <span class="info-val-highlight" id="info-destination">No destination</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Trip Start Time</span>
                            <span class="info-val-sm" id="info-start-time">Not started</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Route Status</span>
                            <span class="status-pill-lg" id="info-route-status">On Time</span>
                        </div>
                    </div>

                    <!-- ETA Box -->
                    <div class="eta-live-card">
                        <div class="eta-header">
                            <span>⏱️ ETA & Route Metrics</span>
                            <span class="eta-live-tag">Updated live</span>
                        </div>
                        <div class="eta-metrics-row">
                            <div>
                                <p class="eta-sub">Remaining Dist.</p>
                                <h4 id="eta-dist">12.4 km</h4>
                            </div>
                            <div>
                                <p class="eta-sub">Travel Time</p>
                                <h4 id="eta-time">22 mins</h4>
                            </div>
                            <div>
                                <p class="eta-sub">Expected Arrival</p>
                                <h4 id="eta-arrival" class="text-teal">11:48 AM</h4>
                            </div>
                        </div>
                    </div>

                    <div class="route-selection-panel" id="route-selection-panel" hidden>
                        <div class="route-selection-heading">
                            <h4>Route Options</h4>
                            <span class="route-selection-count" id="route-selection-count"></span>
                        </div>
                        <div class="route-options" id="route-options" role="group" aria-label="Select a driving route"></div>
                    </div>

                    <!-- Arrival Monitoring Action Box -->
                    <div id="arrival-alert-box" class="arrival-success-box" style="display: none;">
                        <h4>🎉 Arrival Detected!</h4>
                        <p>Vehicle reached destination geofence (< 50m). Trip recorded as <strong>Completed</strong>.</p>
                    </div>

                    <div class="vehicle-actions">
                        <button class="btn-secondary block" onclick="focusSelectedVehicleRoute()">🗺️ Focus Route Geometry</button>
                    </div>
                </div>
            </div>
            <section class="panel-card shadow-sm" aria-labelledby="active-dispatches-heading">
                <div class="panel-header-sub">
                    <h3 id="active-dispatches-heading">Active Dispatches</h3>
                    <span class="badge-sm green" id="active-dispatch-count">—</span>
                </div>
                <div class="dispatch-list" id="dispatch-list-container" aria-live="polite">
                    <div class="dispatch-empty" style="padding:1rem 0.5rem;text-align:center;color:var(--muted,#6c7a93);font-size:0.85rem;">Loading active dispatches...</div>
                </div>
            </section>
    </div>
</div>

<!-- Drawer: Real-Time Alerts & Notifications -->
<div id="notifications-drawer" class="drawer-panel">
    <div class="drawer-header">
        <h3>🔔 Fleet Live Notifications</h3>
        <button class="close-btn" onclick="toggleNotificationsDrawer()">✕</button>
    </div>
    <div class="drawer-content" id="notifications-list">
        <!-- Dynamic list -->
    </div>
</div>

<!-- Modal: Driver Mobile GPS Tracking Simulator -->
<div id="driver-mobile-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-card shadow-lg">
        <div class="modal-header">
            <h3>📱 Driver Mobile Tracking Interface</h3>
            <button class="close-btn" onclick="toggleDriverMobileModal()">✕</button>
        </div>
        <div class="modal-body">
            <p class="text-muted">Simulate or broadcast live mobile GPS coordinates using the HTML5 Browser Geolocation API every 5 seconds to endpoint <code>POST /api/location/update</code>.</p>

            <div class="form-group margin-top-sm">
                <label>Select Driver Vehicle:</label>
                <select id="mobile-vehicle-select" class="form-control">
                    <?php foreach (($dashboard['vehicleOptions'] ?? []) as $optVehicle): ?>
                        <option value="<?= (int)$optVehicle->id ?>"><?= htmlspecialchars(($optVehicle->name ?? $optVehicle->plate_number ?? 'Vehicle') . ' (' . htmlspecialchars($optVehicle->plate_number ?? '') . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="gps-simulator-box margin-top-md">
                <div class="gps-status-header">
                    <span>GPS Status: <strong id="gps-status-text" class="text-teal">Inactive</strong></span>
                    <button id="btn-toggle-gps" class="btn-primary btn-sm" onclick="toggleBrowserGeolocation()">Start Live GPS Broadcast</button>
                </div>
                <div class="gps-metrics-grid margin-top-sm">
                    <div><small>Latitude:</small> <span id="mobile-lat">—</span></div>
                    <div><small>Longitude:</small> <span id="mobile-lng">—</span></div>
                    <div><small>Speed:</small> <span id="mobile-speed">—</span></div>
                    <div><small>Interval:</small> <span>Every 5s</span></div>
                </div>
                <div class="margin-top-sm">
                    <label><small>Simulate Movement Along Route:</small></label>
                    <button class="btn-secondary btn-sm" onclick="simulateVehicleMovementStep()">Step Forward 500m</button>
                    <button class="btn-secondary btn-sm text-green" onclick="simulateDestinationArrival()">Simulate Destination Arrival (< 50m)</button>
                </div>
            </div>

            <div class="console-log-box margin-top-md">
                <label><small>API Transmission Log:</small></label>
                <pre id="gps-log-output">Ready to transmit GPS telemetry to PostgreSQL database...</pre>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cross-System Integration -->
<div id="integration-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-card shadow-lg width-lg">
        <div class="modal-header">
            <h3>🔗 Logistics System Integration Interface</h3>
            <button class="close-btn" onclick="toggleIntegrationModal()">✕</button>
        </div>
        <div class="modal-body">
            <p class="text-muted">Live data exchange hub connecting <strong>Logistics 2 Fleet Management</strong> with enterprise systems.</p>
            
            <div class="integration-grid margin-top-md">
                <div class="integration-card">
                    <h4>📦 Logistics 1: Procurement & Supply Chain</h4>
                    <p><small>Receive Delivery Requests | Send Delivery Status</small></p>
                    <button class="btn-secondary btn-sm" onclick="triggerSystemSync('logistics1')">Sync Logistics 1 Data</button>
                </div>
                <div class="integration-card">
                    <h4>👥 HR3: Workforce Operations</h4>
                    <p><small>Receive Driver Info | Send Driver Assignments</small></p>
                    <button class="btn-secondary btn-sm" onclick="triggerSystemSync('hr3')">Sync HR3 Data</button>
                </div>
                <div class="integration-card">
                    <h4>💳 HR4: Compensation & Payroll</h4>
                    <p><small>Send Driver Trip Logs & OT Hours</small></p>
                    <button class="btn-secondary btn-sm" onclick="triggerSystemSync('hr4')">Sync HR4 Data</button>
                </div>
                <div class="integration-card">
                    <h4>💰 Financial Management System</h4>
                    <p><small>Receive Budget Allocation | Send Fuel & Transport Cost Reports</small></p>
                    <button class="btn-secondary btn-sm" onclick="triggerSystemSync('finance')">Sync Financial Data</button>
                </div>
            </div>

            <div class="console-log-box margin-top-md">
                <label><small>System Sync Output JSON:</small></label>
                <pre id="integration-output">Click any system above to test REST payload transmission.</pre>
            </div>
        </div>
    </div>
</div>

<!-- Embedded JS Logic for Interactive Leaflet Map & API Integration -->
<script>
    const apiBasePath = <?= json_encode(url('/api')) ?>;
    const activeDispatchesUrl = <?= json_encode(url('/dispatches/active')) ?>;
    let map = null;
    let vehicleMarkers = {};
    let activeRoutePolyline = null;
    let activeTrafficPolyline = null;
    let routePolylines = [];
    let activeOriginMarker = null;
    let activeDestinationMarker = null;
    let routeOptions = [];
    let selectedRouteIndex = 0;
    let activeRouteColor = 'green';
    let routeRequestSequence = 0;
    let activeVehicleId = null;
    let selectedDispatchData = null;
    let fleetData = [];
    let activeDispatches = [];
    let activeDispatchesLoaded = false;
    let refreshInterval = null;
    let gpsWatchId = null;
    let simulatedGpsInterval = null;
    let currentSearchQuery = '';

    document.addEventListener('DOMContentLoaded', function () {
        initLeafletMap();
        loadFleetData();
        loadActiveDispatches();

        // Auto-refresh vehicle locations every 5 seconds without page reload
        refreshInterval = setInterval(function () {
            loadFleetData();
            loadActiveDispatches();
        }, 5000);

        const globalSearchInput = document.getElementById('globalSearchInput');
        if (globalSearchInput) {
            globalSearchInput.addEventListener('input', function () {
                currentSearchQuery = this.value.trim().toLowerCase();
                applyFleetFilters();
            });
        }
    });

    function dispatchMatchesSearch(dispatch, query) {
        if (!query) return true;
        return [dispatch.dispatch_no, dispatch.vehicle_code, dispatch.plate_number, dispatch.driver_name, dispatch.employee_id, dispatch.destination, dispatch.origin]
            .filter(Boolean)
            .some(field => String(field).toLowerCase().includes(query));
    }

    function hasValidCoordinates(vehicle) {
        return vehicle.latitude !== null && vehicle.latitude !== undefined && vehicle.latitude !== '' &&
            vehicle.longitude !== null && vehicle.longitude !== undefined && vehicle.longitude !== '' &&
            Number.isFinite(Number(vehicle.latitude)) && Number.isFinite(Number(vehicle.longitude)) &&
            Math.abs(Number(vehicle.latitude)) <= 90 && Math.abs(Number(vehicle.longitude)) <= 180;
    }

    function applyFleetFilters() {
        renderFleetMarkers();
        renderActiveDispatches();
    }

    function initLeafletMap() {
        map = L.map('fleet-map', {
            center: [12.8797, 121.7740],
            zoom: 6,
            zoomControl: true
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors | Logistics 2 Fleet Tracker'
        }).addTo(map);
    }

    function createTruckIcon(vehicleCode, status, speed, color) {
        const isDark = status === 'Maintenance';
        const colorHex = color === 'red' ? '#e74c3c' : (color === 'yellow' ? '#f39c12' : '#17a2b8');
        
        const html = `
            <div class="truck-marker-pin" style="--pin-color: ${colorHex}">
                <div class="truck-icon-body">🚚</div>
                <div class="truck-tag">${vehicleCode}</div>
                <div class="truck-speed">${speed} km/h</div>
            </div>
        `;

        return L.divIcon({
            html: html,
            className: 'custom-leaflet-truck',
            iconSize: [48, 48],
            iconAnchor: [24, 24],
            popupAnchor: [0, -20]
        });
    }

    function loadFleetData() {
        fetch(apiBasePath + '/vehicles/live')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.vehicles) {
                    fleetData = data.vehicles;
                    renderActiveDispatches();
                    if (activeVehicleId !== null && !fleetData.some(v => v.id === activeVehicleId)) {
                        activeVehicleId = null;
                        selectedDispatchData = null;
                        clearRouteDisplay();
                        document.getElementById('empty-state').style.display = 'block';
                        document.getElementById('active-vehicle-content').style.display = 'none';
                    }

                    applyFleetFilters();
                    updateDashboardAnalytics();
                    loadNotifications();

                    if (activeVehicleId) {
                        const activeV = fleetData.find(v => v.id === activeVehicleId);
                        if (activeV) {
                            const selectedDispatch = activeDispatches.find(dispatch => Number(dispatch.vehicle_id) === activeVehicleId)
                                || selectedDispatchData;
                            if (selectedDispatch) selectedDispatchData = selectedDispatch;
                            const selectedTripId = selectedDispatch
                                ? (selectedDispatch.trip_record_id ? Number(selectedDispatch.trip_record_id) : null)
                                : activeV.active_trip_id;
                            displayVehicleDetails(activeV, false, selectedTripId, selectedDispatch);
                        }
                    }
                }
            })
            .catch(err => console.error('Error fetching live vehicle data:', err));
    }

    function renderFleetMarkers() {
        const selectedVehicle = fleetData.find(v => v.id === activeVehicleId);
        Object.keys(vehicleMarkers).forEach(id => {
            if (!selectedVehicle || Number(id) !== selectedVehicle.id) {
                if (map.hasLayer(vehicleMarkers[id])) {
                    map.removeLayer(vehicleMarkers[id]);
                }
                delete vehicleMarkers[id];
            }
        });

        if (!selectedVehicle) return;

        if (!hasValidCoordinates(selectedVehicle)) {
            // No live coordinates yet — leave the map centered, remove the marker if present.
            if (vehicleMarkers[selectedVehicle.id]) {
                if (map.hasLayer(vehicleMarkers[selectedVehicle.id])) map.removeLayer(vehicleMarkers[selectedVehicle.id]);
                delete vehicleMarkers[selectedVehicle.id];
            }
            return;
        }
        const latLng = [Number(selectedVehicle.latitude), Number(selectedVehicle.longitude)];
        const icon = createTruckIcon(selectedVehicle.vehicle_code, selectedVehicle.status, selectedVehicle.speed, selectedVehicle.route_color);

        if (vehicleMarkers[selectedVehicle.id]) {
            vehicleMarkers[selectedVehicle.id].setLatLng(latLng);
            vehicleMarkers[selectedVehicle.id].setIcon(icon);
            if (!map.hasLayer(vehicleMarkers[selectedVehicle.id])) {
                vehicleMarkers[selectedVehicle.id].addTo(map);
            }
        } else {
            const marker = L.marker(latLng, { icon: icon }).addTo(map);
            marker.on('click', () => selectVehicle(selectedVehicle.id));
            vehicleMarkers[selectedVehicle.id] = marker;
        }
    }

    function selectVehicle(vehicleId, selectedDispatch = null) {
        const v = fleetData.find(item => item.id === vehicleId);
        if (!v) return;

        const dispatchSelection = selectedDispatch
            || activeDispatches.find(dispatch => Number(dispatch.vehicle_id) === Number(vehicleId))
            || null;
        selectedDispatchData = dispatchSelection;
        const tripId = dispatchSelection
            ? (dispatchSelection.trip_record_id ? Number(dispatchSelection.trip_record_id) : null)
            : v.active_trip_id;
        activeVehicleId = vehicleId;
        renderActiveDispatches();
        clearRouteDisplay();
        renderFleetMarkers();
        displayVehicleDetails(v, true, tripId, dispatchSelection);
        if (tripId) loadTripRoute(tripId);

        // Center map on marker when live coordinates exist
        if (hasValidCoordinates(v)) {
            map.setView([Number(v.latitude), Number(v.longitude)], 12, { animate: true });
        }
    }

    function displayVehicleDetails(v, updateEta = true, tripId = v.active_trip_id, selectedDispatch = null) {
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('active-vehicle-content').style.display = 'block';

        document.getElementById('info-vehicle-code').innerText = v.vehicle_code;
        document.getElementById('info-vehicle-type').innerText = v.type;
        document.getElementById('info-plate').innerText = 'Plate: ' + v.plate_number;
        
        const badge = document.getElementById('info-status-badge');
        badge.innerText = v.status;
        badge.className = 'status-badge ' + (v.status === 'Active' ? 'active' : 'maintenance');

        document.getElementById('info-driver').innerText = v.driver_name;
        document.getElementById('info-emp-id').innerText = v.employee_id;
        const origin = selectedDispatch?.origin || v.origin;
        const destination = selectedDispatch?.destination || v.destination;
        document.getElementById('info-trip-route').innerText = `${origin || '—'} → ${destination || '—'}`;
        document.getElementById('info-speed').innerText = v.speed + ' km/h';
        
        document.getElementById('info-fuel-bar').style.width = v.fuel_level + '%';
        document.getElementById('info-fuel-val').innerText = v.fuel_level + '%';

        document.getElementById('info-location').innerText = hasValidCoordinates(v)
            ? `${Number(v.latitude).toFixed(4)}, ${Number(v.longitude).toFixed(4)} (${origin || '—'})`
            : `No live GPS fix (${origin || '—'})`;
        document.getElementById('info-destination').innerText = destination || '—';
        document.getElementById('info-start-time').innerText = v.trip_start_time || 'Not started';

        const routePill = document.getElementById('info-route-status');
        routePill.innerText = v.route_color === 'red' ? 'Critical Delay' : (v.route_color === 'yellow' ? 'Delayed' : 'On Time');
        routePill.className = 'status-pill-lg ' + v.route_color;

        // Fetch fresh ETA details for active trip
        if (updateEta && tripId) {
            fetch(apiBasePath + `/trip/${tripId}/eta`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.eta) {
                        if (selectedRouteIndex === 0) {
                            document.getElementById('eta-dist').innerText = data.eta.remaining_distance_km + ' km';
                            document.getElementById('eta-time').innerText = data.eta.remaining_time_formatted;
                            document.getElementById('eta-arrival').innerText = data.eta.expected_arrival_time;
                        }

                        // Check arrival threshold
                        if (data.eta.remaining_distance_km <= 0.05) {
                            document.getElementById('arrival-alert-box').style.display = 'block';
                        } else {
                            document.getElementById('arrival-alert-box').style.display = 'none';
                        }
                    }
                });
        }
    }

    function loadTripRoute(tripId) {
        const requestSequence = ++routeRequestSequence;
        fetch(apiBasePath + `/trip/${tripId}/route`)
            .then(res => res.json())
            .then(data => {
                if (requestSequence !== routeRequestSequence) return;
                if (data.success && data.trip) {
                    const t = data.trip;

                    const routes = Array.isArray(t.routes)
                        ? t.routes.map((route, index) => normalizeRouteOption(route, index)).filter(Boolean)
                        : [];
                    const primaryWaypoints = normalizeRouteOption({ waypoints: t.waypoints }, 0);
                    if (routes.length === 0 && primaryWaypoints) {
                        routes.unshift({
                            ...primaryWaypoints,
                            route_number: 1,
                            is_primary: true
                        });
                    }

                    setTripEndpointMarkers(t.origin_coords, t.dest_coords);
                    drawRoutePolylines(routes.slice(0, 3), t.route_color, t.eta);
                }
            })
            .catch(err => {
                if (requestSequence === routeRequestSequence) {
                    console.error('Error fetching route:', err);
                }
            });
    }

    function normalizeRouteOption(route, index) {
        if (!route || typeof route !== 'object' || !Array.isArray(route.waypoints)) return null;

        const waypoints = route.waypoints.filter(point => {
            if (!point || point.lat === null || point.lat === undefined || point.lng === null || point.lng === undefined) return false;
            const lat = Number(point.lat);
            const lng = Number(point.lng);
            return Number.isFinite(lat) && Number.isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
        });
        if (waypoints.length < 2) return null;

        return {
            ...route,
            route_number: route.route_number || index + 1,
            route_index: Number.isInteger(Number(route.route_index)) ? Number(route.route_index) : index,
            waypoints
        };
    }

    function clearRouteLayers() {
        routePolylines.forEach(polyline => {
            if (map && map.hasLayer(polyline)) map.removeLayer(polyline);
        });
        routePolylines = [];

        if (activeTrafficPolyline && map && map.hasLayer(activeTrafficPolyline)) {
            map.removeLayer(activeTrafficPolyline);
        }
        activeTrafficPolyline = null;
        activeRoutePolyline = null;
    }

    function clearRouteDisplay() {
        routeRequestSequence++;
        clearRouteLayers();
        clearTripEndpointMarkers();
        routeOptions = [];
        selectedRouteIndex = 0;

        const panel = document.getElementById('route-selection-panel');
        const list = document.getElementById('route-options');
        const count = document.getElementById('route-selection-count');
        if (list) list.replaceChildren();
        if (count) count.textContent = '';
        if (panel) panel.hidden = true;
    }

    function clearTripEndpointMarkers() {
        [activeOriginMarker, activeDestinationMarker].forEach(marker => {
            if (marker && map && map.hasLayer(marker)) map.removeLayer(marker);
        });
        activeOriginMarker = null;
        activeDestinationMarker = null;
    }

    function createTripEndpointMarker(coordinates, label, title) {
        if (!Array.isArray(coordinates) || coordinates.length < 2 ||
            coordinates[0] === null || coordinates[1] === null) return null;

        const latitude = Number(coordinates[0]);
        const longitude = Number(coordinates[1]);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude) ||
            Math.abs(latitude) > 90 || Math.abs(longitude) > 180) return null;

        const icon = L.divIcon({
            html: `<div class="route-destination-pin"><span>${label}</span></div>`,
            className: 'route-destination-icon',
            iconSize: [40, 46],
            iconAnchor: [20, 42],
            popupAnchor: [0, -40]
        });

        const marker = L.marker([latitude, longitude], {
            icon: icon,
            title: title,
            alt: title,
            zIndexOffset: 1000
        }).addTo(map);
        marker.bindTooltip(title, { direction: 'top', offset: [0, -10] });
        return marker;
    }

    function setTripEndpointMarkers(originCoords, destinationCoords) {
        clearTripEndpointMarkers();
        activeOriginMarker = createTripEndpointMarker(originCoords, 'A', 'Trip origin');
        activeDestinationMarker = createTripEndpointMarker(destinationCoords, 'B', 'Trip destination');
    }

    function formatRouteDistance(route) {
        const distance = Number(route.distance_km);
        return Number.isFinite(distance) ? `${distance.toFixed(1)} km` : 'Distance unavailable';
    }

    function formatRouteTime(route) {
        if (typeof route.formatted_travel_time === 'string' && route.formatted_travel_time.trim()) {
            return route.formatted_travel_time;
        }
        const minutes = Number(route.travel_time_mins);
        return Number.isFinite(minutes) ? `${Math.round(minutes)} mins` : 'Travel time unavailable';
    }

    function renderRouteOptions() {
        const panel = document.getElementById('route-selection-panel');
        const list = document.getElementById('route-options');
        const count = document.getElementById('route-selection-count');
        if (!panel || !list || !count) return;

        list.replaceChildren();
        panel.hidden = routeOptions.length === 0;
        count.textContent = `${routeOptions.length} ${routeOptions.length === 1 ? 'route' : 'routes'}`;

        routeOptions.forEach((route, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'route-option';
            button.dataset.routePosition = String(index);
            button.setAttribute('aria-pressed', index === selectedRouteIndex ? 'true' : 'false');

            const label = document.createElement('span');
            label.className = 'route-option-label';
            label.textContent = route.is_primary ? 'Primary Route' : `Route ${route.route_number || index + 1}`;

            const summary = document.createElement('span');
            summary.className = 'route-option-summary';
            summary.textContent = `${formatRouteDistance(route)} • ${formatRouteTime(route)}`;

            button.append(label, summary);
            button.addEventListener('click', () => selectRoute(index));
            list.appendChild(button);
        });
    }

    function selectRoute(routePosition, preserveLiveEta = false) {
        const route = routeOptions[routePosition];
        if (!route) return;

        selectedRouteIndex = routePosition;
        const selectedColor = activeRouteColor === 'red' ? '#e74c3c' : (activeRouteColor === 'yellow' ? '#f39c12' : '#2ec4b6');

        routePolylines.forEach((polyline, index) => {
            const isSelected = index === routePosition;
            polyline.setStyle({
                color: isSelected ? selectedColor : (index === 1 ? '#77858d' : '#9aa5aa'),
                weight: isSelected ? 7 : 4,
                opacity: isSelected ? 0.95 : 0.48,
                dashArray: isSelected ? null : (index === 1 ? '7, 7' : '2, 7')
            });
            if (isSelected) polyline.bringToFront();
        });

        activeRoutePolyline = routePolylines[routePosition] || null;
        document.querySelectorAll('.route-option').forEach((button, index) => {
            const isSelected = index === routePosition;
            button.classList.toggle('is-selected', isSelected);
            button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });

        if (!preserveLiveEta) {
            const distance = Number(route.distance_km);
            if (Number.isFinite(distance)) {
                document.getElementById('eta-dist').textContent = `${distance.toFixed(1)} km`;
            }

            const minutes = Number(route.travel_time_mins);
            if (Number.isFinite(minutes)) {
                document.getElementById('eta-time').textContent = formatRouteTime(route);
                const arrival = new Date(Date.now() + Math.round(minutes) * 60000);
                document.getElementById('eta-arrival').textContent = arrival.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
            }
        }

        if (activeRoutePolyline) {
            const bounds = activeRoutePolyline.getBounds();
            const selectedVehicle = fleetData.find(vehicle => Number(vehicle.id) === activeVehicleId);
            if (selectedVehicle && hasValidCoordinates(selectedVehicle)) {
                bounds.extend([Number(selectedVehicle.latitude), Number(selectedVehicle.longitude)]);
            }
            map.fitBounds(bounds, { padding: [40, 40] });
        }
    }

    function drawRoutePolylines(routes, routeColor, liveEta = null) {
        clearRouteLayers();
        routeOptions = routes;
        activeRouteColor = routeColor || 'green';

        routeOptions.forEach((route, index) => {
            const latLngs = route.waypoints.map(point => [Number(point.lat), Number(point.lng)]);
            const polyline = L.polyline(latLngs, {
                color: index === 1 ? '#77858d' : '#9aa5aa',
                weight: 4,
                opacity: 0.48,
                dashArray: index === 1 ? '7, 7' : '2, 7'
            }).addTo(map);
            polyline.on('click', () => selectRoute(index));
            routePolylines.push(polyline);
        });

        renderRouteOptions();
        if (routeOptions.length > 0) {
            selectRoute(0, true);
            if (liveEta) {
                document.getElementById('eta-dist').textContent = `${liveEta.remaining_distance_km} km`;
                document.getElementById('eta-time').textContent = liveEta.remaining_time_formatted;
                document.getElementById('eta-arrival').textContent = liveEta.expected_arrival_time;
            }
        }
    }

    function loadActiveDispatches() {
        fetch(activeDispatchesUrl, { headers: { Accept: 'application/json' } })
            .then(response => {
                if (!response.ok) throw new Error('Unable to load active dispatches.');
                return response.json();
            })
            .then(data => {
                if (!data.success || !Array.isArray(data.dispatches)) {
                    throw new Error('Invalid active dispatch response.');
                }
                activeDispatches = data.dispatches;
                if (selectedDispatchData) {
                    selectedDispatchData = activeDispatches.find(
                        dispatch => dispatch.dispatch_no === selectedDispatchData.dispatch_no
                    ) || null;
                }
                activeDispatchesLoaded = true;
                renderActiveDispatches();
            })
            .catch(error => {
                console.error('Error fetching active dispatches:', error);
                const container = document.getElementById('dispatch-list-container');
                if (container && activeDispatches.length === 0) {
                    container.textContent = 'Unable to load active dispatches.';
                }
            });
    }

    function renderActiveDispatches() {
        const container = document.getElementById('dispatch-list-container');
        if (!container || !activeDispatchesLoaded) return;
        container.replaceChildren();

        const count = document.getElementById('active-dispatch-count');
        if (count) count.textContent = String(activeDispatches.length);

        const visibleDispatches = activeDispatches.filter(dispatch => dispatchMatchesSearch(dispatch, currentSearchQuery));
        if (visibleDispatches.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'dispatch-empty';
            empty.style.cssText = 'padding:1rem 0.5rem;text-align:center;color:var(--muted,#6c7a93);font-size:0.85rem;';
            empty.textContent = activeDispatches.length > 0 ? 'No active dispatches match your search.' : 'No active dispatches.';
            container.appendChild(empty);
            return;
        }

        visibleDispatches.forEach(dispatch => {
            const vehicleId = Number(dispatch.vehicle_id);
            const vehicleIsLoaded = fleetData.some(vehicle => Number(vehicle.id) === vehicleId);
            const item = document.createElement('div');
            item.className = `dispatch-item${activeVehicleId === vehicleId ? ' selected' : ''}`;
            item.setAttribute('role', 'button');
            item.setAttribute('tabindex', vehicleIsLoaded ? '0' : '-1');
            item.setAttribute('aria-disabled', vehicleIsLoaded ? 'false' : 'true');
            item.setAttribute('aria-pressed', activeVehicleId === vehicleId ? 'true' : 'false');
            item.addEventListener('click', () => {
                if (vehicleIsLoaded) selectVehicle(vehicleId, dispatch);
            });
            item.addEventListener('keydown', event => {
                if (vehicleIsLoaded && (event.key === 'Enter' || event.key === ' ')) {
                    event.preventDefault();
                    selectVehicle(vehicleId, dispatch);
                }
            });

            const icon = document.createElement('span');
            icon.className = 'dispatch-icon green';
            icon.textContent = '🚚';

            const info = document.createElement('div');
            info.className = 'dispatch-info';
            const heading = document.createElement('h4');
            heading.textContent = dispatch.dispatch_no || 'Dispatch';
            const driverAndVehicle = [
                [dispatch.driver_name, dispatch.employee_id].filter(Boolean).join(' · '),
                [dispatch.vehicle_code, dispatch.plate_number].filter(Boolean).join(' · '),
            ].filter(Boolean);
            const assignment = document.createElement('p');
            assignment.textContent = driverAndVehicle.join(' · ');
            const route = document.createElement('p');
            route.textContent = `${dispatch.origin || '—'} → ${dispatch.destination || '—'}`;
            info.append(heading, assignment, route);

            const status = document.createElement('div');
            status.className = 'dispatch-status';
            const dispatchBadge = document.createElement('span');
            dispatchBadge.className = 'badge-sm green';
            dispatchBadge.textContent = dispatch.status;
            status.appendChild(dispatchBadge);
            if (dispatch.trip_status) {
                const tripStatus = document.createElement('span');
                tripStatus.className = 'dispatch-trip-status';
                tripStatus.textContent = `Trip: ${dispatch.trip_status}`;
                status.appendChild(tripStatus);
            }

            item.append(icon, info, status);
            container.appendChild(item);
        });
    }

    function updateDashboardAnalytics() {
        fetch(apiBasePath + '/analytics/dashboard')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.analytics) {
                    const a = data.analytics;
                    const set = (id, value) => { const el = document.getElementById(id); if (el) el.innerText = value; };
                    set('stat-active-trips', a.active_trips);
                    set('stat-completed-trips', a.completed_trips);
                    set('stat-delayed-trips', a.delayed_trips);
                    set('stat-eta-accuracy', a.avg_eta_accuracy + '%');
                    set('stat-total-dist', a.total_distance_today_km + ' km');
                    set('stat-total-fuel', a.total_fuel_consumption_l + ' L');
                }
            });
    }

    function loadNotifications() {
        fetch(apiBasePath + '/notifications')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.notifications) {
                    const list = document.getElementById('notifications-list');
                    list.innerHTML = '';
                    document.getElementById('notif-count').innerText = data.notifications.length;

                    data.notifications.forEach(n => {
                        const el = document.createElement('div');
                        el.className = `notif-card ${n.severity}`;
                        el.innerHTML = `
                            <div class="notif-header">
                                <strong>${n.type}</strong>
                                <small>${n.created_at}</small>
                            </div>
                            <p>${n.message}</p>
                        `;
                        list.appendChild(el);
                    });
                }
            });
    }

    // Driver Mobile Geolocation Tracking API
    function toggleBrowserGeolocation() {
        const statusText = document.getElementById('gps-status-text');
        const toggleBtn = document.getElementById('btn-toggle-gps');
        const log = document.getElementById('gps-log-output');

        if (gpsWatchId !== null) {
            navigator.geolocation.clearWatch(gpsWatchId);
            gpsWatchId = null;
            statusText.innerText = 'Inactive';
            statusText.className = 'text-muted';
            toggleBtn.innerText = 'Start Live GPS Broadcast';
            log.innerText += '\n[LOG] Browser GPS Geolocation tracking stopped.';
            return;
        }

        if (!("geolocation" in navigator)) {
            alert("Geolocation is not supported by your browser.");
            return;
        }

        statusText.innerText = 'Broadcasting (5s Interval)';
        statusText.className = 'text-teal';
        toggleBtn.innerText = 'Stop GPS Broadcast';
        log.innerText += '\n[LOG] Initializing navigator.geolocation.watchPosition...';

        gpsWatchId = navigator.geolocation.watchPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const speed = Math.round(pos.coords.speed ? pos.coords.speed * 3.6 : 45.0);

                document.getElementById('mobile-lat').innerText = lat.toFixed(4);
                document.getElementById('mobile-lng').innerText = lng.toFixed(4);
                document.getElementById('mobile-speed').innerText = speed + ' km/h';

                sendLocationUpdate(lat, lng, speed);
            },
            (err) => {
                log.innerText += `\n[ERROR] Geolocation error: ${err.message}. Switching to simulated GPS transmission.`;
                simulateGpsBroadcast();
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    }

    function simulateGpsBroadcast() {
        const vehicleId = document.getElementById('mobile-vehicle-select').value;
        const v = fleetData.find(item => item.id == vehicleId) || fleetData[0];
        
        let lat = v ? v.latitude : null;
        let lng = v ? v.longitude : null;
        let speed = 48;

        if (!Number.isFinite(Number(lat)) || !Number.isFinite(Number(lng))) {
            const log = document.getElementById('gps-log-output');
            log.innerText += `\n[ERROR] No live coordinates are available for the selected vehicle yet.`;
            return;
        }

        sendLocationUpdate(lat, lng, speed);
    }

    function simulateVehicleMovementStep() {
        const vehicleId = parseInt(document.getElementById('mobile-vehicle-select').value);
        const v = fleetData.find(item => item.id === vehicleId);
        if (!v) return;

        // Move 0.005 deg (~500 meters) towards destination
        const newLat = v.latitude + 0.004;
        const newLng = v.longitude + 0.004;
        sendLocationUpdate(newLat, newLng, 52);
    }

    function simulateDestinationArrival() {
        const vehicleId = parseInt(document.getElementById('mobile-vehicle-select').value);
        const v = fleetData.find(item => item.id === vehicleId);
        if (!v || !v.active_trip_id) {
            const log = document.getElementById('gps-log-output');
            log.innerText += `\n[ERROR] No active trip found for the selected vehicle — cannot simulate destination arrival.`;
            return;
        }
        fetch(apiBasePath + `/trip/${v.active_trip_id}/route`)
            .then(res => res.json())
            .then(data => {
                const dest = data.trip && data.trip.dest_coords;
                if (Array.isArray(dest) && dest.length >= 2 && Number.isFinite(Number(dest[0]))) {
                    sendLocationUpdate(Number(dest[0]), Number(dest[1]), 0);
                }
            });
    }

    function sendLocationUpdate(lat, lng, speed) {
        const vehicleId = parseInt(document.getElementById('mobile-vehicle-select').value);
        const log = document.getElementById('gps-log-output');

        fetch(apiBasePath + '/location/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                vehicle_id: vehicleId,
                latitude: lat,
                longitude: lng,
                speed: speed,
                fuel_level: 86.5
            })
        })
        .then(res => res.json())
        .then(data => {
            const timeStr = new Date().toLocaleTimeString();
            log.innerText += `\n[${timeStr}] POST /api/location/update -> HTTP 200 OK | Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}, Status: ${data.arrival_monitoring.trip_status}`;
            log.scrollTop = log.scrollHeight;
            
            // Reload map
            loadFleetData();
        })
        .catch(err => {
            log.innerText += `\n[ERROR] Failed to send update: ${err}`;
        });
    }

    // Cross-system Integration Sync
    function triggerSystemSync(system) {
        const out = document.getElementById('integration-output');
        out.innerText = `[SYNC] Transmitting integration request to /api/integration/system for system: ${system}...`;

        fetch(apiBasePath + '/integration/system', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ system: system })
        })
        .then(res => res.json())
        .then(data => {
            out.innerText = JSON.stringify(data, null, 2);
        })
        .catch(err => {
            out.innerText = `[ERROR] Integration call failed: ${err}`;
        });
    }

    // Controls UI toggles
    function toggleDriverMobileModal() {
        const el = document.getElementById('driver-mobile-modal');
        el.style.display = el.style.display === 'none' ? 'flex' : 'none';
    }

    function toggleIntegrationModal() {
        const el = document.getElementById('integration-modal');
        el.style.display = el.style.display === 'none' ? 'flex' : 'none';
    }

    function toggleNotificationsDrawer() {
        const el = document.getElementById('notifications-drawer');
        el.classList.toggle('open');
    }

    function recenterMap() {
        const v = activeVehicleId ? fleetData.find(item => item.id === activeVehicleId)
            : fleetData.find(hasValidCoordinates);
        if (v && hasValidCoordinates(v)) {
            map.setView([Number(v.latitude), Number(v.longitude)], 12);
        }
    }

    function toggleFullscreenMap() {
        const elem = document.querySelector('.map-view-card');
        if (!document.fullscreenElement) {
            elem.requestFullscreen().catch(err => console.error(err));
        } else {
            document.exitFullscreen();
        }
    }

    function focusSelectedVehicleRoute() {
        if (activeRoutePolyline) {
            map.fitBounds(activeRoutePolyline.getBounds(), { padding: [50, 50] });
        }
    }
</script>
