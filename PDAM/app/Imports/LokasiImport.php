<?php

namespace App\Imports;

use App\Models\Lokasi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;

class LokasiImport implements ToCollection
{
    public int $importedCount = 0;

    public function collection(Collection $rows)
    {
        $idxNoSr = -1;
        $idxNama = -1;
        $idxAlamat = -1;
        $idxDesa = -1;
        $idxLat = -1;
        $idxLng = -1;

        foreach ($rows as $index => $row) {
            // Jika belum menemukan semua index kolom, cari di baris ini
            if ($idxNoSr === -1 || $idxLat === -1) {
                foreach ($row as $k => $v) {
                    $vStr = strtolower(trim((string)$v));
                    if ($vStr === 'no. sambung' || $vStr === 'no_sr' || $vStr === 'no. sr') $idxNoSr = $k;
                    if ($vStr === 'nama pelanggan') $idxNama = $k;
                    if ($vStr === 'alamat') $idxAlamat = $k;
                    if ($vStr === 'desa') $idxDesa = $k;
                    if ($vStr === 'latitude' || $vStr === 'lat') $idxLat = $k;
                    if ($vStr === 'longitude' || $vStr === 'long' || $vStr === 'lng') $idxLng = $k;
                }
                // Tetap lanjut meskipun sudah ketemu, karena latitude/longitude mungkin di baris berikutnya
                continue;
            }

            // Jika sudah tahu index kolomnya, proses datanya
            $noSr = trim((string)($row[$idxNoSr] ?? ''));
            $namaPelanggan = trim((string)($row[$idxNama] ?? ''));

            // Abaikan baris kosong atau header
            if ($noSr === '' || strtolower($noSr) === 'no. sambung' || strtolower($noSr) === 'no_sr' || strtolower($noSr) === 'no. sr' || $namaPelanggan === '') {
                continue;
            }
            
            // Cek jika sudah ada (baik di database maupun yang baru saja diimport)
            $existing = Lokasi::where('no_sr', $noSr)->first();
            if ($existing) {
                // Log baris yang terlewat karena duplikat
                Log::warning("Skipped duplicate No SR during import: " . $noSr);
                continue; 
            }

            $latitude = $this->sanitizeCoordinate($row[$idxLat] ?? null, true);
            $longitude = $this->sanitizeCoordinate($row[$idxLng] ?? null, false);

            $desa = trim((string) ($row[$idxDesa] ?? ''));

            Lokasi::create([
                'jenis' => 'tekanan',
                'no_sr' => $noSr,
                'nama_pelanggan' => $namaPelanggan,
                'alamat' => trim((string) ($row[$idxAlamat] ?? '')),
                'desa' => $desa,
                'nama_lokasi' => $noSr . ' - ' . $namaPelanggan, // Pastikan unik
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            $this->importedCount++;
        }
    }

    /**
     * Membersihkan format koordinat
     */
    private function sanitizeCoordinate($coord, $isLatitude)
    {
        if (empty($coord)) return null;
        
        $coord = (string) $coord;
        
        // Ganti koma dengan titik (untuk desimal)
        $coord = str_replace(',', '.', $coord);

        // Jika ada banyak titik (misal format ribuan Indonesia -7.146.709)
        $parts = explode('.', $coord);
        if (count($parts) > 2) {
            $first = array_shift($parts);
            $rest = implode('', $parts);
            $coord = $first . '.' . $rest;
        }

        $val = (float) $coord;

        // Validasi logika koordinat: 
        // Jika nilai absolut sangat besar (tidak masuk akal untuk Lat/Lng Indonesia), 
        // berarti itu adalah nilai tanpa titik desimal. (Misal -7146709 seharusnya -7.146709)
        if ($isLatitude) {
            // Latitude Indonesia sekitar -11 sampai +6
            // Jika val lebih kecil dari -15 atau lebih besar dari 15, berarti itu salah format
            while (abs($val) > 15) {
                $val = $val / 10;
            }
        } else {
            // Longitude Indonesia sekitar 95 sampai 141
            // Jika val lebih besar dari 180, berarti itu salah format
            while (abs($val) > 180) {
                $val = $val / 10;
            }
        }
        
        return $val;
    }
}
