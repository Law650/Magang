<?php

namespace App\Http\Controllers\Mobile;

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

        $log = LogTekanan::create([
            'lokasi_id' => $validated['lokasi_id'],
            'user_id' => $request->user()?->id,
            'nama_teknisi' => $validated['nama_teknisi'],
            'nilai_tekanan' => $validated['nilai_tekanan'],
            'status' => $status,
            'waktu_pengecekan' => $validated['waktu_pengecekan'],
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
        // Ambil semua lokasi
        $lokasis = Lokasi::all();

        $rekap = $lokasis->map(function ($lokasi) {
            // Ambil log terbaru secara manual untuk menghindari query deadlock latestOfMany
            $latestLog = \App\Models\LogTekanan::where('lokasi_id', $lokasi->id)
                ->orderBy('waktu_pengecekan', 'desc')
                ->first();

            return [
                'id' => $lokasi->id,
                'nama_lokasi' => $lokasi->nama_lokasi,
                'latitude' => $lokasi->latitude,
                'longitude' => $lokasi->longitude,
                'latest_log' => $latestLog ? [
                    'id' => $latestLog->id,
                    'nilai_tekanan' => $latestLog->nilai_tekanan,
                    'status' => $latestLog->status,
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
}
