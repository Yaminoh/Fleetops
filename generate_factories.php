<?php
// Script to generate all factories and seeders

$factories = [
    'VehicleFactory' => "
            'vehicle_code' => \$this->faker->unique()->bothify('TRK-####'),
            'plate_number' => \$this->faker->unique()->bothify('???-####'),
            'type' => \$this->faker->randomElement(['Heavy Cargo Truck', 'Delivery Van', 'Refrigerated Transport']),
            'status' => \$this->faker->randomElement(['Active', 'Maintenance', 'Inactive']),
            'fuel_level' => \$this->faker->randomFloat(2, 10, 100),
    ",
    'DriverFactory' => "
            'name' => \$this->faker->name(),
            'employee_id' => \$this->faker->unique()->bothify('DRV-####'),
            'role' => \$this->faker->jobTitle(),
            'score' => \$this->faker->randomFloat(1, 1, 10),
            'status' => \$this->faker->randomElement(['Active', 'Inactive']),
    ",
    'ReservationFactory' => "
            'reservation_no' => \$this->faker->unique()->bothify('RES-####'),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'driver_id' => \App\Models\Driver::factory(),
            'requested_date' => \$this->faker->date(),
            'status' => \$this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
    ",
    'DispatchFactory' => "
            'dispatch_no' => \$this->faker->unique()->bothify('DSP-####'),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'driver_id' => \App\Models\Driver::factory(),
            'status' => \$this->faker->randomElement(['Scheduled', 'Active', 'Completed']),
    ",
    'TripRecordFactory' => "
            'dispatch_id' => \App\Models\Dispatch::factory(),
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'driver_id' => \App\Models\Driver::factory(),
            'origin' => \$this->faker->city(),
            'destination' => \$this->faker->city(),
            'origin_lat' => \$this->faker->latitude(),
            'origin_lng' => \$this->faker->longitude(),
            'dest_lat' => \$this->faker->latitude(),
            'dest_lng' => \$this->faker->longitude(),
            'departure_time' => \$this->faker->dateTimeThisMonth(),
            'estimated_arrival' => \$this->faker->dateTimeThisMonth(),
            'actual_arrival' => \$this->faker->optional()->dateTimeThisMonth(),
            'total_distance' => \$this->faker->randomFloat(2, 5, 500),
            'total_duration' => \$this->faker->numberBetween(30, 600),
            'fuel_consumption' => \$this->faker->randomFloat(2, 2, 50),
            'status' => \$this->faker->randomElement(['Active', 'Completed']),
    ",
    'LocationLogFactory' => "
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'latitude' => \$this->faker->latitude(),
            'longitude' => \$this->faker->longitude(),
            'speed' => \$this->faker->randomFloat(2, 0, 120),
            'fuel_level' => \$this->faker->randomFloat(2, 0, 100),
            'timestamp' => \$this->faker->dateTimeThisMonth(),
    ",
    'AlertFactory' => "
            'vehicle_id' => \App\Models\Vehicle::factory(),
            'trip_record_id' => \App\Models\TripRecord::factory(),
            'type' => \$this->faker->word(),
            'message' => \$this->faker->sentence(),
            'severity' => \$this->faker->randomElement(['info', 'warning', 'critical']),
    "
];

foreach ($factories as $factory => $fields) {
    $file = "database/factories/$factory.php";
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = preg_replace('/return \[.*?(?=\];)/s', "return [\n$fields        ", $content);
        file_put_contents($file, $content);
        echo "Updated factory $factory\n";
    }
}

$dbSeederFile = "database/seeders/DatabaseSeeder.php";
if (file_exists($dbSeederFile)) {
    $content = file_get_contents($dbSeederFile);
    $seederCalls = "
        \\App\\Models\\Vehicle::factory(10)->create();
        \\App\\Models\\Driver::factory(10)->create();
        \\App\\Models\\Reservation::factory(10)->create();
        \\App\\Models\\Dispatch::factory(10)->create();
        \\App\\Models\\TripRecord::factory(10)->create();
        \\App\\Models\\LocationLog::factory(50)->create();
        \\App\\Models\\Alert::factory(20)->create();
    ";
    
    // Replace the run method body
    $content = preg_replace('/public function run\(\): void\s*\{.*?\}/s', "public function run(): void {\n$seederCalls\n    }", $content);
    file_put_contents($dbSeederFile, $content);
    echo "Updated DatabaseSeeder\n";
}
