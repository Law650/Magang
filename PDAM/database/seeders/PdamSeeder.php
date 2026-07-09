<?php

namespace Database\Seeders;

use App\Models\AsetValve;
use App\Models\Lokasi;
use Illuminate\Database\Seeder;

class PdamSeeder extends Seeder
{
    /**
     * Seed data PDAM Pemalang.
     */
    public function run(): void
    {
        // Seluruh kecamatan di Kabupaten Pemalang dengan koordinat GPS
        $lokasiData = [
            ['nama' => 'Kecamatan Pemalang',       'lat' => -6.8885, 'lng' => 109.3825],
            ['nama' => 'Kecamatan Taman',           'lat' => -6.8920, 'lng' => 109.3580],
            ['nama' => 'Kecamatan Petarukan',       'lat' => -6.8960, 'lng' => 109.4200],
            ['nama' => 'Kecamatan Comal',           'lat' => -6.9050, 'lng' => 109.5250],
            ['nama' => 'Kecamatan Ulujami',         'lat' => -6.9020, 'lng' => 109.5620],
            ['nama' => 'Kecamatan Bodeh',           'lat' => -6.9500, 'lng' => 109.4500],
            ['nama' => 'Kecamatan Ampelgading',     'lat' => -6.9150, 'lng' => 109.4700],
            ['nama' => 'Kecamatan Bantarbolang',    'lat' => -6.9700, 'lng' => 109.3000],
            ['nama' => 'Kecamatan Randudongkal',    'lat' => -7.0300, 'lng' => 109.3200],
            ['nama' => 'Kecamatan Warungpring',     'lat' => -7.0500, 'lng' => 109.3800],
            ['nama' => 'Kecamatan Moga',            'lat' => -7.0800, 'lng' => 109.3500],
            ['nama' => 'Kecamatan Belik',           'lat' => -7.1000, 'lng' => 109.4000],
            ['nama' => 'Kecamatan Pulosari',        'lat' => -7.1200, 'lng' => 109.3600],
            ['nama' => 'Kecamatan Watukumpul',      'lat' => -7.1500, 'lng' => 109.3300],
        ];

        $lokasis = [];
        foreach ($lokasiData as $data) {
            $lokasis[] = Lokasi::create([
                'nama_lokasi' => $data['nama'],
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
            ]);
        }

        // Aset Valve per lokasi
        $valveSpecs = [
            ['nama' => 'GV 6"', 'kapasitas' => 58.50],
            ['nama' => 'GV 4"', 'kapasitas' => 32.00],
            ['nama' => 'GV 8"', 'kapasitas' => 72.25],
            ['nama' => 'GV 3"', 'kapasitas' => 24.00],
            ['nama' => 'GV 10"', 'kapasitas' => 96.50],
            ['nama' => 'GV 2"', 'kapasitas' => 16.75],
        ];

        $asets = [];
        foreach ($lokasis as $index => $lokasi) {
            // 2-3 valve per lokasi
            $valveCount = ($index % 3 === 0) ? 3 : 2;
            for ($i = 0; $i < $valveCount; $i++) {
                $spec = $valveSpecs[($index + $i) % count($valveSpecs)];
                // Set total_tutupan_saat_ini ke 0 dulu, nanti di-update setelah log di-generate
                $asets[] = AsetValve::create([
                    'lokasi_id' => $lokasi->id,
                    'nama_aset' => $spec['nama'] . ' - ' . str_replace('Kecamatan ', '', $lokasi->nama_lokasi),
                    'kapasitas_full_putaran' => $spec['kapasitas'],
                    'total_tutupan_saat_ini' => 0, 
                ]);
            }
        }
    }
}
