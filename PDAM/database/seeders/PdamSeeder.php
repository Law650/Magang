<?php

namespace Database\Seeders;

use App\Models\AsetValve;
use App\Models\Lokasi;
use App\Models\LogTekanan;
use App\Models\LogValve;
use Illuminate\Database\Seeder;

class PdamSeeder extends Seeder
{
    /**
     * Seed data demo realistis PDAM — Kabupaten Pemalang.
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
                $tutupan = round(rand(0, (int) ($spec['kapasitas'] * 100)) / 100, 2);

                $asets[] = AsetValve::create([
                    'lokasi_id' => $lokasi->id,
                    'nama_aset' => $spec['nama'] . ' - ' . str_replace('Kecamatan ', '', $lokasi->nama_lokasi),
                    'kapasitas_full_putaran' => $spec['kapasitas'],
                    'total_tutupan_saat_ini' => $tutupan,
                ]);
            }
        }

        // Log Valve (riwayat aktivitas)
        $teknisiNames = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
        ];

        foreach ($asets as $aset) {
            $logCount = rand(3, 8);
            for ($i = 0; $i < $logCount; $i++) {
                $aksi = rand(0, 1) ? 'buka' : 'tutup';
                $putaran = round(rand(100, 2000) / 100, 2);
                $waktu = now()->subDays(rand(0, 60))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                // Simulate snapshot values
                $snapshotTutupan = round(rand(0, (int) ($aset->kapasitas_full_putaran * 100)) / 100, 2);
                $snapshotBukaan = round((float) $aset->kapasitas_full_putaran - $snapshotTutupan, 2);

                LogValve::create([
                    'aset_valve_id' => $aset->id,
                    'nama_teknisi' => $teknisiNames[array_rand($teknisiNames)],
                    'waktu_kegiatan' => $waktu,
                    'aksi_kerja' => $aksi,
                    'jumlah_putaran' => $putaran,
                    'keterangan' => rand(0, 2) === 0 ? 'Pemeliharaan rutin' : (rand(0, 1) ? 'Penyesuaian debit air' : null),
                    'snapshot_sisa_bukaan' => $snapshotBukaan,
                    'snapshot_total_tutupan' => $snapshotTutupan,
                ]);
            }
        }

        // Log Tekanan
        foreach ($lokasis as $lokasi) {
            $logCount = rand(5, 15);
            for ($i = 0; $i < $logCount; $i++) {
                // Varied pressure values: mostly normal, some low, few critical
                $rand = rand(1, 100);
                if ($rand <= 65) {
                    $tekanan = round(rand(100, 250) / 100, 2); // Normal: 1.0 - 2.5
                } elseif ($rand <= 90) {
                    $tekanan = round(rand(50, 99) / 100, 2); // Rendah: 0.5 - 0.99
                } else {
                    $tekanan = round(rand(0, 49) / 100, 2); // Kritis: 0 - 0.49
                }

                $status = LogTekanan::klasifikasiStatus($tekanan);
                $waktu = now()->subDays(rand(0, 30))->subHours(rand(0, 23));

                LogTekanan::create([
                    'lokasi_id' => $lokasi->id,
                    'nilai_tekanan' => $tekanan,
                    'status' => $status,
                    'waktu_pengecekan' => $waktu,
                ]);
            }
        }
    }
}
