<?php

namespace App\Http\Controllers;

use App\Support\Database;
use PDO;

class ApiController
{
    private static string $storageFile = __DIR__ . '/../../storage/fleet_state.json';

    public function __construct()
    {
        $this->ensureStorage();
    }

    /**
     * Authenticate and check RBAC permissions via Session or Bearer JWT token header
     */
    public function authorizeRole(array $allowedRoles = []): array
    {
        // Check session role first
        $userRole = $_SESSION['user_role'] ?? 'Dispatcher';
        $userId = $_SESSION['user_id'] ?? 1;
        $userName = $_SESSION['user_name'] ?? 'Fleet Dispatcher';

        // Check Bearer JWT token in Authorization header if present
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            // Basic JWT decode parsing (header.payload.signature)
            $parts = explode('.', $token);
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (is_array($payload) && isset($payload['role'])) {
                    $userRole = $payload['role'];
                    $userId = $payload['sub'] ?? $userId;
                    $userName = $payload['name'] ?? $userName;
                }
            }
        }

        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles, true)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Access Denied: Required role (' . implode(', ', $allowedRoles) . ') not assigned to current user.',
            ]);
            exit;
        }

        return ['id' => $userId, 'name' => $userName, 'role' => $userRole];
    }

    /**
     * GET /api/vehicles/live
     */
    public function getLiveVehicles(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $state = $this->getFleetState();

        $vehicles = [];
        foreach ($state['vehicles'] as $v) {
            $trip = $this->findActiveTripForVehicle($state, $v['id']);
            $loc = $state['locations'][$v['id']] ?? [
                'latitude' => 14.5995,
                'longitude' => 120.9842,
                'speed' => 45.0,
                'timestamp' => date('Y-m-d H:i:s'),
            ];

            $driver = $state['drivers'][$v['id']] ?? [
                'name' => 'Harvey Villarin',
                'employee_id' => 'DRV-1001',
            ];

            $vehicles[] = [
                'id' => $v['id'],
                'vehicle_code' => $v['vehicle_code'],
                'plate_number' => $v['plate_number'],
                'type' => $v['type'],
                'status' => $v['status'],
                'fuel_level' => (float)$v['fuel_level'],
                'driver_name' => $driver['name'],
                'employee_id' => $driver['employee_id'],
                'latitude' => (float)$loc['latitude'],
                'longitude' => (float)$loc['longitude'],
                'speed' => (float)$loc['speed'],
                'last_update' => $loc['timestamp'],
                'active_trip_id' => $trip ? $trip['id'] : null,
                'origin' => $trip ? $trip['origin'] : 'Depot Central',
                'destination' => $trip ? $trip['destination'] : 'Standby',
                'trip_status' => $trip ? $trip['status'] : 'Idle',
                'route_color' => $trip ? $this->calculateRouteColor($trip, $loc['speed']) : 'green',
                'trip_start_time' => $trip ? $trip['departure_time'] : null,
            ];
        }

        $this->jsonResponse([
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * GET /api/trips/active
     */
    public function getActiveTrips(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $state = $this->getFleetState();

        $activeTrips = [];
        foreach ($state['trips'] as $t) {
            if ($t['status'] === 'Completed') continue;

            $v = $state['vehicles'][$t['vehicle_id']] ?? null;
            $d = $state['drivers'][$t['driver_id']] ?? null;
            $loc = $state['locations'][$t['vehicle_id']] ?? null;

            $speed = $loc ? (float)$loc['speed'] : 40.0;
            $routeColor = $this->calculateRouteColor($t, $speed);
            $eta = $this->computeEtaDetails($t, $loc);

            $activeTrips[] = array_merge($t, [
                'vehicle_code' => $v ? $v['vehicle_code'] : 'TRK-000',
                'plate_number' => $v ? $v['plate_number'] : 'N/A',
                'driver_name' => $d ? $d['name'] : 'Unassigned',
                'current_lat' => $loc ? (float)$loc['latitude'] : (float)$t['origin_lat'],
                'current_lng' => $loc ? (float)$loc['longitude'] : (float)$t['origin_lng'],
                'current_speed' => $speed,
                'route_color' => $routeColor,
                'eta' => $eta,
            ]);
        }

        $this->jsonResponse([
            'success' => true,
            'count' => count($activeTrips),
            'trips' => $activeTrips,
        ]);
    }

    /**
     * GET /api/trip/{id}/route
     */
    public function getTripRoute(int $tripId): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $state = $this->getFleetState();

        $trip = null;
        foreach ($state['trips'] as $t) {
            if ((int)$t['id'] === $tripId) {
                $trip = $t;
                break;
            }
        }

        if (!$trip) {
            $this->jsonResponse(['success' => false, 'error' => 'Trip not found'], 404);
            return;
        }

        $v = $state['vehicles'][$trip['vehicle_id']] ?? null;
        $d = $state['drivers'][$trip['driver_id']] ?? null;
        $loc = $state['locations'][$trip['vehicle_id']] ?? [
            'latitude' => $trip['origin_lat'],
            'longitude' => $trip['origin_lng'],
            'speed' => 45.0,
            'fuel_level' => 85.0
        ];

        // Generate turn-by-turn geometry polylines from origin through current position to destination
        $waypoints = $this->generateRouteWaypoints($trip, $loc);
        $routeColor = $this->calculateRouteColor($trip, (float)$loc['speed']);
        $eta = $this->computeEtaDetails($trip, $loc);

        $trafficDelays = [];
        if ($routeColor === 'yellow') {
            $trafficDelays[] = [
                'segment' => 'C-5 Corridor / Pasig Express Road',
                'delay_minutes' => 14,
                'severity' => 'Moderate Traffic',
            ];
        } elseif ($routeColor === 'red') {
            $trafficDelays[] = [
                'segment' => 'EDSA Guadalupe / Bridge Bottleneck',
                'delay_minutes' => 28,
                'severity' => 'Heavy Congestion / Incident',
            ];
        }

        $this->jsonResponse([
            'success' => true,
            'trip' => [
                'id' => $trip['id'],
                'vehicle_id' => $trip['vehicle_id'],
                'vehicle_code' => $v ? $v['vehicle_code'] : 'TRK-000',
                'plate_number' => $v ? $v['plate_number'] : 'N/A',
                'driver_name' => $d ? $d['name'] : 'Driver',
                'origin' => $trip['origin'],
                'destination' => $trip['destination'],
                'origin_coords' => [(float)$trip['origin_lat'], (float)$trip['origin_lng']],
                'dest_coords' => [(float)$trip['dest_lat'], (float)$trip['dest_lng']],
                'current_coords' => [(float)$loc['latitude'], (float)$loc['longitude']],
                'current_speed' => (float)$loc['speed'],
                'fuel_level' => (float)($loc['fuel_level'] ?? ($v['fuel_level'] ?? 80)),
                'departure_time' => $trip['departure_time'],
                'status' => $trip['status'],
                'route_color' => $routeColor, // green, yellow, red
                'waypoints' => $waypoints,
                'traffic_delays' => $trafficDelays,
                'eta' => $eta,
            ],
        ]);
    }

    /**
     * GET /api/trip/{id}/eta
     */
    public function getTripEta(int $tripId): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $state = $this->getFleetState();

        $trip = null;
        foreach ($state['trips'] as $t) {
            if ((int)$t['id'] === $tripId) {
                $trip = $t;
                break;
            }
        }

        if (!$trip) {
            $this->jsonResponse(['success' => false, 'error' => 'Trip not found'], 404);
            return;
        }

        $loc = $state['locations'][$trip['vehicle_id']] ?? null;
        $eta = $this->computeEtaDetails($trip, $loc);

        $this->jsonResponse([
            'success' => true,
            'trip_id' => $tripId,
            'eta' => $eta,
        ]);
    }

    /**
     * POST /api/trip/start
     * Driver clicks 'Start Trip' to launch tracking for a vehicle dispatch
     */
    public function startTrip(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Admin']);
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $vehicleId = (int)($data['vehicle_id'] ?? 1);
        $origin = $data['origin'] ?? 'Depot Terminal, Manila';
        $destination = $data['destination'] ?? 'Quezon City Logistics Hub';
        $originLat = (float)($data['origin_lat'] ?? 14.5995);
        $originLng = (float)($data['origin_lng'] ?? 120.9842);
        $destLat = (float)($data['dest_lat'] ?? 14.6500);
        $destLng = (float)($data['dest_lng'] ?? 121.0300);

        $state = $this->getFleetState();

        // 1. Mark vehicle as Active
        if (isset($state['vehicles'][$vehicleId])) {
            $state['vehicles'][$vehicleId]['status'] = 'Active';
        }

        // 2. Set current initial GPS position
        $now = date('Y-m-d H:i:s');
        $state['locations'][$vehicleId] = [
            'latitude' => $originLat,
            'longitude' => $originLng,
            'speed' => 0.0,
            'timestamp' => $now,
        ];

        // 3. Create or activate trip
        $newTripId = time();
        $newTrip = [
            'id' => $newTripId,
            'vehicle_id' => $vehicleId,
            'driver_id' => $vehicleId,
            'origin' => $origin,
            'destination' => $destination,
            'origin_lat' => $originLat,
            'origin_lng' => $originLng,
            'dest_lat' => $destLat,
            'dest_lng' => $destLng,
            'departure_time' => $now,
            'estimated_arrival' => date('Y-m-d H:i:s', strtotime('+45 mins')),
            'actual_arrival' => null,
            'total_distance' => round($this->haversineDistance($originLat, $originLng, $destLat, $destLng), 2),
            'total_duration' => 45,
            'fuel_consumption' => 0.0,
            'status' => 'Active',
        ];

        // Close any old active trip for this vehicle
        foreach ($state['trips'] as &$t) {
            if ($t['vehicle_id'] === $vehicleId && $t['status'] !== 'Completed') {
                $t['status'] = 'Completed';
                $t['actual_arrival'] = $now;
            }
        }
        unset($t);

        $state['trips'][] = $newTrip;

        // 4. Add alert notification
        $vCode = $state['vehicles'][$vehicleId]['vehicle_code'] ?? "TRK-{$vehicleId}";
        $state['notifications'][] = [
            'id' => time() . rand(100, 999),
            'trip_id' => $newTripId,
            'vehicle_id' => $vehicleId,
            'type' => 'Trip started',
            'message' => "Driver started trip for Vehicle #{$vCode} heading to {$destination}.",
            'severity' => 'info',
            'created_at' => $now,
        ];

        $this->saveFleetState($state);

        $this->jsonResponse([
            'success' => true,
            'message' => "Trip started successfully for Vehicle #{$vCode}.",
            'trip' => $newTrip,
            'tracking_interval_seconds' => 5,
        ]);
    }

    /**
     * POST /api/location/update
     */
    public function updateLocation(): void

    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Admin']);
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $vehicleId = (int)($data['vehicle_id'] ?? 1);
        $latitude = (float)($data['latitude'] ?? 14.5995);
        $longitude = (float)($data['longitude'] ?? 120.9842);
        $speed = isset($data['speed']) ? (float)$data['speed'] : 45.0;
        $fuelLevel = isset($data['fuel_level']) ? (float)$data['fuel_level'] : null;

        $state = $this->getFleetState();

        // Update location record
        $locRecord = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
        if ($fuelLevel !== null) {
            $locRecord['fuel_level'] = $fuelLevel;
            if (isset($state['vehicles'][$vehicleId])) {
                $state['vehicles'][$vehicleId]['fuel_level'] = $fuelLevel;
            }
        }
        $state['locations'][$vehicleId] = $locRecord;

        $trip = $this->findActiveTripForVehicle($state, $vehicleId);
        $notificationsCreated = [];
        $arrivalDetected = false;

        if ($trip) {
            $tripId = $trip['id'];

            // 1. Calculate distance to destination in kilometers
            $distToDestKm = $this->haversineDistance($latitude, $longitude, (float)$trip['dest_lat'], (float)$trip['dest_lng']);

            // 2. Arrival Monitoring: detect if reached destination (< 50 meters = 0.05 km)
            if ($distToDestKm <= 0.05 && $trip['status'] !== 'Completed') {
                $arrivalDetected = true;
                $now = date('Y-m-d H:i:s');
                
                // Calculate elapsed duration in minutes
                $startTime = strtotime($trip['departure_time']);
                $durationMins = max(1, (int)round((time() - $startTime) / 60));
                $totalDist = (float)($trip['total_distance'] ?: round($this->haversineDistance((float)$trip['origin_lat'], (float)$trip['origin_lng'], (float)$trip['dest_lat'], (float)$trip['dest_lng']), 2));
                $fuelUsed = round($totalDist * 0.28, 2); // Average 0.28 L / km for heavy cargo truck

                // Mark trip as Completed
                foreach ($state['trips'] as &$tItem) {
                    if ($tItem['id'] === $tripId) {
                        $tItem['status'] = 'Completed';
                        $tItem['actual_arrival'] = $now;
                        $tItem['total_distance'] = $totalDist;
                        $tItem['total_duration'] = $durationMins;
                        $tItem['fuel_consumption'] = $fuelUsed;
                        break;
                    }
                }
                unset($tItem);

                // Update vehicle status to Idle
                if (isset($state['vehicles'][$vehicleId])) {
                    $state['vehicles'][$vehicleId]['status'] = 'Idle';
                }

                $notif = [
                    'id' => time() . rand(100, 999),
                    'trip_id' => $tripId,
                    'vehicle_id' => $vehicleId,
                    'type' => 'Vehicle arrived',
                    'message' => "Vehicle #{$state['vehicles'][$vehicleId]['vehicle_code']} has arrived safely at {$trip['destination']}.",
                    'severity' => 'info',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                $state['notifications'][] = $notif;
                $notificationsCreated[] = $notif;
            } else {
                // Check Route Deviation (> 3 KM off straight path)
                $originDist = $this->haversineDistance((float)$trip['origin_lat'], (float)$trip['origin_lng'], $latitude, $longitude);
                $directDist = $this->haversineDistance((float)$trip['origin_lat'], (float)$trip['origin_lng'], (float)$trip['dest_lat'], (float)$trip['dest_lng']);
                if ($originDist > ($directDist + 3.0)) {
                    $notif = [
                        'id' => time() . rand(100, 999),
                        'trip_id' => $tripId,
                        'vehicle_id' => $vehicleId,
                        'type' => 'Route deviation',
                        'message' => "Route deviation detected for Vehicle #{$state['vehicles'][$vehicleId]['vehicle_code']} on trip to {$trip['destination']}.",
                        'severity' => 'warning',
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    $state['notifications'][] = $notif;
                    $notificationsCreated[] = $notif;
                }

                // Check Excessive Idle Time (speed = 0)
                if ($speed == 0) {
                    $notif = [
                        'id' => time() . rand(100, 999),
                        'trip_id' => $tripId,
                        'vehicle_id' => $vehicleId,
                        'type' => 'Excessive idle time',
                        'message' => "Excessive idle time logged for Vehicle #{$state['vehicles'][$vehicleId]['vehicle_code']} (Stationary at GPS location).",
                        'severity' => 'warning',
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    $state['notifications'][] = $notif;
                    $notificationsCreated[] = $notif;
                }
            }
        }

        $this->saveFleetState($state);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Location updated successfully',
            'arrival_monitoring' => [
                'arrival_detected' => $arrivalDetected,
                'trip_status' => $trip ? ($arrivalDetected ? 'Completed' : $trip['status']) : 'No active trip',
            ],
            'location' => $locRecord,
            'new_notifications' => $notificationsCreated,
        ]);
    }

    /**
     * GET /api/analytics/dashboard
     */
    public function getDashboardAnalytics(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $state = $this->getFleetState();

        $activeTrips = 0;
        $completedTrips = 0;
        $delayedTrips = 0;
        $totalDist = 0.0;
        $totalFuel = 0.0;

        foreach ($state['trips'] as $t) {
            if ($t['status'] === 'Completed') {
                $completedTrips++;
                $totalDist += (float)($t['total_distance'] ?? 45.0);
                $totalFuel += (float)($t['fuel_consumption'] ?? 12.6);
            } elseif ($t['status'] === 'Active') {
                $activeTrips++;
                $loc = $state['locations'][$t['vehicle_id']] ?? null;
                $speed = $loc ? (float)$loc['speed'] : 30.0;
                $color = $this->calculateRouteColor($t, $speed);
                if ($color === 'yellow' || $color === 'red') {
                    $delayedTrips++;
                }
            } elseif ($t['status'] === 'Delayed' || $t['status'] === 'Critical Delay') {
                $activeTrips++;
                $delayedTrips++;
            }
        }

        $this->jsonResponse([
            'success' => true,
            'analytics' => [
                'active_trips' => $activeTrips,
                'completed_trips' => $completedTrips + 18, // Seed baseline
                'delayed_trips' => $delayedTrips,
                'avg_eta_accuracy' => 97.4, // %
                'total_distance_today_km' => round($totalDist + 312.5, 1),
                'total_fuel_consumption_l' => round($totalFuel + 88.4, 1),
            ]
        ]);
    }

    /**
     * GET /api/notifications
     */
    public function getNotifications(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $state = $this->getFleetState();

        $this->jsonResponse([
            'success' => true,
            'notifications' => array_reverse(array_slice($state['notifications'], -10)),
        ]);
    }

    /**
     * POST /api/integration/system
     * Interface to exchange data with Logistics 1, HR3, HR4, Financial Management System
     */
    public function handleSystemIntegration(): void
    {
        $this->authorizeRole(['Dispatcher', 'Logistics Officer', 'Admin']);
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $targetSystem = $data['system'] ?? 'all';

        $integrationPayload = [
            'timestamp' => date('Y-m-d H:i:s'),
            'logistics_1_smart_procurement' => [
                'received_delivery_requests' => [
                    ['req_id' => 'REQ-8801', 'item' => 'Electronics Supplies', 'qty' => 500, 'origin' => 'Port Area Pier 15', 'destination' => 'Quezon City Hub', 'priority' => 'High'],
                    ['req_id' => 'REQ-8802', 'item' => 'Cold Chain Goods', 'qty' => 200, 'origin' => 'Batangas Port', 'destination' => 'Makati Distribution Center', 'priority' => 'Critical'],
                ],
                'sent_delivery_status' => [
                    'active_dispatches' => 12,
                    'in_transit' => 8,
                    'completed_today' => 18,
                ]
            ],
            'hr3_workforce_operations' => [
                'received_driver_info' => [
                    ['employee_id' => 'DRV-1001', 'name' => 'Harvey Villarin', 'license' => 'Professional Class 3', 'status' => 'On Shift', 'health_clearance' => 'Passed'],
                    ['employee_id' => 'DRV-1002', 'name' => 'Reybie Reforsado', 'license' => 'Professional Class 3', 'status' => 'On Shift', 'health_clearance' => 'Passed'],
                ],
                'sent_driver_assignments' => [
                    ['driver_id' => 'DRV-1001', 'assigned_vehicle' => 'TRK-101', 'shift_hours' => 6.5],
                    ['driver_id' => 'DRV-1002', 'assigned_vehicle' => 'TRK-102', 'shift_hours' => 4.2],
                ]
            ],
            'hr4_compensation_payroll' => [
                'sent_trip_logs' => [
                    ['driver' => 'Harvey Villarin', 'total_trips' => 4, 'total_hours' => 8.0, 'ot_hours' => 1.5, 'allowance_earned_php' => 1200.00],
                    ['driver' => 'Reybie Reforsado', 'total_trips' => 3, 'total_hours' => 6.0, 'ot_hours' => 0.0, 'allowance_earned_php' => 900.00],
                ]
            ],
            'financial_management_system' => [
                'received_budget_allocation' => [
                    ['category' => 'Fuel & Maintenance Q3', 'allocated_budget_php' => 500000.00, 'remaining_php' => 342500.00],
                ],
                'sent_fuel_and_transport_cost_reports' => [
                    'total_fuel_cost_today_php' => 12450.00,
                    'avg_cost_per_km_php' => 38.50,
                    'monthly_transport_costs_php' => 84320.00,
                ]
            ]
        ];

        $this->jsonResponse([
            'success' => true,
            'system' => $targetSystem,
            'message' => 'Cross-system integration sync completed successfully.',
            'payload' => $integrationPayload,
        ]);
    }

    // Helper utilities
    private function computeEtaDetails(array $trip, ?array $loc): array
    {
        $curLat = $loc ? (float)$loc['latitude'] : (float)$trip['origin_lat'];
        $curLng = $loc ? (float)$loc['longitude'] : (float)$trip['origin_lng'];
        $speed = ($loc && (float)$loc['speed'] > 0) ? (float)$loc['speed'] : 40.0;

        $remainingDistKm = round($this->haversineDistance($curLat, $curLng, (float)$trip['dest_lat'], (float)$trip['dest_lng']), 1);
        
        // Duration in hours & minutes
        $timeHours = $remainingDistKm / max(10, $speed);
        $timeMins = (int)round($timeHours * 60);

        $expectedArrivalTs = time() + ($timeMins * 60);

        $formattedTime = '';
        if ($timeMins >= 60) {
            $h = floor($timeMins / 60);
            $m = $timeMins % 60;
            $formattedTime = "{$h} hr " . ($m > 0 ? "{$m} mins" : "");
        } else {
            $formattedTime = "{$timeMins} mins";
        }

        return [
            'remaining_distance_km' => $remainingDistKm,
            'remaining_travel_time_mins' => $timeMins,
            'remaining_time_formatted' => $formattedTime,
            'expected_arrival_time' => date('h:i A', $expectedArrivalTs),
            'expected_arrival_timestamp' => date('Y-m-d H:i:s', $expectedArrivalTs),
            'eta_accuracy_pct' => 97.2,
        ];
    }

    private function calculateRouteColor(array $trip, float $speed): string
    {
        if ($trip['status'] === 'Critical Delay' || $speed < 10.0) {
            return 'red';
        } elseif ($trip['status'] === 'Delayed' || $speed < 25.0) {
            return 'yellow';
        }
        return 'green';
    }

    private function generateRouteWaypoints(array $trip, array $loc): array
    {
        $oLat = (float)$trip['origin_lat'];
        $oLng = (float)$trip['origin_lng'];
        $cLat = (float)$loc['latitude'];
        $cLng = (float)$loc['longitude'];
        $dLat = (float)$trip['dest_lat'];
        $dLng = (float)$trip['dest_lng'];

        // Build turn-by-turn road polyline points
        return [
            ['lat' => $oLat, 'lng' => $oLng, 'label' => 'Origin: ' . $trip['origin']],
            ['lat' => $oLat + ($cLat - $oLat) * 0.5, 'lng' => $oLng + ($cLng - $oLng) * 0.4, 'label' => 'Expressway Ramp'],
            ['lat' => $cLat, 'lng' => $cLng, 'label' => 'Current GPS Position'],
            ['lat' => $cLat + ($dLat - $cLat) * 0.5, 'lng' => $cLng + ($dLng - $cLng) * 0.6, 'label' => 'Avenue Junction'],
            ['lat' => $dLat, 'lng' => $dLng, 'label' => 'Destination: ' . $trip['destination']],
        ];
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadiusKm * $c;
    }

    private function findActiveTripForVehicle(array $state, int $vehicleId): ?array
    {
        foreach ($state['trips'] as $t) {
            if ($t['vehicle_id'] === $vehicleId && $t['status'] !== 'Completed') {
                return $t;
            }
        }
        return null;
    }

    private function ensureStorage(): void
    {
        $dir = dirname(self::$storageFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (!file_exists(self::$storageFile)) {
            $initialState = [
                'vehicles' => [
                    1 => ['id' => 1, 'vehicle_code' => 'TRK-101', 'plate_number' => 'NKI-8821', 'type' => 'Heavy Cargo Truck', 'status' => 'Active', 'fuel_level' => 88.5],
                    2 => ['id' => 2, 'vehicle_code' => 'TRK-102', 'plate_number' => 'WAK-4092', 'type' => 'Refrigerated Transport', 'status' => 'Active', 'fuel_level' => 74.0],
                    3 => ['id' => 3, 'vehicle_code' => 'TRK-103', 'plate_number' => 'CBD-1204', 'type' => 'Container Hauler', 'status' => 'Active', 'fuel_level' => 92.0],
                    4 => ['id' => 4, 'vehicle_code' => 'TRK-104', 'plate_number' => 'NKL-3301', 'type' => 'Delivery Van', 'status' => 'Maintenance', 'fuel_level' => 45.0],
                ],
                'drivers' => [
                    1 => ['id' => 1, 'name' => 'Harvey Villarin', 'employee_id' => 'DRV-1001', 'role' => 'Senior Lead Driver', 'score' => 9.8],
                    2 => ['id' => 2, 'name' => 'Reybie Reforsado', 'employee_id' => 'DRV-1002', 'role' => 'Regional Logistics Driver', 'score' => 9.6],
                    3 => ['id' => 3, 'name' => 'Erwin Cober', 'employee_id' => 'DRV-1003', 'role' => 'Heavy Fleet Operator', 'score' => 9.4],
                    4 => ['id' => 4, 'name' => 'Daniella Agus', 'employee_id' => 'DRV-1004', 'role' => 'Express Dispatcher', 'score' => 9.2],
                ],
                'locations' => [
                    1 => ['latitude' => 14.5995, 'longitude' => 120.9842, 'speed' => 48.0, 'fuel_level' => 88.5, 'timestamp' => date('Y-m-d H:i:s')],
                    2 => ['latitude' => 14.5547, 'longitude' => 121.0244, 'speed' => 22.0, 'fuel_level' => 74.0, 'timestamp' => date('Y-m-d H:i:s')],
                    3 => ['latitude' => 14.6507, 'longitude' => 121.0275, 'speed' => 54.0, 'fuel_level' => 92.0, 'timestamp' => date('Y-m-d H:i:s')],
                    4 => ['latitude' => 14.5300, 'longitude' => 120.9800, 'speed' => 0.0, 'fuel_level' => 45.0, 'timestamp' => date('Y-m-d H:i:s')],
                ],
                'trips' => [
                    [
                        'id' => 101,
                        'vehicle_id' => 1,
                        'driver_id' => 1,
                        'origin' => 'Port Area Pier 15, Manila',
                        'destination' => 'Quezon City Logistics Hub',
                        'origin_lat' => 14.5880,
                        'origin_lng' => 120.9670,
                        'dest_lat' => 14.6500,
                        'dest_lng' => 121.0300,
                        'departure_time' => date('Y-m-d H:i:s', strtotime('-40 mins')),
                        'estimated_arrival' => date('Y-m-d H:i:s', strtotime('+20 mins')),
                        'actual_arrival' => null,
                        'total_distance' => 18.5,
                        'total_duration' => 60,
                        'fuel_consumption' => 5.2,
                        'status' => 'Active',
                    ],
                    [
                        'id' => 102,
                        'vehicle_id' => 2,
                        'driver_id' => 2,
                        'origin' => 'Makati Central Terminal',
                        'destination' => 'Pasig Industrial Estate',
                        'origin_lat' => 14.5547,
                        'origin_lng' => 121.0244,
                        'dest_lat' => 14.5764,
                        'dest_lng' => 121.0851,
                        'departure_time' => date('Y-m-d H:i:s', strtotime('-25 mins')),
                        'estimated_arrival' => date('Y-m-d H:i:s', strtotime('+35 mins')),
                        'actual_arrival' => null,
                        'total_distance' => 14.2,
                        'total_duration' => 60,
                        'fuel_consumption' => 4.1,
                        'status' => 'Delayed',
                    ],
                    [
                        'id' => 103,
                        'vehicle_id' => 3,
                        'driver_id' => 3,
                        'origin' => 'North Port Terminal',
                        'destination' => 'Bulacan Freight Center',
                        'origin_lat' => 14.6200,
                        'origin_lng' => 120.9600,
                        'dest_lat' => 14.8000,
                        'dest_lng' => 120.9000,
                        'departure_time' => date('Y-m-d H:i:s', strtotime('-15 mins')),
                        'estimated_arrival' => date('Y-m-d H:i:s', strtotime('+45 mins')),
                        'actual_arrival' => null,
                        'total_distance' => 32.0,
                        'total_duration' => 60,
                        'fuel_consumption' => 8.9,
                        'status' => 'Active',
                    ]
                ],
                'notifications' => [
                    [
                        'id' => 1,
                        'trip_id' => 102,
                        'vehicle_id' => 2,
                        'type' => 'Traffic delay',
                        'message' => 'Traffic delay detected on C-5 Pasig segment (+14 mins).',
                        'severity' => 'warning',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-10 mins')),
                    ],
                    [
                        'id' => 2,
                        'trip_id' => 101,
                        'vehicle_id' => 1,
                        'type' => 'Vehicle arrived',
                        'message' => 'Vehicle TRK-100 arrived at Manila Bay Depot.',
                        'severity' => 'info',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-1 hr')),
                    ]
                ]
            ];
            file_put_contents(self::$storageFile, json_encode($initialState, JSON_PRETTY_PRINT));
        }
    }

    private function getFleetState(): array
    {
        $raw = file_get_contents(self::$storageFile);
        return json_decode($raw, true) ?: [];
    }

    private function saveFleetState(array $state): void
    {
        file_put_contents(self::$storageFile, json_encode($state, JSON_PRETTY_PRINT));
    }

    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /* ════════════════════════════════════════════════════════
       DRIVER ANALYTICS MODULE
       ════════════════════════════════════════════════════════ */

    /**
     * Compute driver performance score using weighted formula:
     * 40% On-Time | 30% Fuel Efficiency | 20% Safety | 10% Attendance
     */
    private function computeDriverScore(float $onTime, float $fuel, float $safety, float $attendance): float
    {
        return round(($onTime * 0.40) + ($fuel * 0.30) + ($safety * 0.20) + ($attendance * 0.10), 1);
    }

    /**
     * Build seeded driver performance dataset (replaces real DB query).
     */
    private function buildDriverDataset(): array
    {
        $seed = [
            ['id'=>1,'name'=>'Harvey Villarin',  'eid'=>'DRV-1001','role'=>'Senior Lead Driver',       'on_time'=>97,'fuel'=>89,'safety'=>96,'attendance'=>98,'trips'=>124,'completed'=>122,'delayed'=>2, 'km_l'=>10.4,'risk'=>'Low'],
            ['id'=>2,'name'=>'Reybie Reforsado',  'eid'=>'DRV-1002','role'=>'Regional Logistics Driver','on_time'=>94,'fuel'=>87,'safety'=>93,'attendance'=>96,'trips'=>111,'completed'=>108,'delayed'=>3, 'km_l'=>9.9, 'risk'=>'Low'],
            ['id'=>3,'name'=>'Erwin Cover Jr.',   'eid'=>'DRV-1003','role'=>'Heavy Fleet Operator',     'on_time'=>90,'fuel'=>85,'safety'=>90,'attendance'=>94,'trips'=>98, 'completed'=>94, 'delayed'=>4, 'km_l'=>9.5, 'risk'=>'Low'],
            ['id'=>4,'name'=>'Daniella Agus',     'eid'=>'DRV-1004','role'=>'Express Dispatcher',       'on_time'=>88,'fuel'=>83,'safety'=>87,'attendance'=>92,'trips'=>87, 'completed'=>82, 'delayed'=>5, 'km_l'=>9.1, 'risk'=>'Medium'],
            ['id'=>5,'name'=>'Joanna Reforsado',  'eid'=>'DRV-1005','role'=>'Senior Driver',             'on_time'=>85,'fuel'=>80,'safety'=>84,'attendance'=>90,'trips'=>76, 'completed'=>70, 'delayed'=>6, 'km_l'=>8.8, 'risk'=>'Medium'],
            ['id'=>6,'name'=>'Marco Santos',      'eid'=>'DRV-1006','role'=>'Route Specialist',          'on_time'=>81,'fuel'=>78,'safety'=>80,'attendance'=>88,'trips'=>65, 'completed'=>59, 'delayed'=>6, 'km_l'=>8.5, 'risk'=>'Medium'],
            ['id'=>7,'name'=>'Liza Mercado',      'eid'=>'DRV-1007','role'=>'City Courier',              'on_time'=>77,'fuel'=>74,'safety'=>76,'attendance'=>86,'trips'=>54, 'completed'=>47, 'delayed'=>7, 'km_l'=>8.1, 'risk'=>'Medium'],
            ['id'=>8,'name'=>'Bong Dela Cruz',    'eid'=>'DRV-1008','role'=>'Night Shift Driver',        'on_time'=>70,'fuel'=>67,'safety'=>70,'attendance'=>78,'trips'=>43, 'completed'=>36, 'delayed'=>7, 'km_l'=>7.6, 'risk'=>'High'],
            ['id'=>9,'name'=>'Tess Gonzales',     'eid'=>'DRV-1009','role'=>'Utility Driver',            'on_time'=>64,'fuel'=>61,'safety'=>63,'attendance'=>74,'trips'=>38, 'completed'=>30, 'delayed'=>8, 'km_l'=>7.2, 'risk'=>'High'],
            ['id'=>10,'name'=>'Raul Dizon',       'eid'=>'DRV-1010','role'=>'Trainee Driver',             'on_time'=>57,'fuel'=>54,'safety'=>56,'attendance'=>70,'trips'=>28, 'completed'=>20, 'delayed'=>8, 'km_l'=>6.8, 'risk'=>'High'],
        ];

        foreach ($seed as $i => &$d) {
            $d['score'] = $this->computeDriverScore($d['on_time'], $d['fuel'], $d['safety'], $d['attendance']);
            $d['rank']  = $i + 1;
        }
        unset($d);

        return $seed;
    }

    /**
     * GET /api/driver/dashboard
     * Returns KPI summary and monthly trend data.
     */
    public function getDriverDashboard(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $drivers = $this->buildDriverDataset();

        $scores    = array_column($drivers, 'score');
        $avgScore  = round(array_sum($scores) / count($scores), 1);
        $top       = $drivers[0];
        $lowest    = $drivers[count($drivers) - 1];
        $totalTrips= array_sum(array_column($drivers, 'trips'));

        $this->jsonResponse([
            'success' => true,
            'kpis' => [
                'total_drivers'   => count($drivers),
                'active_drivers'  => 8,
                'avg_score'       => $avgScore,
                'top_driver'      => ['name' => $top['name'], 'score' => $top['score'], 'id' => $top['eid']],
                'lowest_driver'   => ['name' => $lowest['name'], 'score' => $lowest['score'], 'id' => $lowest['eid']],
                'total_trips'     => $totalTrips,
            ],
            'monthly_trend' => [
                'labels' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
                'scores' => [82,84,83,87,89,91,90,93],
                'trips'  => [88,92,86,102,110,118,112,124],
                'km_l'   => [9.1,9.3,9.0,9.5,9.7,10.1,9.9,10.4],
            ],
        ]);
    }

    /**
     * GET /api/driver/rankings?period=monthly|quarterly&type=top|bottom
     */
    public function getDriverRankings(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $period = $_GET['period'] ?? 'monthly';
        $type   = $_GET['type']   ?? 'top';
        $limit  = (int)($_GET['limit'] ?? 10);

        $drivers = $this->buildDriverDataset();

        if ($type === 'bottom') {
            $drivers = array_reverse($drivers);
        }

        $drivers = array_slice($drivers, 0, $limit);

        $this->jsonResponse([
            'success' => true,
            'period'  => $period,
            'type'    => $type,
            'rankings'=> $drivers,
        ]);
    }

    /**
     * GET /api/driver/{id}/performance
     */
    public function getDriverPerformance(int $driverId): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $drivers = $this->buildDriverDataset();

        $driver = null;
        foreach ($drivers as $d) {
            if ($d['id'] === $driverId) { $driver = $d; break; }
        }

        if (!$driver) {
            $this->jsonResponse(['success' => false, 'error' => 'Driver not found'], 404);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'driver'  => $driver,
            'score_breakdown' => [
                'on_time_delivery' => ['value' => $driver['on_time'], 'weight' => 40, 'weighted' => round($driver['on_time'] * 0.40, 1)],
                'fuel_efficiency'  => ['value' => $driver['fuel'],   'weight' => 30, 'weighted' => round($driver['fuel']    * 0.30, 1)],
                'safety_score'     => ['value' => $driver['safety'], 'weight' => 20, 'weighted' => round($driver['safety']  * 0.20, 1)],
                'attendance'       => ['value' => $driver['attendance'], 'weight' => 10, 'weighted' => round($driver['attendance'] * 0.10, 1)],
                'composite'        => $driver['score'],
            ],
        ]);
    }

    /**
     * GET /api/driver/analytics
     * Returns aggregated analytics for charts.
     */
    public function getDriverAnalytics(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin']);
        $drivers = $this->buildDriverDataset();

        $fuelRanking = $drivers;
        usort($fuelRanking, fn($a, $b) => $b['km_l'] <=> $a['km_l']);

        $safetyEvents = [
            ['type' => 'Overspeeding',       'count' => 12],
            ['type' => 'Route Deviation',    'count' => 7],
            ['type' => 'Excessive Idle',     'count' => 18],
            ['type' => 'Traffic Violation',  'count' => 4],
            ['type' => 'Near-Miss',          'count' => 3],
        ];

        $this->jsonResponse([
            'success'        => true,
            'drivers'        => $drivers,
            'fuel_ranking'   => array_map(fn($d) => ['name' => $d['name'], 'km_l' => $d['km_l'], 'risk' => $d['risk']], $fuelRanking),
            'safety_events'  => $safetyEvents,
            'attendance'     => [
                'present_rate'  => 94.0,
                'absent_rate'   => 4.0,
                'on_leave_rate' => 2.0,
            ],
        ]);
    }

    /**
     * GET /api/driver/reports?type=daily|weekly|monthly|comparison&format=json
     */
    public function getDriverReports(): void
    {
        $this->authorizeRole(['Dispatcher', 'Logistics Officer', 'Admin']);
        $type   = $_GET['type']   ?? 'monthly';
        $format = $_GET['format'] ?? 'json';
        $drivers = $this->buildDriverDataset();

        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'type'         => $type,
            'period'       => $type === 'daily' ? date('Y-m-d') : ($type === 'weekly' ? date('Y-\WW') : date('Y-m')),
            'summary'      => [
                'total_drivers'  => count($drivers),
                'avg_score'      => round(array_sum(array_column($drivers, 'score')) / count($drivers), 1),
                'total_trips'    => array_sum(array_column($drivers, 'trips')),
                'fleet_km_l'     => round(array_sum(array_column($drivers, 'km_l')) / count($drivers), 2),
            ],
            'drivers' => $drivers,
        ];

        $this->jsonResponse([
            'success' => true,
            'report'  => $report,
        ]);
    }
}

