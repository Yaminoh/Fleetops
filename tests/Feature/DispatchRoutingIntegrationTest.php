<?php

namespace Tests\Feature;

use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\TripRecord;
use App\Models\User;
use App\Models\Vehicle;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Tests\TestCase;

class DispatchRoutingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private string|false $originalOrsApiKey = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalOrsApiKey = getenv('OPENROUTESERVICE_API_KEY');
        putenv('OPENROUTESERVICE_API_KEY=fake-test-key');

        Http::preventStrayRequests();
        Http::fake([
            'https://api.openrouteservice.org/geocode/search*' => Http::response([
                'features' => [[
                    'geometry' => ['coordinates' => [120.9842, 14.5995]],
                ]],
            ]),
            'https://api.heigit.org/openrouteservice/v2/directions/driving-car' => Http::response([
                'routes' => [[
                    'summary' => ['distance' => 15000, 'duration' => 1800],
                ]],
            ]),
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->originalOrsApiKey === false) {
            putenv('OPENROUTESERVICE_API_KEY');
        } else {
            putenv('OPENROUTESERVICE_API_KEY=' . $this->originalOrsApiKey);
        }

        parent::tearDown();
    }

    public function test_direct_dispatch_creates_an_active_trip_linked_to_its_vehicle_and_driver(): void
    {
        $user = User::factory()->create(['role' => 'Dispatcher']);
        $vehicle = Vehicle::factory()->create(['status' => 'Active']);
        $driver = Driver::factory()->create(['status' => 'Active']);

        $response = $this->actingAs($user)->post(route('dispatches.store'), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin' => 'Main Office, Manila',
            'destination' => 'Quezon City Logistics Hub',
            'priority' => 'High',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $dispatch = Dispatch::query()->firstOrFail();
        $trip = TripRecord::query()->where('dispatch_id', $dispatch->id)->firstOrFail();

        $this->assertSame('Active', $dispatch->status);
        $this->assertSame($vehicle->id, $dispatch->vehicle_id);
        $this->assertSame($driver->id, $dispatch->driver_id);
        $this->assertSame($dispatch->id, $trip->dispatch_id);
        $this->assertSame($vehicle->id, $trip->vehicle_id);
        $this->assertSame($driver->id, $trip->driver_id);
        $this->assertSame('Main Office, Manila', $trip->origin);
        $this->assertSame('Quezon City Logistics Hub', $trip->destination);
        $this->assertSame('Active', $trip->status);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'status' => 'In Transit']);
    }

    public function test_active_trip_api_includes_a_new_direct_dispatch_with_its_database_id_mapping(): void
    {
        $user = User::factory()->create(['role' => 'Dispatcher']);
        $vehicle = Vehicle::factory()->create(['status' => 'Active']);
        $driver = Driver::factory()->create(['status' => 'Active']);

        $this->actingAs($user)->post(route('dispatches.store'), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin' => 'Main Office, Manila',
            'destination' => 'Quezon City Logistics Hub',
            'priority' => 'High',
        ])->assertRedirect()->assertSessionHas('success');

        $dispatch = Dispatch::query()->firstOrFail();
        $trip = TripRecord::query()->where('dispatch_id', $dispatch->id)->firstOrFail();
        $idOffset = (new ReflectionClass(ApiController::class))
            ->getReflectionConstant('DATABASE_TRIP_ID_OFFSET')
            ->getValue();

        $response = $this->getJson('/api/trips/active');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('trips.0.id', $idOffset + $trip->id)
            ->assertJsonPath('trips.0.trip_record_id', $trip->id)
            ->assertJsonPath('trips.0.dispatch_id', $dispatch->id)
            ->assertJsonPath('trips.0.vehicle_id', $vehicle->id)
            ->assertJsonPath('trips.0.driver_id', $driver->id)
            ->assertJsonPath('trips.0.origin', 'Main Office, Manila')
            ->assertJsonPath('trips.0.destination', 'Quezon City Logistics Hub')
            ->assertJsonPath('trips.0.status', 'Active');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'status' => 'In Transit']);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/geocode/search'));
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST' &&
            str_contains($request->url(), '/directions/driving-car'));
    }
}