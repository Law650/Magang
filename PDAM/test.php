<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = new App\Livewire\PetaDistribusi();
$r = new \ReflectionMethod($p, 'getTekananMarkers');
$r->setAccessible(true);
echo json_encode($r->invoke($p));
