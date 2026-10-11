<?php

namespace Tests\Feature;

use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationDriverSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservations_page_uses_real_active_driver_and_vehicle_records(): void
    {
        $dispatcher = User::factory()->create(['role' => 'Dispatcher']);
        $vehicle = Vehicle::factory()->create([
            'vehicle_code' => 'REAL-TRK-71',
            'plate_number' => 'ABC-7171',
            'type' => 'Delivery Van',
            'status' => 'Active',
        ]);
        $driver = Driver::factory()->create([
            'name' => 'Jordan Reyes',
            'employee_id' => 'DRV-7171',
            'status' => 'Active',
        ]);
        $busyVehicle = Vehicle::factory()->create([
            'vehicle_code' => 'BUSY-TRK',
            'plate_number' => 'BUS-0001',
            'status' => 'Active',
        ]);
        $busyDriver = Driver::factory()->create([
            'name' => 'Busy Driver',
            'employee_id' => 'DRV-BUSY',
            'status' => 'Active',
        ]);
        Dispatch::query()->create([
            'dispatch_no' => 'DSP-RESERVED-1',
            'vehicle_id' => $busyVehicle->id,
            'driver_id' => $busyDriver->id,
            'origin' => 'Depot',
            'destination' => 'Hub',
            'status' => 'Active',
        ]);
        Vehicle::factory()->create([
            'vehicle_code' => 'INACTIVE-TRK',
            'plate_number' => 'ZZZ-0000',
            'status' => 'Maintenance',
        ]);
        Driver::factory()->create([
            'name' => 'Inactive Driver',
            'employee_id' => 'DRV-INACTIVE',
            'status' => 'Inactive',
        ]);

        $response = $this->actingAs($dispatcher)->get('/reservations');

        $response->assertOk()
            ->assertSee('REAL-TRK-71 — ABC-7171 (Delivery Van)', false)
            ->assertSee('Jordan Reyes — DRV-7171', false)
            ->assertDontSee('INACTIVE-TRK')
            ->assertDontSee('DRV-INACTIVE')
            ->assertSee('role="combobox"', false)
            ->assertSee('id="res-driver-suggestions"', false)
            ->assertDontSee('id="res-passenger-count"', false);

        preg_match('/<select class="form-control" id="dd-vehicle"[^>]*>(.*?)<\/select>/s', $response->getContent(), $vehicleOptions);
        preg_match('/<select class="form-control" id="dd-driver"[^>]*>(.*?)<\/select>/s', $response->getContent(), $driverOptions);
        $this->assertStringNotContainsString('BUSY-TRK', $vehicleOptions[1] ?? '');
        $this->assertStringNotContainsString('DRV-BUSY', $driverOptions[1] ?? '');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
        $this->assertDatabaseHas('drivers', ['id' => $driver->id]);
    }

    public function test_reservation_accepts_a_database_driver_without_passenger_count(): void
    {
        $dispatcher = User::factory()->create(['role' => 'Dispatcher']);
        $driver = Driver::factory()->create([
            'employee_id' => 'DRV-RES-100',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($dispatcher)->post(route('reservations.store'), [
            'employee_id' => $driver->employee_id,
            'destination' => 'North Logistics Center',
            'purpose' => 'Equipment delivery',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '10:30',
            'vehicle_type' => 'Any / No Preference',
            'remarks' => 'Use the north entrance.',
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', [
            'employee_id' => $driver->employee_id,
            'destination' => 'North Logistics Center',
        ]);
    }

    public function test_reservation_rejects_an_employee_id_that_is_not_an_active_driver(): void
    {
        $dispatcher = User::factory()->create(['role' => 'Dispatcher']);

        $response = $this->actingAs($dispatcher)->from('/reservations')->post(route('reservations.store'), [
            'employee_id' => 'NOT-A-DRIVER',
            'destination' => 'North Logistics Center',
            'purpose' => 'Equipment delivery',
            'requested_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertRedirect('/reservations')->assertSessionHasErrors('employee_id');
        $this->assertDatabaseCount('reservations', 0);
    }
}