<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

$lokasiCols = DB::select('DESCRIBE lokasis');
echo "LOKASIS:\n";
foreach($lokasiCols as $col) {
    if (str_contains($col->Field, 'latitude') || str_contains($col->Field, 'longitude')) {
        echo $col->Field . " -> " . $col->Type . "\n";
    }
}

$logValveCols = DB::select('DESCRIBE log_valves');
echo "LOG VALVES:\n";
foreach($logValveCols as $col) {
    if (str_contains($col->Field, 'latitude') || str_contains($col->Field, 'longitude')) {
        echo $col->Field . " -> " . $col->Type . "\n";
    }
}
