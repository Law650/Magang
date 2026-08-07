<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LogTekanan;
use App\Models\Lokasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiLogTekananController extends Controller
{
    /**
     * Terima data log tekanan dari aplikasi mobile teknisi.
     *
     * Mendukung upload foto_eviden via multipart/form-data.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lokasi_id' => ['required', 'exists:lokasis,id'],
            'nama_teknisi' => ['required', 'string', 'max:150'],
            'nilai_tekanan' => ['required', 'numeric', 'min:0'],
            'waktu_pengecekan' => ['required', 'date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'foto_eviden' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'status_aliran' => ['nullable', 'string', 'in:mengalir,tidak_mengalir'],
            'kekeruhan' => ['nullable', 'string', 'in:jernih,keruh'],
            'keterangan' => ['nullable', 'string'],
            'no_sr' => ['nullable', 'string'],
        ]);

        // Handle foto upload
        $fotoPath = null;
        if ($request->hasFile('foto_eviden')) {
            $file = $request->file('foto_eviden');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folder = 'log-tekanan/' . now()->format('Y/m');
            $fotoPath = $file->storeAs($folder, $filename, 'public');
        }

        // Klasifikasi status otomatis
        $status = LogTekanan::klasifikasiStatus((float) $validated['nilai_tekanan']);

        // Update koordinat master Lokasi jika sebelumnya kosong
        $lokasi = Lokasi::find($validated['lokasi_id']);
        if ($lokasi && isset($validated['latitude']) && isset($validated['longitude'])) {
            if (empty($lokasi->latitude) || empty($lokasi->longitude) || $lokasi->latitude == 0) {
                $lokasi->update([
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);
            }
        }

        $log = LogTekanan::create([
            'lokasi_id' => $validated['lokasi_id'],
            'user_id' => $request->user()?->id,
            'nama_teknisi' => $validated['nama_teknisi'],
            'nilai_tekanan' => $validated['nilai_tekanan'],
            'status' => $status,
            'status_aliran' => $validated['status_aliran'] ?? null,
            'kekeruhan' => $validated['kekeruhan'] ?? null,
            'waktu_pengecekan' => $validated['waktu_pengecekan'],
            'no_sr' => $validated['no_sr'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'foto_eviden' => $fotoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Log tekanan berhasil disimpan.',
            'data' => $log->load('lokasi'),
        ], 201);
    }

    /**
     * Mendapatkan rekap tekanan (status tekanan terakhir) untuk setiap daerah.
     */
    public function rekap(): JsonResponse
    {
        // Ambil semua lokasi tekanan
        $lokasis = Lokasi::where('jenis', 'tekanan')->get();

        $rekap = $lokasis->map(function ($lokasi) {
            // Ambil log terbaru secara manual untuk menghindari query deadlock latestOfMany
            $latestLog = \App\Models\LogTekanan::where('lokasi_id', $lokasi->id)
                ->orderBy('waktu_pengecekan', 'desc')
                ->first();

            return [
                'id' => $lokasi->id,
                'nama_lokasi' => $lokasi->nama_lokasi,
                'no_sr' => $lokasi->no_sr,
                'nama_pelanggan' => $lokasi->nama_pelanggan,
                'alamat' => $lokasi->alamat,
                'desa' => $lokasi->desa,
                'latitude' => $lokasi->latitude,
                'longitude' => $lokasi->longitude,
                'latest_log' => $latestLog ? [
                    'id' => $latestLog->id,
                    'nilai_tekanan' => $latestLog->nilai_tekanan,
                    'status' => $latestLog->status,
                    'status_aliran' => $latestLog->status_aliran,
                    'kekeruhan' => $latestLog->kekeruhan,
                    'keterangan' => $latestLog->keterangan,
                    'no_sr' => $latestLog->no_sr,
                    'waktu_pengecekan' => $latestLog->waktu_pengecekan,
                    'nama_teknisi' => $latestLog->nama_teknisi,
                    'foto_eviden' => $latestLog->foto_eviden ? asset('storage/' . $latestLog->foto_eviden) : null,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $rekap,
        ]);
    }

    /**
     * Update data log tekanan (fitur edit dari riwayat).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $log = LogTekanan::findOrFail($id);

        $validated = $request->validate([
            'nilai_tekanan' => ['nullable', 'numeric', 'min:0'],
            'status_aliran' => ['nullable', 'string', 'in:mengalir,tidak_mengalir'],
            'kekeruhan' => ['nullable', 'string', 'in:jernih,keruh'],
            'keterangan' => ['nullable', 'string'],
            'no_sr' => ['nullable', 'string'],
            'foto_eviden' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if (isset($validated['nilai_tekanan'])) {
            $validated['status'] = LogTekanan::klasifikasiStatus((float) $validated['nilai_tekanan']);
        }

        if ($request->hasFile('foto_eviden')) {
            if ($log->foto_eviden) {
                Storage::disk('public')->delete($log->foto_eviden);
            }
            $file = $request->file('foto_eviden');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folder = 'log-tekanan/' . now()->format('Y/m');
            $validated['foto_eviden'] = $file->storeAs($folder, $filename, 'public');
        }

        $validated['is_edited'] = true;

        $log->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Log tekanan berhasil diperbarui.',
            'data' => $log->fresh()->load('lokasi'),
        ]);
    }
}
