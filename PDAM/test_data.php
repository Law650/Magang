<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$logs = App\Models\LogValve::orderBy('id', 'desc')->limit(5)->get();
foreach($logs as $log) {
    echo "LOG [{$log->id}]: {$log->latitude}, {$log->longitude}\n";
}

$lokasis = App\Models\Lokasi::orderBy('id', 'desc')->limit(5)->get();
foreach($lokasis as $lok) {
    echo "LOKASI [{$lok->id}]: {$lok->latitude}, {$lok->longitude}\n";
}
