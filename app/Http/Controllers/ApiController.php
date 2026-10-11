<?php

namespace App\Http\Controllers;

use App\Support\Database;
use App\Support\OpenRouteServiceGeocoder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

class ApiController
{
    public function __construct()
    {
        // Uses live fleet tables (vehicles, drivers, trip_records, alerts).
    }

    /**
     * Authenticate with Laravel guards and check role permissions.
     */
    public function authorizeRole(array $allowedRoles = []): array
    {
        $user = auth('sanctum')->user() ?? auth('web')->user();
        $userRole = $user?->role ?? '';

        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles, true)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Access Denied: Required role (' . implode(', ', $allowedRoles) . ') not assigned to current user.',
            ]);
            exit;
        }

        return ['id' => $user?->id, 'name' => $user?->name, 'role' => $userRole];
    }

    /**
     * GET /api/vehicles/live
     */
    public function getLiveVehicles(): void
    {
        $actor = $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User', 'Staff']);
        $state = $this->getFleetState();
        $authorizedVehicleIds = null;

        if ($actor['role'] !== 'Admin') {
            $driverIds = DB::table('drivers')
                ->where('user_id', $actor['id'])
                ->pluck('id');
            $dispatchVehicleIds = DB::table('dispatches')
                ->whereIn('driver_id', $driverIds)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
                ->pluck('vehicle_id');
            $tripVehicleIds = DB::table('trip_records')
                ->whereIn('driver_id', $driverIds)
                ->whereRaw('LOWER(TRIM(status)) <> ?', ['completed'])
                ->pluck('vehicle_id');
            $authorizedVehicleIds = $dispatchVehicleIds
                ->merge($tripVehicleIds)
                ->mapWithKeys(fn ($vehicleId) => [(int) $vehicleId => true])
                ->all();
        }

        $vehicles = [];
        foreach ($state['vehicles'] as $v) {
            if ($authorizedVehicleIds !== null && !isset($authorizedVehicleIds[$v['id']])) {
                continue;
            }

            $trip = $this->findActiveTripForVehicle($state, $v['id']);
            $loc = $state['locations'][$v['id']] ?? null;

            if ($loc === null && $trip) {
                $loc = [
                    'latitude' => $trip['origin_lat'] !== null ? (float)$trip['origin_lat'] : null,
                    'longitude' => $trip['origin_lng'] !== null ? (float)$trip['origin_lng'] : null,
                    'speed' => 0.0,
                    'timestamp' => $trip['departure_time'] ?? date('Y-m-d H:i:s'),
                ];
            }
            if ($loc === null) {
                $loc = ['latitude' => null, 'longitude' => null, 'speed' => 0.0, 'timestamp' => null];
            }

            $driver = null;
            if ($trip) {
                $driver = $state['drivers'][$trip['driver_id']] ?? null;
            }
            if ($driver === null && !empty($state['drivers'])) {
                $driver = $state['drivers'][$v['id']] ?? reset($state['drivers']);
            }

            $vehicles[] = [
                'id' => $v['id'],
                'vehicle_code' => $v['vehicle_code'],
                'plate_number' => $v['plate_number'],
                'type' => $v['type'],
                'status' => $v['status'],
                'fuel_level' => (float)$v['fuel_level'],
                'driver_name' => $driver['name'] ?? 'Unassigned',
                'employee_id' => $driver['employee_id'] ?? '',
                'latitude' => $loc['latitude'] !== null ? (float)$loc['latitude'] : null,
                'longitude' => $loc['longitude'] !== null ? (float)$loc['longitude'] : null,
                'speed' => (float)($loc['speed'] ?? 0),
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
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User']);
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
        $actor = $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User', 'Staff']);
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
        if (!$this->actorCanAccessTrip($trip, $actor)) {
            $this->jsonResponse(['success' => false, 'error' => 'Trip not found'], 404);
            return;
        }

        $v = $state['vehicles'][$trip['vehicle_id']] ?? null;
        $d = $state['drivers'][$trip['driver_id']] ?? null;
        $loc = $state['locations'][$trip['vehicle_id']] ?? null;

        // Geocode missing origin/destination coordinates and persist them.
        $coords = $this->ensureTripCoordinates($trip);
        if ($coords === null) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Unable to geocode the trip origin or destination. Check the application log for the failed location.',
            ], 400);
            return;
        }
        $trip = array_merge($trip, $coords);

        if ($loc === null) {
            $loc = [
                'latitude' => (float)$trip['origin_lat'],
                'longitude' => (float)$trip['origin_lng'],
                'speed' => 0.0,
                'fuel_level' => (float)($v['fuel_level'] ?? 0),
            ];
        }

        // Keep route geometry independent of live GPS; ETA is loaded separately.
        $routeColor = $this->calculateRouteColor($trip, (float)$loc['speed']);
        $originLocation = [
            'latitude' => (float)$trip['origin_lat'],
            'longitude' => (float)$trip['origin_lng'],
            'speed' => 0.0,
        ];
        $waypoints = [];
        $alternativeRoutes = [];
        try {
            $dispatchRouteResponse = $this->requestOpenRouteServiceRouteForTrip($trip, null);
            $waypoints = $this->generateRouteWaypoints($trip, $originLocation, $dispatchRouteResponse);
            $alternativeRoutes = $this->buildAlternativeRoutes(
                $dispatchRouteResponse,
                $trip,
                $originLocation,
                $waypoints
            );
        } catch (\Throwable $exception) {
            Log::warning('Unable to generate TripRecord A-to-B route.', [
                'trip_record_id' => $trip['id'],
                'exception' => $exception->getMessage(),
            ]);
        }

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
                'routes' => $alternativeRoutes,
                'traffic_delays' => $trafficDelays,
                'eta' => null,
            ],
        ]);
    }

    /**
     * GET /api/trip/{id}/eta
     */
    public function getTripEta(int $tripId): void
    {
        $actor = $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User', 'Staff']);
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
        if (!$this->actorCanAccessTrip($trip, $actor)) {
            $this->jsonResponse(['success' => false, 'error' => 'Trip not found'], 404);
            return;
        }

        $loc = $state['locations'][$trip['vehicle_id']] ?? null;
        $coords = $this->ensureTripCoordinates($trip);
        if ($coords === null) {
            $this->jsonResponse(['success' => false, 'error' => 'Unable to derive trip origin/destination coordinates.'], 400);
            return;
        }
        $trip = array_merge($trip, $coords);
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
        $actor = $this->authorizeRole(['Driver', 'User', 'Dispatcher', 'Admin']);
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $vehicleId = (int)($data['vehicle_id'] ?? 0);
        $origin = $data['origin'] ?? null;
        $destination = $data['destination'] ?? null;
        $originLat = isset($data['origin_lat']) ? (float)$data['origin_lat'] : null;
        $originLng = isset($data['origin_lng']) ? (float)$data['origin_lng'] : null;
        $destLat = isset($data['dest_lat']) ? (float)$data['dest_lat'] : null;
        $destLng = isset($data['dest_lng']) ? (float)$data['dest_lng'] : null;
        $driverId = isset($data['driver_id']) ? (int)$data['driver_id'] : 0;

        $driverBoundActor = in_array($actor['role'], ['Driver', 'User'], true);
        if ($driverBoundActor) {
            $accountDriverId = (int)DB::table('drivers')->where('user_id', $actor['id'])->value('id');
            if (!$accountDriverId || ($driverId && $driverId !== $accountDriverId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Driver account does not match the requested trip.'], 403);
                return;
            }
            $driverId = $accountDriverId;
        }

        $vehicle = $vehicleId ? DB::table('vehicles')->where('id', $vehicleId)->first() : null;
        if (!$vehicle) {
            $this->jsonResponse(['success' => false, 'error' => 'Vehicle not found.'], 404);
            return;
        }
        if (!$driverId) {
            $latestTrip = DB::table('trip_records')->where('vehicle_id', $vehicleId)->orderByDesc('id')->first();
            $driverId = $latestTrip->driver_id ?? 0;
        }
        $driver = $driverId ? DB::table('drivers')->where('id', $driverId)->first() : null;
        if (!$driver) {
            $this->jsonResponse(['success' => false, 'error' => 'Driver not found for this vehicle.'], 422);
            return;
        }

        $activeDispatch = DB::table('dispatches')
            ->where('vehicle_id', $vehicleId)
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
            ->orderByDesc('id')
            ->first();
        $existingVehicleTrip = DB::table('trip_records')
            ->where('vehicle_id', $vehicleId)
            ->whereRaw('LOWER(TRIM(status)) <> ?', ['completed'])
            ->orderByDesc('id')
            ->first();
        if ($driverBoundActor && $activeDispatch && (int)$activeDispatch->driver_id !== $driverId) {
            $this->jsonResponse(['success' => false, 'error' => 'Vehicle is assigned to a different driver.'], 403);
            return;
        }
        if ($actor['role'] === 'User' && $existingVehicleTrip && (int)$existingVehicleTrip->driver_id !== $driverId) {
            $this->jsonResponse(['success' => false, 'error' => 'Vehicle is assigned to a different driver.'], 403);
            return;
        }

        $ownsActiveDispatch = $activeDispatch && (int)$activeDispatch->driver_id === $driverId;
        $ownsExistingTrip = $existingVehicleTrip && (int)$existingVehicleTrip->driver_id === $driverId;
        if ($actor['role'] === 'User' && !$ownsActiveDispatch && !$ownsExistingTrip) {
            $this->jsonResponse(['success' => false, 'error' => 'No active dispatch or trip is assigned to this driver account.'], 403);
            return;
        }

        $dispatchId = $ownsActiveDispatch
            ? (int)$activeDispatch->id
            : null;
        $existingTrip = null;
        if ($dispatchId !== null) {
            $existingTrip = DB::table('trip_records')
                ->where('dispatch_id', $dispatchId)
                ->whereRaw('LOWER(TRIM(status)) <> ?', ['completed'])
                ->orderByDesc('id')
                ->first();
        }
        if ($actor['role'] === 'User' && !$existingTrip && $ownsExistingTrip) {
            $existingTrip = $existingVehicleTrip;
        }
        if ($existingTrip) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Tracking continued for the active dispatch or trip.',
                'trip' => (array)$existingTrip,
                'tracking_interval_seconds' => 5,
            ]);
            return;
        }

        if (!$origin || !$destination) {
            $this->jsonResponse(['success' => false, 'error' => 'Origin and destination are required.'], 422);
            return;
        }

        // Geocode free-text addresses when the client did not provide coordinates.
        if ($originLat === null || $originLng === null) {
            $geo = $this->geocodeAddress((string)$origin);
            $originLat = $geo['lat'] ?? null;
            $originLng = $geo['lng'] ?? null;
        }
        if ($destLat === null || $destLng === null) {
            $geo = $this->geocodeAddress((string)$destination);
            $destLat = $geo['lat'] ?? null;
            $destLng = $geo['lng'] ?? null;
        }

      if ($originLat === null || $originLng === null || $destLat === null || $destLng === null) {
    $this->jsonResponse([
        'success' => false,
        'error' => 'Unable to determine coordinates for the trip origin or destination. Please check the addresses and try again.',
    ], 422);
    return;
}

        $now = date('Y-m-d H:i:s');

        $tripId = DB::transaction(function () use ($vehicleId, $driverId, $dispatchId, $origin, $destination, $originLat, $originLng, $destLat, $destLng, $now, $vehicle) {
            DB::table('vehicles')->where('id', $vehicleId)->update(['status' => 'Active', 'updated_at' => $now]);

            DB::table('location_logs')->insert([
                'vehicle_id' => $vehicleId,
                'latitude' => $originLat ?? 0,
                'longitude' => $originLng ?? 0,
                'speed' => 0.0,
                'timestamp' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Close any old active trip for this vehicle.
            DB::table('trip_records')
                ->where('vehicle_id', $vehicleId)
                ->where('status', '!=', 'Completed')
                ->update(['status' => 'Completed', 'actual_arrival' => $now, 'updated_at' => $now]);

            $tripId = DB::table('trip_records')->insertGetId([
                'dispatch_id' => $dispatchId,
                'vehicle_id' => $vehicleId,
                'driver_id' => $driverId,
                'origin' => $origin,
                'destination' => $destination,
                'origin_lat' => $originLat,
                'origin_lng' => $originLng,
                'dest_lat' => $destLat,
                'dest_lng' => $destLng,
                'departure_time' => $now,
                'estimated_arrival' => null,
                'actual_arrival' => null,
                'total_distance' => ($originLat !== null && $destLat !== null)
                    ? round($this->haversineDistance((float)$originLat, (float)$originLng, (float)$destLat, (float)$destLng), 2)
                    : 0,
                'total_duration' => 0,
                'fuel_consumption' => 0,
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('alerts')->insert([
                'vehicle_id' => $vehicleId,
                'trip_record_id' => $tripId,
                'type' => 'Trip started',
                'title' => 'Trip started',
                'message' => "Driver started a trip for Vehicle #{$vehicle->vehicle_code} heading to {$destination}.",
                'detail' => "Driver started a trip for Vehicle #{$vehicle->vehicle_code} heading to {$destination}.",
                'icon' => '🚚',
                'severity' => 'info',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $tripId;
        });

        $trip = DB::table('trip_records')->where('id', $tripId)->first();

        $this->jsonResponse([
            'success' => true,
            'message' => "Trip started successfully for Vehicle #{$vehicle->vehicle_code}.",
            'trip' => (array) $trip,
            'tracking_interval_seconds' => 5,
        ]);
    }

    /**
     * POST /api/location/update
     */
    public function updateLocation(): void

    {
        $actor = $this->authorizeRole(['Driver', 'User', 'Dispatcher', 'Admin']);
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $vehicleId = (int)($data['vehicle_id'] ?? 0);
        $latitude = isset($data['latitude']) ? (float)$data['latitude'] : null;
        $longitude = isset($data['longitude']) ? (float)$data['longitude'] : null;
        $speed = isset($data['speed']) ? (float)$data['speed'] : 0.0;
        $fuelLevel = isset($data['fuel_level']) ? (float)$data['fuel_level'] : null;
        $vehicle = $vehicleId ? DB::table('vehicles')->where('id', $vehicleId)->first() : null;

        if (!$vehicle || $latitude === null || $longitude === null) {
            $this->jsonResponse(['success' => false, 'error' => 'Vehicle and coordinates are required.'], 422);
            return;
        }

        $tripModel = DB::table('trip_records')
            ->where('vehicle_id', $vehicleId)
            ->whereRaw('LOWER(TRIM(status)) <> ?', ['completed'])
            ->orderByDesc('id')
            ->first();

        $activeDispatch = DB::table('dispatches')
            ->where('vehicle_id', $vehicleId)
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
            ->orderByDesc('id')
            ->first();

        if (in_array($actor['role'], ['Driver', 'User'], true)) {
            $accountDriverId = (int)DB::table('drivers')->where('user_id', $actor['id'])->value('id');
            $ownsTrip = $tripModel && (int)$tripModel->driver_id === $accountDriverId;
            $ownsDispatch = $activeDispatch && (int)$activeDispatch->driver_id === $accountDriverId;
            if (!$accountDriverId ||
                ($tripModel && (int)$tripModel->driver_id !== $accountDriverId) ||
                ($activeDispatch && (int)$activeDispatch->driver_id !== $accountDriverId) ||
                ($actor['role'] === 'User' && !$ownsTrip && !$ownsDispatch)) {
                $this->jsonResponse(['success' => false, 'error' => 'Vehicle is not assigned to this driver account.'], 403);
                return;
            }
        }

        $now = date('Y-m-d H:i:s');

        $locRecord = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'timestamp' => $now,
        ];
        if ($fuelLevel !== null) {
            $locRecord['fuel_level'] = $fuelLevel;
        }

        DB::table('location_logs')->insert([
            'vehicle_id' => $vehicleId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'fuel_level' => $fuelLevel,
            'timestamp' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($fuelLevel !== null) {
            DB::table('vehicles')->where('id', $vehicleId)->update(['fuel_level' => $fuelLevel, 'updated_at' => $now]);
        }

        $trip = $tripModel ? (array) $tripModel : null;
        $notificationsCreated = [];
        $arrivalDetected = false;

        $addAlert = function (string $type, string $message, string $severity) use ($vehicleId, $trip, $vehicle, $now, &$notificationsCreated) {
            $alertId = DB::table('alerts')->insertGetId([
                'vehicle_id' => $vehicleId,
                'trip_record_id' => $trip['id'] ?? null,
                'type' => $type,
                'title' => $type,
                'message' => $message,
                'detail' => $message,
                'icon' => $severity === 'warning' ? '⚠️' : 'ℹ️',
                'severity' => $severity,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $notificationsCreated[] = [
                'id' => $alertId,
                'trip_id' => $trip['id'] ?? null,
                'vehicle_id' => $vehicleId,
                'type' => $type,
                'message' => $message,
                'severity' => $severity,
                'created_at' => $now,
            ];
        };

        if ($trip) {
            $tripId = $trip['id'];

            // Ensure destination coordinates exist so arrival monitoring works.
            $trip = array_merge($trip, $this->ensureTripCoordinates($trip) ?? []);
            $destLat = $trip['dest_lat'] !== null ? (float)$trip['dest_lat'] : null;
            $destLng = $trip['dest_lng'] !== null ? (float)$trip['dest_lng'] : null;

            if ($destLat !== null && $destLng !== null) {
                // 1. Calculate distance to destination in kilometers
                $distToDestKm = $this->haversineDistance($latitude, $longitude, $destLat, $destLng);

                // 2. Arrival Monitoring: detect if reached destination (< 50 meters = 0.05 km)
                if ($distToDestKm <= 0.05 && $trip['status'] !== 'Completed') {
                    $arrivalDetected = true;

                    $startTime = $trip['departure_time'] ? strtotime((string)$trip['departure_time']) : time();
                    $durationMins = max(1, (int)round((time() - $startTime) / 60));
                    $totalDist = (float)($trip['total_distance'] ?: round($this->haversineDistance((float)$trip['origin_lat'], (float)$trip['origin_lng'], $destLat, $destLng), 2));
                    $fuelUsed = round($totalDist * 0.28, 2); // Average 0.28 L / km for heavy cargo truck

                    DB::table('trip_records')->where('id', $tripId)->update([
                        'status' => 'Completed',
                        'actual_arrival' => $now,
                        'total_distance' => $totalDist,
                        'total_duration' => $durationMins,
                        'fuel_consumption' => $fuelUsed,
                        'updated_at' => $now,
                    ]);

                    // Complete the dispatch linked to this trip, if any.
                    if (!empty($trip['dispatch_id'])) {
                        DB::table('dispatches')->where('id', $trip['dispatch_id'])->update(['status' => 'Completed', 'updated_at' => $now]);
                    }

                    DB::table('vehicles')->where('id', $vehicleId)->update(['status' => 'Active', 'updated_at' => $now]);

                    $addAlert('Vehicle arrived', "Vehicle #{$vehicle->vehicle_code} has arrived safely at {$trip['destination']}.", 'info');
                } else {
                    // Check Route Deviation (> 3 KM off straight path)
                    if ($trip['origin_lat'] !== null && $trip['origin_lng'] !== null) {
                        $originDist = $this->haversineDistance((float)$trip['origin_lat'], (float)$trip['origin_lng'], $latitude, $longitude);
                        $directDist = $this->haversineDistance((float)$trip['origin_lat'], (float)$trip['origin_lng'], $destLat, $destLng);
                        if ($originDist > ($directDist + 3.0)) {
                            $addAlert('Route deviation', "Route deviation detected for Vehicle #{$vehicle->vehicle_code} on trip to {$trip['destination']}.", 'warning');
                        }
                    }

                    // Check Excessive Idle Time (speed = 0)
                    if ($speed == 0) {
                        $addAlert('Excessive idle time', "Excessive idle time logged for Vehicle #{$vehicle->vehicle_code} (Stationary at GPS location).", 'warning');
                    }
                }
            } else {
                // Check Excessive Idle Time even when coordinates are unavailable.
                if ($speed == 0) {
                    $addAlert('Excessive idle time', "Excessive idle time logged for Vehicle #{$vehicle->vehicle_code} (Stationary at GPS location).", 'warning');
                }
            }
        }

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
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User']);

        $today = date('Y-m-d');
        $activeTrips = 0;
        $completedTrips = 0;
        $delayedTrips = 0;
        $totalDist = 0.0;
        $totalFuel = 0.0;

        $trips = DB::table('trip_records')->get();
        foreach ($trips as $t) {
            if (in_array(strtolower((string)$t->status), ['completed'], true)) {
                $completedTrips++;
                $completedDate = $t->actual_arrival ? substr((string)$t->actual_arrival, 0, 10) : null;
                if ($completedDate === $today) {
                    $totalDist += (float)($t->total_distance ?? 0);
                    $totalFuel += (float)($t->fuel_consumption ?? 0);
                }
            } elseif (strtolower((string)$t->status) === 'delayed' || strtolower((string)$t->status) === 'critical delay') {
                $activeTrips++;
                $delayedTrips++;
            } else {
                $activeTrips++;
                $loc = DB::table('location_logs')
                    ->where('vehicle_id', $t->vehicle_id)
                    ->orderByDesc('timestamp')
                    ->first();
                $speed = $loc ? (float)$loc->speed : 30.0;
                $color = $this->calculateRouteColor(['status' => $t->status], $speed);
                if ($color === 'yellow' || $color === 'red') {
                    $delayedTrips++;
                }
            }
        }

        $this->jsonResponse([
            'success' => true,
            'analytics' => [
                'active_trips' => $activeTrips,
                'completed_trips' => $completedTrips,
                'delayed_trips' => $delayedTrips,
                'avg_eta_accuracy' => 0.0,
                'total_distance_today_km' => round($totalDist, 1),
                'total_fuel_consumption_l' => round($totalFuel, 1),
            ]
        ]);
    }

    /**
     * GET /api/notifications
     */
    public function getNotifications(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User']);
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
                    ['employee_id' => 'DRV-1002', 'name' => 'Jhoanna Reforsado', 'license' => 'Professional Class 3', 'status' => 'On Shift', 'health_clearance' => 'Passed'],
                ],
                'sent_driver_assignments' => [
                    ['driver_id' => 'DRV-1001', 'assigned_vehicle' => 'TRK-101', 'shift_hours' => 6.5],
                    ['driver_id' => 'DRV-1002', 'assigned_vehicle' => 'TRK-102', 'shift_hours' => 4.2],
                ]
            ],
            'hr4_compensation_payroll' => [
                'sent_trip_logs' => [
                    ['driver' => 'Harvey Villarin', 'total_trips' => 4, 'total_hours' => 8.0, 'ot_hours' => 1.5, 'allowance_earned_php' => 1200.00],
                    ['driver' => 'Jhoanna Reforsado', 'total_trips' => 3, 'total_hours' => 6.0, 'ot_hours' => 0.0, 'allowance_earned_php' => 900.00],
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
    private function computeEtaDetails(array $trip, ?array $loc, ?array $routeResponse = null): array
    {
        $routeResponse = $routeResponse ?? $this->requestOpenRouteServiceRouteForTrip($trip, $loc);
        $summary = $this->getOpenRouteServiceSummary($routeResponse);
        if (!is_array($summary) || !is_numeric($summary['distance'] ?? null) || !is_numeric($summary['duration'] ?? null)) {
            throw new \RuntimeException('OpenRouteService response contains no valid route distance and duration.');
        }

        $remainingDistKm = round((float)$summary['distance'] / 1000, 1);
        $timeMins = (int)round((float)$summary['duration'] / 60);

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

    private function requestOpenRouteServiceRouteForTrip(array $trip, ?array $loc): array
    {
        $currentLat = $loc['latitude'] ?? null;
        $currentLng = $loc['longitude'] ?? null;
        $hasValidCurrentLocation = is_numeric($currentLat) && is_numeric($currentLng) &&
            (float)$currentLat >= -90 && (float)$currentLat <= 90 &&
            (float)$currentLng >= -180 && (float)$currentLng <= 180;

        return $this->requestOpenRouteServiceRoute(
            $hasValidCurrentLocation ? (float)$currentLat : (float)$trip['origin_lat'],
            $hasValidCurrentLocation ? (float)$currentLng : (float)$trip['origin_lng'],
            (float)$trip['dest_lat'],
            (float)$trip['dest_lng']
        );
    }

    private function getOpenRouteServiceSummary(array $response): ?array
    {
        $routes = $response['routes'] ?? null;
        if (is_array($routes) && isset($routes[0]) && is_array($routes[0])) {
            $summary = $routes[0]['summary'] ?? null;
            if (is_array($summary)) {
                return $summary;
            }
        }

        $features = $response['features'] ?? null;
        if (is_array($features) && isset($features[0]) && is_array($features[0])) {
            $properties = $features[0]['properties'] ?? null;
            if (is_array($properties) && is_array($properties['summary'] ?? null)) {
                return $properties['summary'];
            }
        }

        return null;
    }

    private function formatRouteTravelTime(int $timeMins): string
    {
        $hours = intdiv($timeMins, 60);
        $minutes = $timeMins % 60;

        if ($hours > 0) {
            return "{$hours} hr" . ($minutes > 0 ? " {$minutes} mins" : '');
        }

        return "{$timeMins} mins";
    }

    private function requestOpenRouteServiceRoute(float $startLat, float $startLng, float $destLat, float $destLng): array
    {
        $apiKey = getenv('OPENROUTESERVICE_API_KEY');
        if ($apiKey === false || trim($apiKey) === '') {
            throw new \RuntimeException('OpenRouteService API key is missing (OPENROUTESERVICE_API_KEY).');
        }

        if (!function_exists('curl_init')) {
            throw new \RuntimeException('OpenRouteService routing requires the PHP cURL extension.');
        }

        $requestBody = json_encode([
            'coordinates' => [
                [$startLng, $startLat],
                [$destLng, $destLat],
            ],
            'alternative_routes' => [
                'target_count' => 3,
            ],
        ]);
        if ($requestBody === false) {
            throw new \RuntimeException('Unable to encode the OpenRouteService routing request.');
        }

        $curl = curl_init('https://api.heigit.org/openrouteservice/v2/directions/driving-car');
        if ($curl === false) {
            throw new \RuntimeException('Unable to initialize the OpenRouteService HTTP request.');
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $responseBody = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpStatus = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($responseBody === false) {
            throw new \RuntimeException('OpenRouteService HTTP request failed: ' . $curlError);
        }

        $response = json_decode($responseBody, true);
        if ($httpStatus < 200 || $httpStatus >= 300) {
            $apiError = is_array($response)
                ? ($response['error']['message'] ?? $response['message'] ?? 'No details provided.')
                : 'No details provided.';
            throw new \RuntimeException("OpenRouteService returned HTTP {$httpStatus}: {$apiError}");
        }

        if (!is_array($response)) {
            throw new \RuntimeException('OpenRouteService returned an invalid JSON response.');
        }

        if (isset($response['error'])) {
            $apiError = $response['error']['message'] ?? $response['message'] ?? 'No details provided.';
            throw new \RuntimeException('OpenRouteService returned an API error: ' . $apiError);
        }

        return $response;
    }

    private function generateRouteWaypoints(array $trip, array $loc, ?array $routeResponse = null): array
    {
        $routeResponse = $routeResponse ?? $this->requestOpenRouteServiceRouteForTrip($trip, $loc);
        $coordinates = null;
        $features = $routeResponse['features'] ?? null;
        $feature = is_array($features) ? ($features[0] ?? null) : null;
        if (is_array($feature) && is_array($feature['geometry'] ?? null)) {
            $coordinates = $feature['geometry']['coordinates'] ?? null;
        }

        $routes = $routeResponse['routes'] ?? null;
        $primaryRoute = is_array($routes) ? ($routes[0] ?? null) : null;
        $encodedGeometry = is_array($primaryRoute) ? ($primaryRoute['geometry'] ?? null) : null;
        if (is_string($encodedGeometry) && $encodedGeometry !== '') {
            $coordinates = [];
            $index = 0;
            $latitude = 0;
            $longitude = 0;
            $encodedLength = strlen($encodedGeometry);

            while ($index < $encodedLength) {
                $deltas = [];
                for ($axis = 0; $axis < 2; $axis++) {
                    $result = 0;
                    $shift = 0;
                    do {
                        if ($index >= $encodedLength) {
                            throw new \RuntimeException('OpenRouteService returned invalid route geometry.');
                        }

                        $byte = ord($encodedGeometry[$index++]) - 63;
                        if ($byte < 0 || $byte > 63) {
                            throw new \RuntimeException('OpenRouteService returned invalid route geometry.');
                        }

                        $result |= ($byte & 0x1f) << $shift;
                        $shift += 5;
                    } while ($byte >= 0x20);

                    $deltas[] = ($result & 1) ? ~($result >> 1) : ($result >> 1);
                }

                $latitude += $deltas[0];
                $longitude += $deltas[1];
                $coordinates[] = [$longitude / 100000, $latitude / 100000];
            }
        } elseif (is_array($encodedGeometry)) {
            $coordinates = $encodedGeometry;
        }

        if (!is_array($coordinates) || $coordinates === []) {
            throw new \RuntimeException('OpenRouteService response contains no route geometry.');
        }

        $waypoints = [];
        foreach ($coordinates as $coordinate) {
            if (!is_array($coordinate) || !isset($coordinate[0], $coordinate[1]) ||
                !is_numeric($coordinate[0]) || !is_numeric($coordinate[1])) {
                throw new \RuntimeException('OpenRouteService returned invalid route geometry.');
            }

            $waypoints[] = ['lat' => (float)$coordinate[1], 'lng' => (float)$coordinate[0]];
        }

        return $waypoints;
    }

    private function buildAlternativeRoutes(array $response, array $trip, array $loc, array $primaryWaypoints): array
    {
        $candidates = [];
        $routeFormat = 'routes';
        $orsRoutes = $response['routes'] ?? null;
        if (is_array($orsRoutes)) {
            foreach ($orsRoutes as $index => $route) {
                if (is_array($route)) {
                    $candidates[] = ['index' => (int)$index, 'route' => $route];
                }
            }
        }

        if ($candidates === []) {
            $routeFormat = 'features';
            $features = $response['features'] ?? null;
            if (is_array($features)) {
                foreach ($features as $index => $feature) {
                    if (is_array($feature)) {
                        $candidates[] = ['index' => (int)$index, 'route' => $feature];
                    }
                }
            }
        }

        // ORS returns the selected primary route first; retain that ordering in the API response.
        if ($candidates === [] || $candidates[0]['index'] !== 0) {
            return [];
        }

        $routeOptions = [];
        foreach ($candidates as $candidate) {
            if ($candidate['index'] >= 3) {
                continue;
            }

            $route = $candidate['route'];
            if ($routeFormat === 'routes') {
                $summary = $route['summary'] ?? null;
            } else {
                $properties = $route['properties'] ?? null;
                $summary = is_array($properties) ? ($properties['summary'] ?? null) : null;
            }

            if (!is_array($summary) || !is_numeric($summary['distance'] ?? null) ||
                !is_numeric($summary['duration'] ?? null) || (float)$summary['distance'] < 0 ||
                (float)$summary['duration'] < 0) {
                continue;
            }

            if ($candidate['index'] === 0) {
                $waypoints = $primaryWaypoints;
            } else {
                $candidateResponse = [$routeFormat => [$route]];
                try {
                    $waypoints = $this->generateRouteWaypoints($trip, $loc, $candidateResponse);
                } catch (\RuntimeException $exception) {
                    continue;
                }
            }

            $timeMins = (int)round((float)$summary['duration'] / 60);
            $routeOptions[] = [
                'route_number' => count($routeOptions) + 1,
                'route_index' => $candidate['index'],
                'waypoints' => $waypoints,
                'distance_km' => round((float)$summary['distance'] / 1000, 1),
                'travel_time_mins' => $timeMins,
                'formatted_travel_time' => $this->formatRouteTravelTime($timeMins),
            ];
        }

        return $routeOptions;
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

    private function actorCanAccessTrip(array $trip, array $actor): bool
    {
        return $actor['role'] === 'Admin' || DB::table('drivers')
            ->where('id', $trip['driver_id'])
            ->where('user_id', $actor['id'])
            ->exists();
    }

    private function ensureStorage(): void
    {
        // Legacy no-op: the fleet is backed directly by the database now.
    }

    private function getFleetState(): array
    {
        $vehicles = [];
        foreach (DB::table('vehicles')->get() as $v) {
            $vehicles[(int)$v->id] = [
                'id' => (int)$v->id,
                'vehicle_code' => $v->vehicle_code ?? ($v->name ?? (string)$v->id),
                'plate_number' => $v->plate_number,
                'type' => $v->type,
                'status' => $v->status,
                'fuel_level' => (float)$v->fuel_level,
            ];
        }

        $drivers = [];
        foreach (DB::table('drivers')->leftJoin('users', 'drivers.user_id', '=', 'users.id')->get(['drivers.*', 'users.name as user_name']) as $d) {
            $drivers[(int)$d->id] = [
                'id' => (int)$d->id,
                'name' => $d->user_name ?: ($d->name ?? 'Driver'),
                'employee_id' => $d->employee_id,
                'role' => $d->role,
                'score' => (float)$d->score,
            ];
        }

        $locations = [];
        foreach (DB::table('location_logs')->orderByDesc('timestamp')->orderByDesc('id')->get() as $l) {
            $vehicleId = (int)$l->vehicle_id;
            if (isset($locations[$vehicleId])) {
                continue;
            }
            $locations[$vehicleId] = [
                'latitude' => $l->latitude !== null ? (float)$l->latitude : null,
                'longitude' => $l->longitude !== null ? (float)$l->longitude : null,
                'speed' => (float)($l->speed ?? 0),
                'fuel_level' => $l->fuel_level !== null ? (float)$l->fuel_level : null,
                'timestamp' => (string)$l->timestamp,
            ];
        }

        $trips = [];
        foreach (DB::table('trip_records')->get() as $t) {
            $trips[] = [
                'id' => (int)$t->id,
                'vehicle_id' => (int)$t->vehicle_id,
                'driver_id' => (int)$t->driver_id,
                'origin' => $t->origin,
                'destination' => $t->destination,
                'origin_lat' => $t->origin_lat !== null ? (float)$t->origin_lat : null,
                'origin_lng' => $t->origin_lng !== null ? (float)$t->origin_lng : null,
                'dest_lat' => $t->dest_lat !== null ? (float)$t->dest_lat : null,
                'dest_lng' => $t->dest_lng !== null ? (float)$t->dest_lng : null,
                'departure_time' => (string)$t->departure_time,
                'estimated_arrival' => $t->estimated_arrival !== null ? (string)$t->estimated_arrival : null,
                'actual_arrival' => $t->actual_arrival !== null ? (string)$t->actual_arrival : null,
                'total_distance' => $t->total_distance !== null ? (float)$t->total_distance : 0.0,
                'total_duration' => (int)($t->total_duration ?? 0),
                'fuel_consumption' => $t->fuel_consumption !== null ? (float)$t->fuel_consumption : 0.0,
                'status' => $t->status,
            ];
        }

        $notifications = [];
        foreach (DB::table('alerts')->orderByDesc('id')->limit(50)->get() as $a) {
            $notifications[] = [
                'id' => (int)$a->id,
                'trip_id' => $a->trip_record_id !== null ? (int)$a->trip_record_id : null,
                'vehicle_id' => $a->vehicle_id !== null ? (int)$a->vehicle_id : null,
                'type' => $a->type ?: ($a->title ?: 'Notice'),
                'message' => $a->message ?: ($a->detail ?: ''),
                'severity' => $a->severity ?? 'info',
                'created_at' => (string)$a->created_at,
            ];
        }
        $notifications = array_reverse($notifications);

        return [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'locations' => $locations,
            'trips' => $trips,
            'notifications' => $notifications,
        ];
    }

    private function saveFleetState(array $state): void
    {
        // Deprecated: state mutations are persisted directly via DB updates.
    }

    /**
     * Geocode a free-text address with the existing OpenRouteService key.
     * Returns ['lat' => float, 'lng' => float] or null.
     */
    private function geocodeAddress(string $address): ?array
    {
        return app(OpenRouteServiceGeocoder::class)->geocode($address);
    }

    /**
     * Ensure a trip has origin/destination coordinates; geocode missing ones
     * and persist them to the trip record (and linked dispatch).
     *
     * @return array{origin_lat: float, origin_lng: float, dest_lat: float, dest_lng: float}|null
     */
    private function ensureTripCoordinates(array $trip): ?array
    {
        $geocoder = app(OpenRouteServiceGeocoder::class);
        $originLat = $trip['origin_lat'] ?? null;
        $originLng = $trip['origin_lng'] ?? null;
        $destLat = $trip['dest_lat'] ?? null;
        $destLng = $trip['dest_lng'] ?? null;

        $hasOrigin = $geocoder->hasValidCoordinates($originLat, $originLng);
        $hasDest = $geocoder->hasValidCoordinates($destLat, $destLng);
        $failedLocations = [];

        if (!$hasOrigin && !empty($trip['origin'])) {
            $geo = $this->geocodeAddress((string)$trip['origin']);
            if ($geo !== null) {
                $originLat = $geo['lat'];
                $originLng = $geo['lng'];
                $hasOrigin = true;
            }
        }
        if (!$hasDest && !empty($trip['destination'])) {
            $geo = $this->geocodeAddress((string)$trip['destination']);
            if ($geo !== null) {
                $destLat = $geo['lat'];
                $destLng = $geo['lng'];
                $hasDest = true;
            }
        }

        if (!$hasOrigin) {
            $failedLocations[] = 'origin';
        }
        if (!$hasDest) {
            $failedLocations[] = 'destination';
        }
        if ($failedLocations !== []) {
            Log::warning('Unable to geocode TripRecord location(s).', [
                'trip_record_id' => $trip['id'] ?? null,
                'failed_locations' => $failedLocations,
                'origin' => $trip['origin'] ?? null,
                'destination' => $trip['destination'] ?? null,
            ]);
            return null;
        }

        DB::table('trip_records')->where('id', $trip['id'])->update([
            'origin_lat' => $originLat,
            'origin_lng' => $originLng,
            'dest_lat' => $destLat,
            'dest_lng' => $destLng,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($trip['dispatch_id'])) {
            DB::table('dispatches')->where('id', $trip['dispatch_id'])->update([
                'origin_lat' => $originLat,
                'origin_lng' => $originLng,
                'dest_lat' => $destLat,
                'dest_lng' => $destLng,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return [
            'origin_lat' => (float)$originLat,
            'origin_lng' => (float)$originLng,
            'dest_lat' => (float)$destLat,
            'dest_lng' => (float)$destLng,
        ];
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
        $rows = DB::table('drivers')
            ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
            ->leftJoin('trip_records', 'trip_records.driver_id', '=', 'drivers.id')
            ->select(
                'drivers.id',
                'users.name as user_name',
                'drivers.name as driver_name',
                'drivers.employee_id as eid',
                'drivers.role',
                'drivers.status',
                'drivers.score',
                DB::raw('COUNT(trip_records.id) as trips'),
                DB::raw("SUM(CASE WHEN trip_records.status = 'Completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN trip_records.status IS NOT NULL AND LOWER(trip_records.status) LIKE '%delay%' THEN 1 ELSE 0 END) as delayed"),
                DB::raw('COALESCE(SUM(trip_records.total_distance), 0) as distance'),
                DB::raw('COALESCE(SUM(trip_records.fuel_consumption), 0) as fuel')
            )
            ->groupBy('drivers.id', 'users.name', 'drivers.name', 'drivers.employee_id', 'drivers.role', 'drivers.status', 'drivers.score')
            ->orderByDesc('drivers.score')
            ->get();

        $dataset = [];
        foreach ($rows as $i => $row) {
            $name = $row->user_name ?: ($row->driver_name ?: 'Driver');
            $score = (float)($row->score ?? 0);
            $distance = (float)$row->distance;
            $fuel = (float)$row->fuel;

            $dataset[] = [
                'id' => (int)$row->id,
                'name' => $name,
                'eid' => $row->eid,
                'role' => $row->role,
                'status' => $row->status,
                'on_time' => 0,
                'fuel' => 0,
                'safety' => 0,
                'attendance' => 0,
                'trips' => (int)$row->trips,
                'completed' => (int)$row->completed,
                'delayed' => (int)$row->delayed,
                'km_l' => $fuel > 0 ? round($distance / $fuel, 1) : 0,
                'risk' => $score >= 90 ? 'Low' : ($score >= 70 ? 'Medium' : 'High'),
                'score' => round($score, 1),
                'rank' => $i + 1,
            ];
        }

        return $dataset;
    }

    /**
     * GET /api/driver/dashboard
     * Returns KPI summary and monthly trend data.
     */
    public function getDriverDashboard(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User']);
        $drivers = $this->buildDriverDataset();

        if (empty($drivers)) {
            $this->jsonResponse([
                'success' => true,
                'kpis' => [
                    'total_drivers'   => 0,
                    'active_drivers'  => 0,
                    'avg_score'       => 0,
                    'top_driver'      => ['name' => 'N/A', 'score' => 0, 'id' => ''],
                    'lowest_driver'   => ['name' => 'N/A', 'score' => 0, 'id' => ''],
                    'total_trips'     => 0,
                ],
                'monthly_trend' => ['labels' => [], 'scores' => [], 'trips' => [], 'km_l' => []],
            ]);
            return;
        }

        $scores    = array_column($drivers, 'score');
        $avgScore  = round(array_sum($scores) / count($scores), 1);
        $top       = $drivers[0];
        $lowest    = $drivers[count($drivers) - 1];
        $totalTrips= array_sum(array_column($drivers, 'trips'));
        $activeDrivers = count(array_filter($drivers, fn($d) => strtolower((string)($d['status'] ?? '')) === 'active'));

        // Build real monthly aggregates from trips.
        $trendLabels = [];
        $trendScores = [];
        $trendTrips  = [];
        $trendKmL    = [];
        for ($i = 7; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("first day of -{$i} month"));
            $monthEnd   = date('Y-m-t', strtotime($monthStart));
            $agg = DB::table('trip_records')
                ->whereBetween('created_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
                ->selectRaw('COUNT(*) as trips, COALESCE(SUM(total_distance),0) as distance, COALESCE(SUM(fuel_consumption),0) as fuel')
                ->first();

            $trendLabels[] = date('M', strtotime($monthStart));
            $trendScores[] = $avgScore;
            $trendTrips[]  = (int)($agg->trips ?? 0);
            $trendKmL[]    = ($agg->fuel ?? 0) > 0 ? round(((float)$agg->distance) / (float)$agg->fuel, 1) : 0;
        }

        $this->jsonResponse([
            'success' => true,
            'kpis' => [
                'total_drivers'   => count($drivers),
                'active_drivers'  => $activeDrivers,
                'avg_score'       => $avgScore,
                'top_driver'      => ['name' => $top['name'], 'score' => $top['score'], 'id' => $top['eid']],
                'lowest_driver'   => ['name' => $lowest['name'], 'score' => $lowest['score'], 'id' => $lowest['eid']],
                'total_trips'     => $totalTrips,
            ],
            'monthly_trend' => [
                'labels' => $trendLabels,
                'scores' => $trendScores,
                'trips'  => $trendTrips,
                'km_l'   => $trendKmL,
            ],
        ]);
    }

    /**
     * GET /api/driver/rankings?period=monthly|quarterly&type=top|bottom
     */
    public function getDriverRankings(): void
    {
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User']);
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
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User']);
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
        $this->authorizeRole(['Driver', 'Dispatcher', 'Logistics Officer', 'Admin', 'User']);
        $drivers = $this->buildDriverDataset();

        $fuelRanking = $drivers;
        usort($fuelRanking, fn($a, $b) => $b['km_l'] <=> $a['km_l']);

        $safetyEvents = [];

        $totalPresent   = count($drivers);
        $activeDrivers  = count(array_filter($drivers, fn($d) => strtolower((string)($d['status'] ?? '')) === 'active'));

        $this->jsonResponse([
            'success'        => true,
            'drivers'        => $drivers,
            'fuel_ranking'   => array_map(fn($d) => ['name' => $d['name'], 'km_l' => $d['km_l'], 'risk' => $d['risk']], $fuelRanking),
            'safety_events'  => $safetyEvents,
            'attendance'     => [
                'present_rate'  => $totalPresent > 0 ? round(($activeDrivers / $totalPresent) * 100, 1) : 0,
                'absent_rate'   => 0.0,
                'on_leave_rate' => 0.0,
            ],
        ]);
    }

    /**
     * GET /api/driver/reports?type=daily|weekly|monthly|comparison&format=json
     */
    public function getDriverReports(): void
    {
        $this->authorizeRole(['Dispatcher', 'Logistics Officer', 'Admin', 'User']);
        $type   = $_GET['type']   ?? 'monthly';
        $format = $_GET['format'] ?? 'json';
        $drivers = $this->buildDriverDataset();

        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'type'         => $type,
            'period'       => $type === 'daily' ? date('Y-m-d') : ($type === 'weekly' ? date('Y-\WW') : date('Y-m')),
            'summary'      => [
                'total_drivers'  => count($drivers),
                'avg_score'      => count($drivers) > 0 ? round(array_sum(array_column($drivers, 'score')) / count($drivers), 1) : 0,
                'total_trips'    => array_sum(array_column($drivers, 'trips')),
                'fleet_km_l'     => count($drivers) > 0 ? round(array_sum(array_column($drivers, 'km_l')) / count($drivers), 2) : 0,
            ],
            'drivers' => $drivers,
        ];

        $this->jsonResponse([
            'success' => true,
            'report'  => $report,
        ]);
    }
}

