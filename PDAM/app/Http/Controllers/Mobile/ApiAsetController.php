<?php

namespace App\Http\Controllers\Mobile;

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
                'latitude' => $aset->lokasi->latitude ? (float) $aset->lokasi->latitude : null,
                'longitude' => $aset->lokasi->longitude ? (float) $aset->lokasi->longitude : null,
                'nama_teknisi' => $aset->lastLogValve ? $aset->lastLogValve->nama_teknisi : null,
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
            $aset = AsetValve::create([
                'lokasi_id' => $lokasi->id,
                'nama_aset' => $validated['jenis_pipa'],
                'kapasitas_full_putaran' => $validated['kapasitas_full'],
                'total_tutupan_saat_ini' => 0.00,
            ]);
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
                'latitude' => $lokasi->latitude ? (float) $lokasi->latitude : null,
                'longitude' => $lokasi->longitude ? (float) $lokasi->longitude : null,
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
            'nama_lokasi' => [
                'required', 
                'string', 
                'max:150', 
                \Illuminate\Validation\Rule::unique('lokasis')->where(fn ($query) => $query->where('jenis', 'tekanan'))
            ],
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $lokasi = Lokasi::create([
            'nama_lokasi' => $validated['nama_lokasi'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'jenis' => 'tekanan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daerah Tekanan berhasil ditambahkan.',
            'data' => [
                'id' => $lokasi->id,
                'nama_lokasi' => $lokasi->nama_lokasi,
                'latitude' => $lokasi->latitude ? (float) $lokasi->latitude : null,
                'longitude' => $lokasi->longitude ? (float) $lokasi->longitude : null,
            ],
        ]);
    }
}
