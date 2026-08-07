<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\AsetValve;
use App\Models\Lokasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API Controller untuk data aset — digunakan oleh aplikasi mobile
 * sebagai sumber data dropdown pencarian valve.
 */
class ApiAsetController extends Controller
{
    /**
     * List semua aset valve beserta lokasi.
     *
     * GET /api/aset-valve
     * Query params (opsional): ?search=GV-01
     */
    public function index(Request $request): JsonResponse
    {
        $query = AsetValve::with(['lokasi', 'lastLogValve']);

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nama_aset', 'like', $search)
                  ->orWhereHas('lokasi', fn ($q) => $q->where('nama_lokasi', 'like', $search));
            });
        }

        $asets = $query->orderBy('nama_aset')->get();

        return response()->json([
            'success' => true,
            'data' => $asets->map(fn ($aset) => [
                'id' => $aset->id,
                'nama_aset' => $aset->nama_aset,
                'nama_lokasi' => $aset->lokasi->nama_lokasi,
                'kapasitas_full_putaran' => (float) $aset->kapasitas_full_putaran,
                'sisa_bukaan' => $aset->sisa_bukaan,
                'persentase_bukaan' => $aset->persentase_bukaan,
                'latitude' => $aset->latitude ? (float) $aset->latitude : null,
                'longitude' => $aset->longitude ? (float) $aset->longitude : null,
                'nama_teknisi' => $aset->lastLogValve ? $aset->lastLogValve->nama_teknisi : null,
                'keterangan' => $aset->lastLogValve ? $aset->lastLogValve->keterangan : null,
                'foto_eviden' => $aset->lastLogValve && $aset->lastLogValve->foto_eviden 
                    ? asset('storage/' . $aset->lastLogValve->foto_eviden) : null,
                'foto_eviden_2' => $aset->lastLogValve && $aset->lastLogValve->foto_eviden_2 
                    ? asset('storage/' . $aset->lastLogValve->foto_eviden_2) : null,
            ]),
        ]);
    }

    /**
     * List semua lokasi/daerah.
     *
     * GET /api/lokasi
     */
    public function lokasi(): JsonResponse
    {
        $lokasis = Lokasi::where('jenis', 'tekanan')
            ->withCount('asetValves')
            ->orderBy('nama_lokasi')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lokasis->map(fn ($lok) => [
                'id' => $lok->id,
                'no_sr' => $lok->no_sr,
                'nama_pelanggan' => $lok->nama_pelanggan,
                'alamat' => $lok->alamat,
                'desa' => $lok->desa,
                'nama_lokasi' => $lok->nama_lokasi,
                'latitude' => $lok->latitude ? (float) $lok->latitude : null,
                'longitude' => $lok->longitude ? (float) $lok->longitude : null,
                'jumlah_aset' => $lok->aset_valves_count,
            ]),
        ]);
    }

    /**
     * Menambah Jalur (Lokasi) dan Jenis Pipa (AsetValve) baru.
     *
     * POST /api/aset-valve
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jalur' => 'required|string|max:150',
            'jenis_pipa' => 'required|string|max:150',
            'kapasitas_full' => 'required|numeric|min:0',
            'kondisi_awal' => 'nullable|string|in:Full Bukaan,Full Tutupan,Sebagian',
            'custom_tutupan' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Cek apakah Jalur (Lokasi) sudah ada khusus untuk valve
        $lokasi = Lokasi::where('nama_lokasi', $validated['jalur'])
            ->where('jenis', 'valve')
            ->first();

        if (!$lokasi) {
            // Buat Jalur baru jika belum ada, simpan koordinatnya
            $lokasi = Lokasi::create([
                'nama_lokasi' => $validated['jalur'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'jenis' => 'valve',
            ]);
        } else {
            // Jika jalur sudah ada dan koordinat baru dikirim, kita bisa menimpanya atau membiarkannya.
            // Sesuai persetujuan: Biarkan menggunakan koordinat yang lama (tidak ditimpa).
        }

        // Cek apakah Aset sudah ada di Lokasi tersebut (mencegah duplikat)
        $aset = AsetValve::where('lokasi_id', $lokasi->id)
            ->where('nama_aset', $validated['jenis_pipa'])
            ->first();

        if (!$aset) {
            $kondisiAwal = $validated['kondisi_awal'] ?? 'Full Bukaan';
            $totalTutupan = 0.00;
            if ($kondisiAwal === 'Full Tutupan') {
                $totalTutupan = $validated['kapasitas_full'];
            } elseif ($kondisiAwal === 'Sebagian') {
                $totalTutupan = isset($validated['custom_tutupan']) ? (float) $validated['custom_tutupan'] : 0.00;
                if ($totalTutupan > $validated['kapasitas_full']) {
                    $totalTutupan = $validated['kapasitas_full'];
                }
            }

            $aset = AsetValve::create([
                'lokasi_id' => $lokasi->id,
                'nama_aset' => $validated['jenis_pipa'],
                'kapasitas_full_putaran' => $validated['kapasitas_full'],
                'total_tutupan_saat_ini' => $totalTutupan,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Exception: Jenis Pipa GV dengan nama "' . $validated['jenis_pipa'] . '" sudah ada di jalur ini.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jalur dan Jenis Pipa berhasil ditambahkan.',
            'data' => [
                'id' => $aset->id,
                'nama_aset' => $aset->nama_aset,
                'nama_lokasi' => $lokasi->nama_lokasi,
                'kapasitas_full_putaran' => (float) $aset->kapasitas_full_putaran,
                'sisa_bukaan' => $aset->sisa_bukaan,
                'persentase_bukaan' => $aset->persentase_bukaan,
                'latitude' => $aset->latitude ? (float) $aset->latitude : null,
                'longitude' => $aset->longitude ? (float) $aset->longitude : null,
            ],
        ]);
    }

    /**
     * Menambah Daerah (Lokasi) khusus Tekanan.
     *
     * POST /api/lokasi-tekanan
     */
    public function storeLokasiTekanan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'no_sr' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('lokasis')],
            'nama_pelanggan' => ['required', 'string', 'max:150'],
            'alamat' => ['nullable', 'string'],
            'desa' => ['required', 'string', 'max:100'],
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $namaLokasiFinal = $validated['no_sr'] . ' - ' . $validated['nama_pelanggan'];

        $lokasi = Lokasi::create([
            'jenis' => 'tekanan',
            'no_sr' => $validated['no_sr'],
            'nama_pelanggan' => $validated['nama_pelanggan'],
            'alamat' => $validated['alamat'] ?? null,
            'desa' => $validated['desa'],
            'nama_lokasi' => $namaLokasiFinal,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daerah Tekanan berhasil ditambahkan.',
            'data' => [
                'id' => $lokasi->id,
                'nama_lokasi' => $lokasi->nama_lokasi,
                'no_sr' => $lokasi->no_sr,
                'nama_pelanggan' => $lokasi->nama_pelanggan,
                'alamat' => $lokasi->alamat,
                'desa' => $lokasi->desa,
                'latitude' => $lokasi->latitude ? (float) $lokasi->latitude : null,
                'longitude' => $lokasi->longitude ? (float) $lokasi->longitude : null,
            ],
        ]);
    }
}
