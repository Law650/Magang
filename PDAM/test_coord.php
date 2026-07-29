<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$lat1 = -6.9090573;
$lon1 = 109.381683;
$lat2 = -6.90906;
$lon2 = 109.3816;

$earthRadius = 6371000;
$dLat = deg2rad($lat2 - $lat1);
$dLon = deg2rad($lon2 - $lon1);
$a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
$jarak = $earthRadius * $c;
echo 'JARAK: ' . $jarak . PHP_EOL;
