<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_database_statuses_and_updates_after_a_vehicle_changes(): void
    {
        $statuses = ['Active', ' available ', 'Reserved', 'Booked', 'In Transit', 'Maintenance', 'Delayed', 'Inactive', 'Unknown'];
        foreach ($statuses as $index => $status) {
            Vehicle::create(['vehicle_code' => 'TEST-'.$index, 'plate_number' => 'PLATE-'.$index, 'status' => $status]);
        }

        $response = $this->actingAs(User::factory()->create())->get('/dashboard');
        $response->assertOk()->assertSee('data-total="9"', false);
        $this->assertSame([
            'counts' => ['Available' => 2, 'Booked' => 3, 'Maintenance' => 1, 'Delayed' => 1, 'Unavailable' => 2],
            'total' => 9,
        ], $response->viewData('dashboard')['vehicleAvailability']);

        Vehicle::where('vehicle_code', 'TEST-0')->update(['status' => 'Maintenance']);
        $response = $this->get('/dashboard')->assertOk();
        $this->assertSame(1, $response->viewData('dashboard')['vehicleAvailability']['counts']['Available']);
        $this->assertSame(2, $response->viewData('dashboard')['vehicleAvailability']['counts']['Maintenance']);
    }

    public function test_empty_fleet_displays_zero_without_a_percentage_chart(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/dashboard');
        $response->assertOk()->assertSee('data-total="0"', false)->assertSee('style="background: var(--border);"', false);
        $this->assertSame(0, $response->viewData('dashboard')['vehicleAvailability']['total']);
    }
}
