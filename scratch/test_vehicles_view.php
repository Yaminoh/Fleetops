<?php

if (!class_exists(\Illuminate\Foundation\Application::class)) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

use App\Models\User;
use App\Http\Controllers\PageController;

$user = User::first();
$req = \Illuminate\Http\Request::create('/vehicles', 'GET');
$req->setUserResolver(fn() => $user);

$controller = new PageController();
$view = $controller->show('vehicles');
$html = $view->render();

echo "Vehicles Page Render Success!" . PHP_EOL;
echo "HTML Length: " . strlen($html) . " bytes." . PHP_EOL;
echo "DB Vehicles count loaded: " . count($view->getData()['dashboard']['vehiclesList']) . PHP_EOL;
