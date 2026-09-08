<?php
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
        '$table->foreignId("user_id")->constrained()->restrictOnDelete();',
        '$table->string("employee_id")->unique()->index();',
        '$table->string("license_number")->nullable();',
        '$table->date("license_expiry")->nullable();',
        '$table->string("status")->index()->default("Active");',
        '$table->timestamps();'
    ],
    'reservations' => [
        '$table->id();',
        '$table->string("reservation_no")->unique()->index();',
        '$table->string("employee_id")->index();',
        '$table->string("destination")->nullable();',
        '$table->string("purpose")->nullable();',
        '$table->date("requested_date")->index();',
        '$table->time("requested_time")->nullable();',
        '$table->string("vehicle_type")->nullable();',
        '$table->integer("passenger_count")->default(1);',
        '$table->text("remarks")->nullable();',
        '$table->string("status")->index()->default("Pending");',
        '$table->foreignId("approved_by")->nullable()->constrained("users")->nullOnDelete();',
        '$table->dateTime("approved_at")->nullable();',
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
    'dispatch_logs' => [
        '$table->id();',
        '$table->foreignId("dispatch_id")->constrained()->cascadeOnDelete();',
        '$table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();',
        '$table->string("action");',
        '$table->text("remarks")->nullable();',
        '$table->timestamps();'
    ],
    'fuel_logs' => [
        '$table->id();',
        '$table->foreignId("vehicle_id")->constrained()->cascadeOnDelete();',
        '$table->foreignId("trip_record_id")->nullable()->constrained()->cascadeOnDelete();',
        '$table->decimal("liters", 8, 2);',
        '$table->decimal("cost", 8, 2)->nullable();',
        '$table->date("date");',
        '$table->string("receipt_image")->nullable();',
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

$models = [
    'Vehicle' => [
        'fillable' => "['vehicle_code', 'plate_number', 'type', 'status', 'fuel_level']",
        'relations' => "
    public function dispatches() { return \$this->hasMany(Dispatch::class); }
    public function tripRecords() { return \$this->hasMany(TripRecord::class); }
    public function locationLogs() { return \$this->hasMany(LocationLog::class); }
    public function fuelLogs() { return \$this->hasMany(FuelLog::class); }
    public function alerts() { return \$this->hasMany(Alert::class); }"
    ],
    'Driver' => [
        'fillable' => "['user_id', 'employee_id', 'license_number', 'license_expiry', 'status']",
        'relations' => "
    public function user() { return \$this->belongsTo(User::class); }
    public function dispatches() { return \$this->hasMany(Dispatch::class); }
    public function tripRecords() { return \$this->hasMany(TripRecord::class); }"
    ],
    'Reservation' => [
        'fillable' => "['reservation_no', 'employee_id', 'destination', 'purpose', 'requested_date', 'requested_time', 'vehicle_type', 'passenger_count', 'remarks', 'status', 'approved_by', 'approved_at']",
        'relations' => "
    public function approver() { return \$this->belongsTo(User::class, 'approved_by'); }"
    ],
    'Dispatch' => [
        'fillable' => "['dispatch_no', 'vehicle_id', 'driver_id', 'status']",
        'relations' => "
    public function vehicle() { return \$this->belongsTo(Vehicle::class); }
    public function driver() { return \$this->belongsTo(Driver::class); }
    public function logs() { return \$this->hasMany(DispatchLog::class); }
    public function tripRecords() { return \$this->hasMany(TripRecord::class); }"
    ],
    'DispatchLog' => [
        'fillable' => "['dispatch_id', 'user_id', 'action', 'remarks']",
        'relations' => "
    public function dispatch() { return \$this->belongsTo(Dispatch::class); }
    public function user() { return \$this->belongsTo(User::class); }"
    ],
    'FuelLog' => [
        'fillable' => "['vehicle_id', 'trip_record_id', 'liters', 'cost', 'date', 'receipt_image']",
        'relations' => "
    public function vehicle() { return \$this->belongsTo(Vehicle::class); }
    public function tripRecord() { return \$this->belongsTo(TripRecord::class); }"
    ],
    'TripRecord' => [
        'fillable' => "['dispatch_id', 'vehicle_id', 'driver_id', 'origin', 'destination', 'origin_lat', 'origin_lng', 'dest_lat', 'dest_lng', 'departure_time', 'estimated_arrival', 'actual_arrival', 'total_distance', 'total_duration', 'fuel_consumption', 'status']",
        'relations' => "
    public function dispatch() { return \$this->belongsTo(Dispatch::class); }
    public function vehicle() { return \$this->belongsTo(Vehicle::class); }
    public function driver() { return \$this->belongsTo(Driver::class); }
    public function alerts() { return \$this->hasMany(Alert::class); }
    public function fuelLogs() { return \$this->hasMany(FuelLog::class); }"
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
        
        if (strpos($content, 'fillable') === false) {
            $content = preg_replace('/(use HasFactory;)/', "$1\n\n    protected \$fillable = {$data['fillable']};", $content);
        } else {
            $content = preg_replace('/protected \$fillable = \[.*?\];/s', "protected \$fillable = {$data['fillable']};", $content);
        }
        
        $content = preg_replace('/(public function.*?\})?\s*\}$/s', $data['relations'] . "\n}", $content);
        
        file_put_contents($file, $content);
        echo "Updated model $model\n";
    }
}
