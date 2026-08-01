<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\AsetValve;
use App\Models\LogValve;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiLogValveController extends Controller
{
    /**
     * Terima data log valve dari aplikasi mobile teknisi.
     * Business logic: validasi, update total_tutupan (clamp), simpan snapshot, DB::transaction.
     *
     * Mendukung upload foto_eviden via multipart/form-data.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'aset_id' => ['required', 'exists:aset_valves,id'],
            'nama_teknisi' => ['required', 'string', 'max:150'],
            'waktu_kegiatan' => ['required', 'date'],
            'aksi_kerja' => ['required', 'in:buka,tutup,cek,Buka,Tutup,Cek'],
            'jumlah_putaran' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'foto_eviden' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'foto_eviden_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'kapasitas_full' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Normalize aksi_kerja ke lowercase
        $validated['aksi_kerja'] = strtolower($validated['aksi_kerja']);
        
        // Map 'cek' ke 'buka' agar tidak error di kolom ENUM MySQL, karena putaran = 0 tidak akan merubah status
        if ($validated['aksi_kerja'] === 'cek') {
            $validated['aksi_kerja'] = 'buka';
        }

        // Handle foto upload
        $fotoPath = null;
        if ($request->hasFile('foto_eviden')) {
            $file = $request->file('foto_eviden');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folder = 'log-valve/' . now()->format('Y/m');
            $fotoPath = $file->storeAs($folder, $filename, 'public');
        }

        $fotoPath2 = null;
        if ($request->hasFile('foto_eviden_2')) {
            $file = $request->file('foto_eviden_2');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folder = 'log-valve/' . now()->format('Y/m');
            $fotoPath2 = $file->storeAs($folder, $filename, 'public');
        }

        $log = DB::transaction(function () use ($validated, $fotoPath, $fotoPath2, $request) {
            $aset = AsetValve::lockForUpdate()->findOrFail($validated['aset_id']);
            
            // Allow updating kapasitas_full from mobile
            if (isset($validated['kapasitas_full'])) {
                $aset->kapasitas_full_putaran = $validated['kapasitas_full'];
            }

            $totalTutupan = (float) $aset->total_tutupan_saat_ini;
            $kapasitasFull = (float) $aset->kapasitas_full_putaran;
            $putaran = (float) $validated['jumlah_putaran'];

            // Kalkulasi: + jika tutup, - jika buka, clamp antara 0 dan kapasitas full
            if ($validated['aksi_kerja'] === 'tutup') {
                $totalTutupan = min($totalTutupan + $putaran, $kapasitasFull);
            } else {
                $totalTutupan = max($totalTutupan - $putaran, 0);
            }

            $sisaBukaan = round($kapasitasFull - $totalTutupan, 2);

            // Update aset valve
            $aset->update([
                'total_tutupan_saat_ini' => round($totalTutupan, 2),
            ]);

            // Jika aset valve belum memiliki koordinat, atau jika kita ingin update, simpan koordinatnya
            if (isset($validated['latitude']) && isset($validated['longitude'])) {
                if (empty($aset->latitude) || empty($aset->longitude) || $request->input('aksi_kerja') === 'cek') {
                    $aset->update([
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                    ]);
                }
            }

            // Simpan log dengan snapshot
            return LogValve::create([
                'aset_valve_id' => $aset->id,
                'user_id' => $request->user()?->id,
                'nama_teknisi' => $validated['nama_teknisi'],
                'waktu_kegiatan' => $validated['waktu_kegiatan'],
                'aksi_kerja' => $validated['aksi_kerja'],
                'jumlah_putaran' => $putaran,
                'keterangan' => $validated['keterangan'] ?? null,
                'snapshot_sisa_bukaan' => $sisaBukaan,
                'snapshot_total_tutupan' => round($totalTutupan, 2),
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'foto_eviden' => $fotoPath,
                'foto_eviden_2' => $fotoPath2,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Log valve berhasil disimpan.',
            'data' => $log->load('asetValve.lokasi'),
        ], 201);
    }

    /**
     * Update data log valve (fitur edit dari riwayat).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $log = LogValve::findOrFail($id);

        $validated = $request->validate([
            'aksi_kerja' => ['nullable', 'in:buka,tutup,cek,Buka,Tutup,Cek'],
            'jumlah_putaran' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
            'foto_eviden' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'foto_eviden_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if (isset($validated['aksi_kerja'])) {
            $validated['aksi_kerja'] = strtolower($validated['aksi_kerja']);
            if ($validated['aksi_kerja'] === 'cek') {
                $validated['aksi_kerja'] = 'buka';
            }
        }

        if ($request->hasFile('foto_eviden')) {
            // Delete old if exists
            if ($log->foto_eviden) {
                Storage::disk('public')->delete($log->foto_eviden);
            }
            $file = $request->file('foto_eviden');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folder = 'log-valve/' . now()->format('Y/m');
            $validated['foto_eviden'] = $file->storeAs($folder, $filename, 'public');
        }

        if ($request->hasFile('foto_eviden_2')) {
            if ($log->foto_eviden_2) {
                Storage::disk('public')->delete($log->foto_eviden_2);
            }
            $file = $request->file('foto_eviden_2');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folder = 'log-valve/' . now()->format('Y/m');
            $validated['foto_eviden_2'] = $file->storeAs($folder, $filename, 'public');
        }

        $validated['is_edited'] = true;

        DB::transaction(function () use ($log, $validated) {
            // Jika ada perubahan aksi atau putaran, kita perlu merevisi data aset (ini bisa kompleks jika bukan log terakhir)
            // Untuk penyederhanaan (karena ini log), kita update log-nya saja dan mungkin menyesuaikan snapshot_total_tutupan jika memungkinkan,
            // Namun yang aman adalah membiarkan total_tutupan aset sesuai update manual di tempat lain jika historisnya berubah.
            // Saat ini kita update isi log saja.
            
            $log->update($validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'Log valve berhasil diperbarui.',
            'data' => $log->fresh()->load('asetValve.lokasi'),
        ]);
    }
}
