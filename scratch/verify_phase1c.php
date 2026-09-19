<?php

if (!class_exists(\Illuminate\Foundation\Application::class)) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

use App\Models\User;
use App\Http\Controllers\PageController;

echo "==========================================" . PHP_EOL;
echo "  FLEETOPS PHASE 1C DASHBOARD VERIFICATION" . PHP_EOL;
echo "==========================================" . PHP_EOL . PHP_EOL;

$user = User::first() ?? new User(['id' => 1, 'name' => 'Admin User', 'role' => 'Admin']);

$req = \Illuminate\Http\Request::create('/dashboard', 'GET');
$req->setUserResolver(fn() => $user);

$pageController = new PageController();
$view = $pageController->show('dashboard');
$dashboard = $view->getData()['dashboard'];

echo "Stats array count: " . count($dashboard['stats']) . PHP_EOL;
echo "Metrics array:" . PHP_EOL;
print_r($dashboard['metrics']);

echo PHP_EOL . "Testing Blade view rendering..." . PHP_EOL;
$rendered = $view->render();
echo "Render length: " . strlen($rendered) . " bytes." . PHP_EOL;

echo PHP_EOL . "PHASE 1C VERIFICATION SUCCESSFUL!" . PHP_EOL;
