<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use App\Models\LogTekanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiLogTekananController extends Controller
{
    /**
     * Terima data log tekanan dari aplikasi mobile teknisi.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:150'],
            'nilai_tekanan' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'waktu_pengecekan' => ['required', 'date'],
        ]);

        $lokasi = Lokasi::firstOrCreate(
            ['nama_lokasi' => $validated['nama_lokasi']]
        );

        $status = LogTekanan::klasifikasiStatus((float) $validated['nilai_tekanan']);

        $log = LogTekanan::create([
            'lokasi_id' => $lokasi->id,
            'nilai_tekanan' => $validated['nilai_tekanan'],
            'status' => $status,
            'waktu_pengecekan' => $validated['waktu_pengecekan'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Log tekanan berhasil disimpan.',
            'data' => $log->load('lokasi'),
        ], 201);
    }
}
