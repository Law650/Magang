<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$aset = \App\Models\AsetValve::create([
    'lokasi_id' => 1,
    'nama_aset' => 'GV-TEST-PHP-' . time(),
    'kapasitas_full_putaran' => 10,
    'total_tutupan_saat_ini' => 0.00
]);

echo "Sisa Bukaan for Full Bukaan (total_tutupan=0, kapasitas=10): " . $aset->sisa_bukaan . "\n";

$aset2 = \App\Models\AsetValve::create([
    'lokasi_id' => 1,
    'nama_aset' => 'GV-TEST-PHP-' . (time() + 1),
    'kapasitas_full_putaran' => 10,
    'total_tutupan_saat_ini' => 10.00
]);
echo "Sisa Bukaan for Full Tutupan (total_tutupan=10, kapasitas=10): " . $aset2->sisa_bukaan . "\n";

