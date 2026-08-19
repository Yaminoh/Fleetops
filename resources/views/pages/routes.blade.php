<!-- Leaflet.js CSS & FontAwesome Icons -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<div class="tracking-module-wrapper">
    <!-- Top Analytics Overview Bar -->
    <div class="tracking-analytics-grid">
        <div class="analytics-card">
            <div class="analytics-icon blue">🚚</div>
            <div>
                <p class="analytics-label">Active Trips</p>
                <h3 class="analytics-value" id="stat-active-trips">3</h3>
                <span class="analytics-sub text-teal">● Real-time tracking</span>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon green">🏁</div>
            <div>
                <p class="analytics-label">Completed Trips</p>
                <h3 class="analytics-value" id="stat-completed-trips">18</h3>
                <span class="analytics-sub">Today's total</span>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon yellow">⚠️</div>
            <div>
                <p class="analytics-label">Delayed Trips</p>
                <h3 class="analytics-value" id="stat-delayed-trips">1</h3>
                <span class="analytics-sub text-orange">Action recommended</span>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon teal">🎯</div>
            <div>
                <p class="analytics-label">Avg. ETA Accuracy</p>
                <h3 class="analytics-value" id="stat-eta-accuracy">97.4%</h3>
                <span class="analytics-sub text-teal">High precision</span>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon blue">📏</div>
            <div>
                <p class="analytics-label">Distance Today</p>
                <h3 class="analytics-value" id="stat-total-dist">345.2 km</h3>
                <span class="analytics-sub">Fleet mileage</span>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-icon orange">⛽</div>
            <div>
                <p class="analytics-label">Fuel Consumed</p>
                <h3 class="analytics-value" id="stat-total-fuel">100.8 L</h3>
                <span class="analytics-sub">Logistics cost tracked</span>
            </div>
        </div>
    </div>

    <!-- Main Module Control Bar -->
    <div class="tracking-control-bar">
        <div class="control-left">
            <h2>Real-Time Fleet Map</h2>
            <div class="live-pulse-badge">
                <span class="pulse-dot"></span> Auto-refreshing (5s)
            </div>
        </div>
        <div class="control-right">
            <button class="btn-secondary" onclick="toggleDriverMobileModal()">
                📱 Driver Mobile GPS Tracker
            </button>
            <button class="btn-secondary" onclick="toggleIntegrationModal()">
                🔗 System Integration
            </button>
            <button class="btn-primary" onclick="toggleNotificationsDrawer()">
                🔔 Live Alerts <span class="badge-count" id="notif-count">2</span>
            </button>
        </div>
    </div>

    <!-- Main Interactive Map & Info Split Screen -->
    <div class="map-container-grid">
        <!-- Interactive Leaflet Map Box -->
        <div class="map-view-card">
            <div class="map-header-tools">
                <div class="map-filters">
                    <button class="filter-pill active" onclick="filterFleet('all')">All Vehicles (4)</button>
                    <button class="filter-pill" onclick="filterFleet('Active')">Active (3)</button>
                    <button class="filter-pill" onclick="filterFleet('Maintenance')">Maintenance (1)</button>
                </div>
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
                            <span class="badge-code" id="info-vehicle-code">TRK-101</span>
                            <h3 id="info-vehicle-type">Heavy Cargo Truck</h3>
                            <p class="plate-text" id="info-plate">NKI-8821</p>
                        </div>
                        <div class="status-badge" id="info-status-badge">Active</div>
                    </div>

                    <hr class="divider" />

                    <!-- Live Vehicle Information List -->
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Driver Name</span>
                            <strong class="info-val" id="info-driver">Harvey Villarin</strong>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Employee ID</span>
                            <strong class="info-val" id="info-emp-id">DRV-1001</strong>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Speed</span>
                            <strong class="info-val text-teal" id="info-speed">48 km/h</strong>
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
                            <span class="info-val-sm" id="info-location">14.5995, 120.9842 (Port Area Pier 15)</span>
                        </div>
                        <div class="info-item full">
                            <span class="info-label">Destination</span>
                            <span class="info-val-highlight" id="info-destination">Quezon City Logistics Hub</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Trip Start Time</span>
                            <span class="info-val-sm" id="info-start-time">10:30 AM</span>
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

            <!-- Active Dispatches List Card -->
            <div class="panel-card shadow-sm margin-top-md">
                <div class="panel-header-sub">
                    <h3>Active Fleet Dispatches</h3>
                    <span class="badge-count" id="active-dispatch-count">3</span>
                </div>
                <div class="dispatch-list" id="dispatch-list-container">
                    <!-- Dynamic rendering -->
                </div>
            </div>
        </div>
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
                    <option value="1">TRK-101 (Harvey Villarin - Cargo Truck)</option>
                    <option value="2">TRK-102 (Reybie Reforsado - Refrigerated)</option>
                    <option value="3">TRK-103 (Erwin Cober - Container Hauler)</option>
                </select>
            </div>

            <div class="gps-simulator-box margin-top-md">
                <div class="gps-status-header">
                    <span>GPS Status: <strong id="gps-status-text" class="text-teal">Inactive</strong></span>
                    <button id="btn-toggle-gps" class="btn-primary btn-sm" onclick="toggleBrowserGeolocation()">Start Live GPS Broadcast</button>
                </div>
                <div class="gps-metrics-grid margin-top-sm">
                    <div><small>Latitude:</small> <span id="mobile-lat">14.5995</span></div>
                    <div><small>Longitude:</small> <span id="mobile-lng">120.9842</span></div>
                    <div><small>Speed:</small> <span id="mobile-speed">45 km/h</span></div>
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
    const basePath = "<?= $dashboard['basePath'] ?>";
    let map = null;
    let vehicleMarkers = {};
    let activeRoutePolyline = null;
    let activeTrafficPolyline = null;
    let activeVehicleId = null;
    let fleetData = [];
    let refreshInterval = null;
    let gpsWatchId = null;
    let simulatedGpsInterval = null;

    document.addEventListener('DOMContentLoaded', function () {
        initLeafletMap();
        loadFleetData();
        
        // Auto-refresh vehicle locations every 5 seconds without page reload
        refreshInterval = setInterval(loadFleetData, 5000);
    });

    function initLeafletMap() {
        // Center on Metro Manila / Philippines Fleet Area
        map = L.map('fleet-map', {
            center: [14.5995, 120.9842],
            zoom: 12,
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
        fetch(basePath + '/api/vehicles/live')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.vehicles) {
                    fleetData = data.vehicles;
                    renderFleetMarkers(data.vehicles);
                    renderDispatchList(data.vehicles);
                    updateDashboardAnalytics();
                    loadNotifications();

                    if (activeVehicleId) {
                        const activeV = fleetData.find(v => v.id === activeVehicleId);
                        if (activeV) {
                            displayVehicleDetails(activeV);
                        }
                    }
                }
            })
            .catch(err => console.error('Error fetching live vehicle data:', err));
    }

    function renderFleetMarkers(vehicles) {
        vehicles.forEach(v => {
            const latLng = [v.latitude, v.longitude];
            const icon = createTruckIcon(v.vehicle_code, v.status, v.speed, v.route_color);

            if (vehicleMarkers[v.id]) {
                vehicleMarkers[v.id].setLatLng(latLng);
                vehicleMarkers[v.id].setIcon(icon);
            } else {
                const marker = L.marker(latLng, { icon: icon }).addTo(map);
                marker.on('click', () => selectVehicle(v.id));
                vehicleMarkers[v.id] = marker;
            }
        });
    }

    function selectVehicle(vehicleId) {
        activeVehicleId = vehicleId;
        const v = fleetData.find(item => item.id === vehicleId);
        if (!v) return;

        displayVehicleDetails(v);
        loadTripRoute(v.active_trip_id || 101);

        // Center map on marker
        map.panTo([v.latitude, v.longitude], { animate: true });
    }

    function displayVehicleDetails(v) {
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
        document.getElementById('info-speed').innerText = v.speed + ' km/h';
        
        document.getElementById('info-fuel-bar').style.width = v.fuel_level + '%';
        document.getElementById('info-fuel-val').innerText = v.fuel_level + '%';

        document.getElementById('info-location').innerText = `${v.latitude.toFixed(4)}, ${v.longitude.toFixed(4)} (${v.origin})`;
        document.getElementById('info-destination').innerText = v.destination;
        document.getElementById('info-start-time').innerText = v.trip_start_time || '10:30 AM';

        const routePill = document.getElementById('info-route-status');
        routePill.innerText = v.route_color === 'red' ? 'Critical Delay' : (v.route_color === 'yellow' ? 'Delayed' : 'On Time');
        routePill.className = 'status-pill-lg ' + v.route_color;

        // Fetch fresh ETA details for active trip
        if (v.active_trip_id) {
            fetch(basePath + `/api/trip/${v.active_trip_id}/eta`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.eta) {
                        document.getElementById('eta-dist').innerText = data.eta.remaining_distance_km + ' km';
                        document.getElementById('eta-time').innerText = data.eta.remaining_time_formatted;
                        document.getElementById('eta-arrival').innerText = data.eta.expected_arrival_time;

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
        fetch(basePath + `/api/trip/${tripId}/route`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.trip) {
                    const t = data.trip;
                    drawRoutePolylines(t.waypoints, t.route_color);
                }
            })
            .catch(err => console.error('Error fetching route:', err));
    }

    function drawRoutePolylines(waypoints, routeColor) {
        // Clear old polylines
        if (activeRoutePolyline) map.removeLayer(activeRoutePolyline);
        if (activeTrafficPolyline) map.removeLayer(activeTrafficPolyline);

        const latLngs = waypoints.map(w => [w.lat, w.lng]);
        const colorHex = routeColor === 'red' ? '#e74c3c' : (routeColor === 'yellow' ? '#f39c12' : '#2ec4b6');

        activeRoutePolyline = L.polyline(latLngs, {
            color: colorHex,
            weight: 6,
            opacity: 0.85,
            dashArray: routeColor === 'yellow' ? '8, 8' : null
        }).addTo(map);

        map.fitBounds(activeRoutePolyline.getBounds(), { padding: [40, 40] });
    }

    function renderDispatchList(vehicles) {
        const container = document.getElementById('dispatch-list-container');
        container.innerHTML = '';

        let activeCount = 0;
        vehicles.forEach(v => {
            if (v.status !== 'Maintenance') activeCount++;

            const item = document.createElement('div');
            item.className = `dispatch-item ${activeVehicleId === v.id ? 'selected' : ''}`;
            item.onclick = () => selectVehicle(v.id);

            item.innerHTML = `
                <div class="dispatch-icon ${v.route_color}">🚚</div>
                <div class="dispatch-info">
                    <h4>${v.vehicle_code} • ${v.driver_name}</h4>
                    <p>To ${v.destination} • Speed: ${v.speed} km/h</p>
                </div>
                <div class="dispatch-status">
                    <span class="badge-sm ${v.route_color}">${v.route_color === 'red' ? 'Delay' : (v.route_color === 'yellow' ? 'Slow' : 'Live')}</span>
                </div>
            `;
            container.appendChild(item);
        });

        document.getElementById('active-dispatch-count').innerText = activeCount;
    }

    function updateDashboardAnalytics() {
        fetch(basePath + '/api/analytics/dashboard')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.analytics) {
                    const a = data.analytics;
                    document.getElementById('stat-active-trips').innerText = a.active_trips;
                    document.getElementById('stat-completed-trips').innerText = a.completed_trips;
                    document.getElementById('stat-delayed-trips').innerText = a.delayed_trips;
                    document.getElementById('stat-eta-accuracy').innerText = a.avg_eta_accuracy + '%';
                    document.getElementById('stat-total-dist').innerText = a.total_distance_today_km + ' km';
                    document.getElementById('stat-total-fuel').innerText = a.total_fuel_consumption_l + ' L';
                }
            });
    }

    function loadNotifications() {
        fetch(basePath + '/api/notifications')
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
        
        let lat = v ? v.latitude : 14.5995;
        let lng = v ? v.longitude : 120.9842;
        let speed = 48;

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
        // Destination of Trip 101 is QC Hub: 14.6500, 121.0300
        sendLocationUpdate(14.6500, 121.0300, 0);
    }

    function sendLocationUpdate(lat, lng, speed) {
        const vehicleId = parseInt(document.getElementById('mobile-vehicle-select').value);
        const log = document.getElementById('gps-log-output');

        fetch(basePath + '/api/location/update', {
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

        fetch(basePath + '/api/integration/system', {
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

    function filterFleet(status) {
        document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
        event.target.classList.add('active');

        if (status === 'all') {
            renderFleetMarkers(fleetData);
        } else {
            const filtered = fleetData.filter(v => v.status === status);
            renderFleetMarkers(filtered);
        }
    }

    function recenterMap() {
        map.setView([14.5995, 120.9842], 12);
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
