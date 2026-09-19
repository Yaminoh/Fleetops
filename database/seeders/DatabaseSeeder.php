<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Dispatch;
use App\Models\DispatchLog;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\LocationLog;
use App\Models\Reservation;
use App\Models\TripRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'reybie@fleetops.com'],
            ['name' => 'Reybie R.', 'password' => Hash::make('password123'), 'role' => 'Admin', 'status' => 'active'],
        );

        // Coherent sample data for local demos and project defense.
        $users = User::factory(9)->create();
        $vehicles = Vehicle::factory(10)->create();
        $drivers = Driver::factory(10)->recycle($users)->create();
        $reservations = Reservation::factory(10)->recycle($users)->create();
        $dispatches = Dispatch::factory(10)
            ->recycle($vehicles)
            ->recycle($drivers)
            ->recycle($reservations)
            ->create();
        $trips = TripRecord::factory(10)
            ->recycle($dispatches)
            ->recycle($vehicles)
            ->recycle($drivers)
            ->create();
        LocationLog::factory(50)->recycle($vehicles)->create();
        FuelLog::factory(20)->recycle($vehicles)->recycle($dispatches)->create();
        Alert::factory(20)->recycle($vehicles)->recycle($trips)->create();
        DispatchLog::factory(20)->recycle($dispatches)->recycle($users)->create();
    }
}
