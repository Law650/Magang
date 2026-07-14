<?php
/*
namespace Database\Seeders;

use App\Models\AsetValve;
use App\Models\Lokasi;
use Illuminate\Database\Seeder;

class MasterAsetSeeder extends Seeder
{
   
     * Seed master data aset GV.
     *
     * CARA PENGISIAN:
     * 1. Isi array $daftarLokasi: daftar desa beserta koordinatnya,
     *    dikelompokkan per kecamatan (komentar saja untuk kecamatan).
     * 2. Isi array $daftarAset: setiap baris = 1 GV, isi nama GV
     *    dan nama desa tempat GV tersebut berada.
     * 3. Pastikan nama desa di $daftarAset sesuai persis dengan key di $daftarLokasi.
     */
    // public function run(): void
    // {
        // ============================================================
        // 1. DAFTAR LOKASI (DESA) & KOORDINAT
        //    Kelompokkan per kecamatan menggunakan komentar.
        //    Key = nama desa (harus sama persis dengan field 'lokasi' di $daftarAset)
        // ============================================================
        // $daftarLokasi = [

            // --- KECAMATAN PEMALANG ---
            //'Paduraksa'     => ['lat' => -6.942547, 'lng' => 109.390231],
            // 'Mulyoharjo'  => ['lat' => -0.000000, 'lng' => 0.000000],
            // 'Kebondalem'  => ['lat' => -0.000000, 'lng' => 0.000000],
            // TODO: Tambahkan desa lainnya di Kec. Pemalang...

            // --- KECAMATAN TAMAN ---
            // 'Asemdoyong'  => ['lat' => -0.000000, 'lng' => 0.000000],
            // 'Pedurungan'  => ['lat' => -0.000000, 'lng' => 0.000000],
            // TODO: Tambahkan desa lainnya di Kec. Taman...

            // --- KECAMATAN PETARUKAN ---
            // 'Petarukan'   => ['lat' => -0.000000, 'lng' => 0.000000],
            // TODO: Tambahkan desa lainnya di Kec. Petarukan...

            // ===========================================================
            // TEMPLATE: Copy baris di bawah untuk menambahkan desa baru
            // ===========================================================
            // 'Nama Desa'   => ['lat' => -0.000000, 'lng' => 0.000000],
        // ];

        // ============================================================
        // 2. DAFTAR ASET GV
        //    Setiap baris = 1 GV.
        //    'lokasi' = nama desa (harus sesuai key di $daftarLokasi di atas)
        // ============================================================
        // $daftarAset = [

            // --- GV di Desa Paduraksa (Kec. Pemalang) ---
            // ['nama' => 'GV 14"',  'lokasi' => 'Paduraksa',  'kapasitas' => 58.50, 'tutupan' => 0.00],
            // ['nama' => 'GV 12"',  'lokasi' => 'Paduraksa',  'kapasitas' => 32.00, 'tutupan' => 0.00],
            // ['nama' => 'GV 16"',  'lokasi' => 'Paduraksa',  'kapasitas' => 20.00, 'tutupan' => 0.00],

            // --- GV di Desa ... (Kec. Taman) ---
            // ['nama' => 'GV 6"',  'lokasi' => 'Asemdoyong',  'kapasitas' => 58.50, 'tutupan' => 0.00],

            // --- GV di Desa ... (Kec. Petarukan) ---
            // ['nama' => 'GV 8"',  'lokasi' => 'Petarukan',   'kapasitas' => 72.25, 'tutupan' => 0.00],

            // ===========================================================
            // TEMPLATE: Copy baris di bawah untuk menambahkan GV baru
            // ===========================================================
            // ['nama' => 'GV X"', 'lokasi' => 'Nama Desa', 'kapasitas' => 0.00, 'tutupan' => 0.00],
        // ];

        // ============================================================
        // PROSES INSERT (tidak perlu diubah)
        // ============================================================
        
        /*
        $lokasiCache = [];
        $countPerLokasi = [];

        foreach ($daftarAset as $aset) {
            $namaDesa = $aset['lokasi'];

            // Ambil koordinat dari $daftarLokasi, atau default 0 jika belum didefinisikan
            $koordinat = $daftarLokasi[$namaDesa] ?? ['lat' => 0, 'lng' => 0];

            // Buat atau ambil lokasi (cache agar tidak query berulang)
            if (!isset($lokasiCache[$namaDesa])) {
                $lokasiCache[$namaDesa] = Lokasi::firstOrCreate(
                    ['nama_lokasi' => $namaDesa],
                    [
                        'latitude'  => $koordinat['lat'],
                        'longitude' => $koordinat['lng'],
                    ]
                );
            }

            $lokasi = $lokasiCache[$namaDesa];

            // Insert aset GV
            AsetValve::create([
                'lokasi_id'              => $lokasi->id,
                'nama_aset'              => $aset['nama'] . ' - ' . $lokasi->nama_lokasi,
                'kapasitas_full_putaran'  => $aset['kapasitas'],
                'total_tutupan_saat_ini'  => $aset['tutupan'],
            ]);

            // Hitung per lokasi untuk info output
            $countPerLokasi[$namaDesa] = ($countPerLokasi[$namaDesa] ?? 0) + 1;
        }

        // Output ringkasan
        foreach ($countPerLokasi as $nama => $count) {
            $this->command->info("✓ {$nama}: {$count} aset GV berhasil di-seed.");
        }

        $this->command->info("═══════════════════════════════════════");
        $this->command->info("Total: " . count($countPerLokasi) . " desa, " . count($daftarAset) . " aset GV");
    }
}
*/
