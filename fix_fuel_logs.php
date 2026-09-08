<?php
// 1. Update fuel_logs migration
$migDir = 'database/migrations';
$migFiles = glob($migDir . '/*_create_fuel_logs_table.php');
if (!empty($migFiles)) {
    $mig = $migFiles[0];
    $content = file_get_contents($mig);
    $content = str_replace(
        '$table->foreignId("trip_record_id")->nullable()->constrained()->cascadeOnDelete();',
        '$table->foreignId("dispatch_id")->nullable()->constrained()->cascadeOnDelete();',
        $content
    );
    file_put_contents($mig, $content);
    echo "Updated fuel_logs migration\n";
}

// 2. Update FuelLog Model
$fuelLogModel = 'app/Models/FuelLog.php';
if (file_exists($fuelLogModel)) {
    $content = file_get_contents($fuelLogModel);
    $content = str_replace("'trip_record_id'", "'dispatch_id'", $content);
    $content = str_replace('belongsTo(TripRecord::class)', 'belongsTo(Dispatch::class)', $content);
    $content = str_replace('tripRecord()', 'dispatch()', $content);
    file_put_contents($fuelLogModel, $content);
    echo "Updated FuelLog model\n";
}

// 3. Update TripRecord Model (remove fuelLogs)
$tripRecordModel = 'app/Models/TripRecord.php';
if (file_exists($tripRecordModel)) {
    $content = file_get_contents($tripRecordModel);
    $content = preg_replace('/public function fuelLogs\(\).*?\}/', '', $content);
    file_put_contents($tripRecordModel, $content);
    echo "Updated TripRecord model\n";
}

// 4. Update Dispatch Model (add fuelLogs)
$dispatchModel = 'app/Models/Dispatch.php';
if (file_exists($dispatchModel)) {
    $content = file_get_contents($dispatchModel);
    if (strpos($content, 'fuelLogs()') === false) {
        $content = preg_replace('/\}$/', "    public function fuelLogs() { return \$this->hasMany(FuelLog::class); }\n}", $content);
        file_put_contents($dispatchModel, $content);
        echo "Updated Dispatch model\n";
    }
}
