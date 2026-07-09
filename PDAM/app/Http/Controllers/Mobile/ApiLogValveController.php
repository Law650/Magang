<?php

namespace App\Http\Controllers\Mobile;

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
            'aksi_kerja' => ['required', 'in:buka,tutup,Buka,Tutup'],
            'jumlah_putaran' => ['required', 'numeric', 'min:0.01'],
            'keterangan' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'foto_eviden' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        // Normalize aksi_kerja ke lowercase
        $validated['aksi_kerja'] = strtolower($validated['aksi_kerja']);

        // Handle foto upload
        $fotoPath = null;
        if ($request->hasFile('foto_eviden')) {
            $file = $request->file('foto_eviden');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folder = 'log-valve/' . now()->format('Y/m');
            $fotoPath = $file->storeAs($folder, $filename, 'public');
        }

        $log = DB::transaction(function () use ($validated, $fotoPath, $request) {
            $aset = AsetValve::lockForUpdate()->findOrFail($validated['aset_id']);

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
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Log valve berhasil disimpan.',
            'data' => $log->load('asetValve.lokasi'),
        ], 201);
    }
}
