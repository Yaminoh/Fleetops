<?php
// Script to generate all migrations, models, factories, seeders

// 1. Migrations
$migrationsDir = 'database/migrations';
$migrations = glob($migrationsDir . '/*_create_*_table.php');

$tables = [
    'vehicles' => [
        '$table->id();',
        '$table->string("vehicle_code")->unique()->index();',
        '$table->string("plate_number")->unique()->index();',
        '$table->string("type")->nullable();',
        '$table->string("status")->index()->default("Active");',
        '$table->decimal("fuel_level", 5, 2)->default(100);',
        '$table->timestamps();'
    ],
    'drivers' => [
        '$table->id();',
        '$table->string("name");',
        '$table->string("employee_id")->unique()->index();',
        '$table->string("role")->nullable();',
        '$table->decimal("score", 3, 1)->nullable();',
        '$table->string("status")->index()->default("Active");',
        '$table->timestamps();'
    ],
    'reservations' => [
        '$table->id();',
        '$table->string("reservation_no")->unique()->index();',
        '$table->foreignId("vehicle_id")->constrained()->restrictOnDelete();',
        '$table->foreignId("driver_id")->constrained()->restrictOnDelete();',
        '$table->date("requested_date")->index();',
        '$table->string("status")->index()->default("Pending");',
        '$table->timestamps();'
    ],
    'dispatches' => [
        '$table->id();',
        '$table->string("dispatch_no")->unique()->index();',
        '$table->foreignId("vehicle_id")->constrained()->restrictOnDelete();',
        '$table->foreignId("driver_id")->constrained()->restrictOnDelete();',
        '$table->string("status")->index()->default("Scheduled");',
        '$table->timestamps();'
    ],
    'trip_records' => [
        '$table->id();',
        '$table->foreignId("dispatch_id")->nullable()->constrained()->cascadeOnDelete();',
        '$table->foreignId("vehicle_id")->constrained()->restrictOnDelete();',
        '$table->foreignId("driver_id")->constrained()->restrictOnDelete();',
        '$table->string("origin")->nullable();',
        '$table->string("destination")->nullable();',
        '$table->decimal("origin_lat", 10, 7)->nullable();',
        '$table->decimal("origin_lng", 10, 7)->nullable();',
        '$table->decimal("dest_lat", 10, 7)->nullable();',
        '$table->decimal("dest_lng", 10, 7)->nullable();',
        '$table->dateTime("departure_time")->nullable();',
        '$table->dateTime("estimated_arrival")->nullable();',
        '$table->dateTime("actual_arrival")->nullable()->index();',
        '$table->decimal("total_distance", 8, 2)->nullable();',
        '$table->integer("total_duration")->nullable();',
        '$table->decimal("fuel_consumption", 8, 2)->nullable();',
        '$table->string("status")->default("Active");',
        '$table->timestamps();'
    ],
    'location_logs' => [
        '$table->id();',
        '$table->foreignId("vehicle_id")->constrained()->cascadeOnDelete();',
        '$table->decimal("latitude", 10, 7);',
        '$table->decimal("longitude", 10, 7);',
        '$table->decimal("speed", 5, 2)->nullable();',
        '$table->decimal("fuel_level", 5, 2)->nullable();',
        '$table->dateTime("timestamp")->index();',
        '$table->timestamps();'
    ],
    'alerts' => [
        '$table->id();',
        '$table->foreignId("vehicle_id")->constrained()->cascadeOnDelete();',
        '$table->foreignId("trip_record_id")->nullable()->constrained()->cascadeOnDelete();',
        '$table->string("type")->nullable();',
        '$table->text("message");',
        '$table->string("severity")->index()->default("info");',
        '$table->timestamps();'
    ]
];

foreach ($migrations as $mig) {
    $content = file_get_contents($mig);
    foreach ($tables as $table => $fields) {
        if (strpos($mig, "create_{$table}_table") !== false) {
            $fieldsStr = implode("\n            ", $fields);
            $content = preg_replace('/Schema::create\(\''.$table.'\', function \(Blueprint \$table\) \{.*?\};\)/s', "Schema::create('$table', function (Blueprint \$table) {\n            $fieldsStr\n        });", $content);
            file_put_contents($mig, $content);
            echo "Updated migration for $table\n";
        }
    }
}

// 2. Models
$models = [
    'Vehicle' => [
        'fillable' => "['vehicle_code', 'plate_number', 'type', 'status', 'fuel_level']",
        'relations' => "
    public function reservations() { return \$this->hasMany(Reservation::class); }
    public function dispatches() { return \$this->hasMany(Dispatch::class); }
    public function tripRecords() { return \$this->hasMany(TripRecord::class); }
    public function locationLogs() { return \$this->hasMany(LocationLog::class); }
    public function alerts() { return \$this->hasMany(Alert::class); }"
    ],
    'Driver' => [
        'fillable' => "['name', 'employee_id', 'role', 'score', 'status']",
        'relations' => "
    public function reservations() { return \$this->hasMany(Reservation::class); }
    public function dispatches() { return \$this->hasMany(Dispatch::class); }
    public function tripRecords() { return \$this->hasMany(TripRecord::class); }"
    ],
    'Reservation' => [
        'fillable' => "['reservation_no', 'vehicle_id', 'driver_id', 'requested_date', 'status']",
        'relations' => "
    public function vehicle() { return \$this->belongsTo(Vehicle::class); }
    public function driver() { return \$this->belongsTo(Driver::class); }"
    ],
    'Dispatch' => [
        'fillable' => "['dispatch_no', 'vehicle_id', 'driver_id', 'status']",
        'relations' => "
    public function vehicle() { return \$this->belongsTo(Vehicle::class); }
    public function driver() { return \$this->belongsTo(Driver::class); }
    public function tripRecords() { return \$this->hasMany(TripRecord::class); }"
    ],
    'TripRecord' => [
        'fillable' => "['dispatch_id', 'vehicle_id', 'driver_id', 'origin', 'destination', 'origin_lat', 'origin_lng', 'dest_lat', 'dest_lng', 'departure_time', 'estimated_arrival', 'actual_arrival', 'total_distance', 'total_duration', 'fuel_consumption', 'status']",
        'relations' => "
    public function dispatch() { return \$this->belongsTo(Dispatch::class); }
    public function vehicle() { return \$this->belongsTo(Vehicle::class); }
    public function driver() { return \$this->belongsTo(Driver::class); }
    public function alerts() { return \$this->hasMany(Alert::class); }"
    ],
    'LocationLog' => [
        'fillable' => "['vehicle_id', 'latitude', 'longitude', 'speed', 'fuel_level', 'timestamp']",
        'relations' => "
    public function vehicle() { return \$this->belongsTo(Vehicle::class); }"
    ],
    'Alert' => [
        'fillable' => "['vehicle_id', 'trip_record_id', 'type', 'message', 'severity']",
        'relations' => "
    public function vehicle() { return \$this->belongsTo(Vehicle::class); }
    public function tripRecord() { return \$this->belongsTo(TripRecord::class); }"
    ]
];

foreach ($models as $model => $data) {
    $file = "app/Models/$model.php";
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Add fillable if not exists
        if (strpos($content, 'fillable') === false) {
            $content = preg_replace('/(use HasFactory;)/', "$1\n\n    protected \$fillable = {$data['fillable']};", $content);
        }
        
        // Add relations
        $content = preg_replace('/\}$/', $data['relations'] . "\n}", $content);
        
        file_put_contents($file, $content);
        echo "Updated model $model\n";
    }
}
