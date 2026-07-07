<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use App\Models\LogTekanan;
use Illuminate\Database\Seeder;

class LogTekananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Template untuk memasukkan data asli
        $data = [
            [
                'lokasi_id' => 1, // ID dari tabel lokasis
                'nilai_tekanan' => 1.5,
                'status' => 'normal', // normal, rendah, atau kritis
                'waktu_pengecekan' => '2026-07-07 10:00:00',
            ],
            // Tambahkan baris data asli lainnya di bawah ini
            // [
            //     'lokasi_id' => 2,
            //     'nilai_tekanan' => 0.8,
            //     'status' => 'rendah',
            //     'waktu_pengecekan' => '2026-07-07 11:00:00',
            // ],
        ];

        foreach ($data as $item) {
            LogTekanan::create($item);
        }
    }
}
