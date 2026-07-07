<?php

namespace Database\Seeders;

use App\Models\LogValve;
use App\Models\AsetValve;
use Illuminate\Database\Seeder;

class LogValveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Template untuk memasukkan data asli
        $data = [
            [
                'aset_valve_id' => 1, // ID dari tabel aset_valves
                'nama_teknisi' => 'Nama Teknisi Anda',
                'waktu_kegiatan' => '2026-07-07 10:00:00',
                'aksi_kerja' => 'buka', // 'buka' atau 'tutup'
                'jumlah_putaran' => 5.5,
                'keterangan' => 'Keterangan atau catatan kegiatan',
                'snapshot_sisa_bukaan' => 45.0,
                'snapshot_total_tutupan' => 13.5,
            ],
            // Tambahkan baris data asli lainnya di bawah ini
            // [
            //     'aset_valve_id' => 2,
            //     'nama_teknisi' => 'Nama Teknisi Lain',
            //     'waktu_kegiatan' => '2026-07-07 11:30:00',
            //     'aksi_kerja' => 'tutup',
            //     'jumlah_putaran' => 2.0,
            //     'keterangan' => 'Penutupan sebagian',
            //     'snapshot_sisa_bukaan' => 43.0,
            //     'snapshot_total_tutupan' => 15.5,
            // ],
        ];

        foreach ($data as $item) {
            LogValve::create($item);
        }
    }
}
